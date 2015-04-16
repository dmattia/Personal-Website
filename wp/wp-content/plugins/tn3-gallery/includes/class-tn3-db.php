<?php

class TN3_DB
{
    var $found_rows;

    function __construct()
    {
	global $wpdb;
	$this->db = $wpdb;
	$this->docs = $wpdb->prefix . "tn3_documents";
	$this->flds = $wpdb->prefix . "tn3_fields";
	$this->rels = $wpdb->prefix . "tn3_relations";
    }

    function get($type, $page = 0, $limit = 10, $sort = 'modify_time', $ord = 'ASC', $rel = null, $dorder = false, $s = null)
    {
	$ll = $page * $limit;
	$docs = $this->docs;
	$flds = $this->flds;
	$rels = $this->rels;

	$inner = array();
	$where = array();
	$order = '';
	// get ids
	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT $docs.*".($dorder? ",$rels.dorder" : "")." FROM $docs";
	switch ($sort) {
	    case "title":
		$inner[$flds] = array("$docs.id", "$flds.docid");
		$where["$flds.name"] = "='title'";
		$order = "$flds.value_text $ord";
		if (isset($s)) {
		    $where["MATCH ($flds.value_text)"] = " AGAINST ('$s')";
		}
		/*
		$sql .= "INNER JOIN $flds
			 ON $docs.id=$flds.docid
			 WHERE $docs.type='$type' AND $flds.name='title'
			 ORDER BY $flds.value_text $ord LIMIT $ll,$limit;";
		*/
		break;
	    default:
		$order = "$docs.$sort $ord";
		if (isset($rel)) {
		    $inner[$rels] = array("$docs.id", "$rels.docid2");
		    $where["$rels.docid1"] = "='$rel'";
		    $order = "$rels.dorder $ord";
		}
		if (isset($s)) {
		    $inner[$flds] = array("$docs.id", "$flds.docid");
		    $where["MATCH ($flds.value_text)"] = " AGAINST ('$s')";
		}
		break;
	};
	if (count($inner) > 0) {
	    foreach ($inner as $k => $v) {
		$sql .= "\nINNER JOIN $k\nON $v[0]=$v[1] ";		
	    }
	}
	$sql .= "\nWHERE $docs.type='$type' ";
	foreach ($where as $k => $v) {
	    $sql .= "AND $k$v ";
	}
	$sql .= "\nORDER BY $order LIMIT $ll,$limit;";
	//tn3log::w($sql);
	$r = $this->db->get_results($sql, OBJECT_K);
	$this->found_rows = $this->db->get_var("SELECT FOUND_ROWS();");
	//tn3log::a($r);
	if (empty($r)) return $r;

	return $this->get_fields_from_docs($r);

    }
    private function get_fields_from_docs($r)
    {
	$sql = "SELECT *
		FROM $this->flds
		WHERE docid IN(".implode(',', array_keys($r)).")";
	$r2 = $this->db->get_results($sql, ARRAY_A);

	foreach ($r2 as $k => $v) {
	    $vn = 'value_'.$v['value_type'];
	    $r[$v['docid']]->$v['name'] = $v[$vn];
	} 
	//tn3log::w($r);
	return array_values($r);
    }
    function count($type)
    {
	return $this->db->get_var($this->db->prepare("SELECT COUNT(*) FROM ".$this->docs." WHERE type='%s';", $type));
    }
    function getID($id)
    {
	$sql = "SELECT * FROM $this->docs
		INNER JOIN $this->flds
		ON $this->docs.id=$this->flds.docid
		WHERE $this->docs.id='$id'";
	$r = $this->db->get_results($sql, OBJECT_K);
	$outr = $this->get_fields_from_docs($r);
	return $outr[0];
    }
    function add_field(&$a, $name, $type, $val)
    {
	$a[$name] = array(  'type'	=> $type,
			    'value'	=> $val);
    }

    function insert_image($rel_path, $w, $h, $filesize, $rel)
    {
	$d = array();
	$this->add_field($d, 'path', 'text', $rel_path);
	$this->add_field($d, 'width', 'number', $w);
	$this->add_field($d, 'height', 'number', $h);
	$this->add_field($d, 'filesize', 'number', $filesize);
	$meta = wp_read_image_metadata($rel_path);
	if ($meta !== false) {
	    $this->add_field($d, 'meta', 'comp', serialize($meta));
	    if ($meta['caption']) 
		$this->add_field($d, 'description', 'text', $meta['caption']);
	    if ($meta['created_timestamp']) 
		$this->add_field($d, 'created', 'date', gmdate('Y-m-d H:i:s', $meta['created_timestamp']));
	}
	$info = pathinfo($rel_path);
	$this->add_field($d, 'title', 'text', $meta['title']? $meta['title'] : $info['filename']);
	
	//require_once ($this->paths["plug_dir"].'includes/admin/exif/exif.php');
	//$imeta = read_exif_data_raw($file, 0);
	//tn3log::w($idata);
	
	$this->insert('image', $d, $rel);
    }
    function insert_album($title, $desc = null, $thumb = null)
    {
	$d = array();
	$this->add_field($d, 'title', 'text', $title);
	if ($desc) $this->add_field($d, 'description', 'text', $desc);
	if ($thumb) $this->add_field($d, 'thumb', 'text', $thumb);
	$this->insert('album', $d);
    }
    function insert_gallery($title, $desc = null)
    {
	$d = array();
	$this->add_field($d, 'title', 'text', $title);
	if ($desc) $this->add_field($d, 'description', 'text', $desc);
	$this->insert('gallery', $d);
    }
    function insert_doc($typ)
    {
	global $current_user;
	get_currentuserinfo();
	$doc = array(	'type'		=> $typ,
			'create_time'	=> gmdate('Y-m-d H:i:s', time()),
			'create_user'	=> $current_user->ID,
			'modify_time'	=> gmdate('Y-m-d H:i:s', time()),
			'modify_user'	=> $current_user->ID
		    );
	$this->db->insert( $this->docs,  $doc, array( '%s', '%s', '%d', '%s', '%d' ) );
	return $this->db->insert_id;
    }
    function insert($type, $d, $rel = null)
    {
	$nid = $this->insert_doc($type);
	$data = array( $nid => $d );
	$r = $this->insert_fields($data);

	if ($rel != null) return $this->relate($nid, $rel);
	return $r;
    }
    function insert_fields($data, $update = false)
    {
	//tn3log::w($data);
	$isql = "INSERT INTO $this->flds (docid, name, value_type, value_text, value_date, value_number, value_bool, value_comp) VALUES \n";
	$sa = array();
	$typ = array('text', 'date', 'number', 'bool', 'comp');
	$todel = array();
	
	foreach ($data as $nid => $d) {
	    foreach ($d as $k => $v) {
		if ( trim( $v['value'] ) == "" ) {
		    if ($k != 'title' && $v['type'] == 'text') {
			$qk = "DELETE FROM $this->flds WHERE docid=".$nid." AND name='".$k."'";
			//tn3log::a($qk);
			$this->db->query($qk);
		    }
		    continue;
		}
		$s = "('$nid', '$k', '".$v['type']."'";
		foreach($typ as $t) $s .= ($v['type'] == $t)? ", '".$v['value']."'" : ", NULL";
		$sa[] = $s.")";
	    };
	};
	if (count($sa) == 0) return true;
	$isql .= implode(",\n", $sa);
	if ($update) $isql .= " ON DUPLICATE KEY UPDATE \nvalue_text=VALUES(value_text), value_date=VALUES(value_date), value_number=VALUES(value_number), value_bool=VALUES(value_bool), value_comp=VALUES(value_comp);";
	else $isql .= ";";

	//tn3log::a($isql);
	return $this->db->query($isql);
    }
    function relate($ids, $aids)
    {
	// insert relations
	if (!is_array($ids)) $ids = array($ids);
	if (!is_array($aids)) $aids = array($aids);
	$isql = "INSERT IGNORE INTO " . $this->rels . " (docid1, docid2) VALUES ";
	$sa = array();
	foreach ($aids as $aid) {
	    foreach ($ids as $id) {
		$sa[] = "('$aid', '$id')";		
	    }
	}
	$isql .= implode(",", $sa) . ";";
	$r = $this->db->query($isql);
	// count relations
	$this->update_count($ids, 'contained');
	$this->update_count($aids, 'contains');	    

	return $r;
    }
    function unrelate($ids, $aids)
    {
	// insert relations
	if (!is_array($ids)) $ids = array($ids);
	if (!is_array($aids)) $aids = array($aids);
	$isql = "DELETE FROM " . $this->rels . 
	    " WHERE docid1 IN(" . implode(",", $aids) . ")" . 
	    " AND docid2 IN(" . implode(",", $ids) . ");";
	$r = $this->db->query($isql);
	// count relations
	$this->update_count($ids, 'contained');
	$this->update_count($aids, 'contains');	    

	return $r;
    }
    // wpdp OBJECT to OBJECT_K replacement
    function obj2k($r, $field)
    {
	$o = array();
	foreach ($r as $k => $v) {
	    $o[$v->$field] = $v;
	}
	return $o;
    }
    function update_count($ids, $typ)
    {
	$uids = implode(",", $ids);
	$docid = ($typ == "contained")? "docid2" : "docid1";
	$sql = "SELECT $docid AS id, COUNT(*) AS $typ
		FROM $this->rels 
		WHERE $docid IN($uids) 
		GROUP BY $docid;";
	$r = $this->db->get_results($sql);
	// get_results with OBJECT_K returns error so we're rolling our own
	$r = $this->obj2k($r, "id");
	foreach($ids as $k => $v) if (! isset($r[$v])) {
	    $r[$v] = new stdClass();
	    $r[$v]->id = $v;
	    $r[$v]->$typ = 0;
	}
	//tn3log::a($r);
	// be careful with default values as they are not defined
	$sa = array();
	$sql = "INSERT INTO $this->docs (id,$typ) VALUES ";
	foreach ($r as $k =>$v) $sa[] = "($v->id,".$v->$typ.")";
	$sql .= implode(",", $sa);
	$sql .= " ON DUPLICATE KEY UPDATE $typ=VALUES($typ);";
	return $this->db->query($sql);
    }
    function update_rels($parent, $dorder)
    {
	$sa = array();
	$sql = "INSERT INTO $this->rels (docid1, docid2, dorder) VALUES ";
	foreach ($dorder as $k => $v) $sa[] = "($parent,$k,$v)";
	$sql .= implode(",", $sa);
	$sql .= " ON DUPLICATE KEY UPDATE dorder=VALUES(dorder);";
	return $this->db->query($sql);
    }
    function delete($ids, $is_image = false)
    {
	$ids = implode(",", $ids);
	// if image select paths to return them for file deleting
	if ( $is_image ) {
	    $sql = "SELECT value_text FROM $this->flds WHERE docid IN($ids) AND name='path';";
	    $paths = $this->db->get_col($sql);
	}
	// select all affected container ids
	$sql = "SELECT DISTINCT docid1
		FROM $this->rels
		WHERE docid2 IN($ids);";
	$cids = $this->db->get_col($sql);

	/*
	$sql = "DELETE FROM $this->rels WHERE docid1 IN($ids) OR docid2 IN($ids);";
	$r = $this->db->query($sql);
	if (false === $r) return $r;
	$sql = "DELETE FROM $this->flds WHERE docid IN($ids);";
	$r = $this->db->query($sql);
	if (false === $r) return $r;
	$sql = "DELETE FROM $this->docs WHERE id IN($ids);";
	$r = $this->db->query($sql);
	 */
	$docs = $this->docs;
	$flds = $this->flds;
	$rels = $this->rels;

	$sql = "DELETE $docs, $flds, $rels 
		FROM $docs 
		INNER JOIN $flds ON $docs.id=$flds.docid
		LEFT JOIN $rels ON $docs.id=$rels.docid1 OR $docs.id = $rels.docid2
		WHERE $docs.id IN($ids);";
	$r = $this->db->query($sql);

	if ($r !== false) $this->update_count($cids, 'contains');
		
	if ( !$is_image || false === $r) return $r;
	return $paths;
    }

}



?>
