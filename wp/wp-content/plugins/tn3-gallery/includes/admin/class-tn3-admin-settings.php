<?php

require_once (TN3::$dir.'includes/admin/class-tn3-admin-page.php');
require_once (TN3::$dir.'includes/class-tn3-options.php');

class TN3_Admin_Settings extends TN3_Admin_Page
{

    var $form_opts = array();
    var $vout = array();
    var $verrors = array(); 

    function __construct()
    {
	parent::__construct();
    }
    function print_page()
    {
	$this->print_header(TN3_Admin::$t['settings']." - ".TN3_Admin::$t[$this->section]);

	echo"<form id='tn3-".$this->section."-form' action='options.php?tn3=tn3-settings-$this->section' method='post'>";

	settings_errors();
	// Output nonce, action, and option_page fields for a settings page.
	// $option_group same as 1st arg of register_setting
	settings_fields("tn3_".$this->section);
	// prints out all settings sections added with add_settings_section
	// $pageslug used in add_settings_section anad add_settings_field
	do_settings_sections("tn3-".$this->section."-settings");

	$this->add_to_form();
	
	echo '<br />';
	echo '<input name="Submit" type="submit" class="button-primary" value="Save Changes" />';
	echo '</form>';
	
    }
    function add_to_form(){}
    function admin_init()
    {
	//wp_register_script( "jquery-ui-tabs" );
	parent::admin_init();
	$this->form_opts = TN3_Options::getForms();
	$this->form_opts = $this->form_opts[ $this->section ];


	    // add field to whitelist so that it can be submited
	    // $option_group is name of the key in whitelist
	    // $option_name is name of the option
	    // $f to sanitize and validate. because we are storing settings as arrays we use this function to join for fields in array to submit
	    register_setting(
		"tn3_".$this->section,
		"tn3_admin_".$this->section,
		array($this, "sanitize_options")
	    );



	    
	    // $id that connects fields to this section
	    // $title of the section
	    // $f that echoes description
	    // $pageslug used as key to group sections
	    add_settings_section("tn3_".$this->section,
		$this->form_opts['title'],
		array($this, 'nofunc'),
		"tn3-".$this->section."-settings");

	    foreach( $this->form_opts['fields'] as $fname => $fv ) {
		
		$id = "tn3_".$this->section."_$fname";
		$optv = TN3::$o[$this->section][$fname];
		if (!is_null($optv)) $fv['value'] = $optv;
		// $id
		// $title
		// $f to echo form element
		// $pageslug used as key to group sections
		// $id of the section that field belongs
		// $args passed to $f ('label_for' special key for label may be used)
		add_settings_field($id,
		    $fv['title'],
		    array($this, 'render'),
		    "tn3-".$this->section."-settings",
		    "tn3_".$this->section,
		    array($fv));

	    }
	
    }
    function nofunc()
    {

    }
    function sanitize_options()
    {
	// option_page value can be tn3_general, tn3_skin, ...
	if ( isset($_POST['option_page']) && substr($_POST['option_page'], 0, 4) == "tn3_" ) {

	    if (isset($_POST['tn3_skin_presets_action']) && "delete" == $_POST['tn3_skin_presets_action']) {
		$this->delete_skin_preset($_POST['tn3_skin_presets']);
		return;
	    } else if (isset($_POST['tn3_transition_presets_action']) && "delete" == $_POST['tn3_transition_presets_action']) {
		$this->delete_transition_preset($_POST['tn3_transition_presets']);
		return;
	    }

	    // loop through form fields used to build form, sanitize and validate them
	    foreach ( $this->form_opts['fields'] as $k => $v ) {

		// when field is array, set form values first, then validate
		if (is_array($v['value'])) {
		    $arrayValue = array();
		    foreach ($v['value'] as $vk => $vv) {
			// $pv will hold the name of the form field
			$formField = "tn3_".$this->section."_$k"."_$vk";
			// there is no POST value for disabled fields so skip them
			if ( isset($_POST[$formField]) ) 
			    $arrayValue[$vk] = $_POST[$formField];
			else
			    $arrayValue[$vk] = $vv;
		    }
		    $this->do_validate_field( $k, $arrayValue, $v['type'] );
		} else {
		    $formField = "tn3_".$this->section."_$k";
		    if ( isset($_POST[$formField]) ) {
			$this->do_validate_field( $k, $_POST[$formField], $v['type'] );
		    } else if ($v['type'] == "checkbox") {
			$this->do_validate_field( $k, 0, $v['type'] );
		    }
		    
		}
	    }
	    // when there is a preset, save it first
	    if ( $_POST['option_page'] == "tn3_skin" ) {
		$this->save_skin_preset($_POST['tn3_skin_presets'], $this->vout);
		$this->vout['tn3_skin_presets'] = $_POST['tn3_skin_presets'];
	    } else if ( $_POST['option_page'] == "tn3_transition" ) {
		$this->save_transition_preset($_POST['tn3_transition_presets'], $this->vout);
		$this->vout['tn3_transition_presets'] = $_POST['tn3_transition_presets'];
	    }
	    // do_validate_field function will also fill error array(verrors) and output array(vout)
	    if (count($this->verrors) > 0 ) add_settings_error( "tn3_".$this->section."_$k", $k, implode(", ", $this->verrors).": Wrong Value!", 'error' );
	    else add_settings_error( "tn3_".$this->section."_$k", $k, "Updated Successfully!", 'updated' );
	    //tn3log::w($out);
	    return $this->vout;
	}	    
    }
    function do_validate_field( $name, $value, $type )
    {
	// this will convert only non-array values
	// validate_field function should convert complex types(Array)
	if ($type == "checkbox" && !isset($value)) $value = 0;
	$val = $this->validate_field( $name, $value, $type );
	// if it is not validated, set to previous value and write to error buffer
	if ( is_null($val) ) {
	    $this->verrors[] = $name;
	    $this->vout[$name] = TN3::$o[$this->section][$name];
	} else {
	    $this->vout[$name] = $val;
	}
    }
    function validate_field( $name, $value, $type )
    {
	return $value;
    }

    function save_skin_preset($name, $p)
    {
	$all = get_option("tn3_presets_skin");
	$np = array('image' => array(), 'thumbnailer' => array());
	foreach ($p as $k => $v) {
	    if ($k == "presets") continue;
	    $typ = explode("_", $k);
	    switch ($typ[2]) {
	    case "n":
		$v = (float)$v;
		break;
	    case "b":
		$v = (bool)$v;
		break;
	    case "o":
		$v = explode(",", $v);
		break;
	    default:

		break;
	    }
	    if ($typ[0] == 'general') $np[$typ[1]] = $v;
	    else $np[$typ[0]][$typ[1]] = $v;
	}
	$all[$name] = $np;
	update_option("tn3_presets_skin", $all);
    }
    function save_transition_preset($name, $p)
    {
	$all = get_option("tn3_presets_transition");
	$params = json_decode(urldecode($p['params']));
	$all[$name] = $params;
	update_option("tn3_presets_transition", $all);
    }
    function delete_skin_preset($name)
    {
	$all = get_option("tn3_presets_skin");
	unset($all[$name]);
	update_option("tn3_presets_skin", $all);
    }
    function delete_transition_preset($name)
    {
	$all = get_option("tn3_presets_transition");
	unset($all[$name]);
	update_option("tn3_presets_transition", $all);
    }

    ########### Form Rendering
    function render($a)
    {
	extract($a[0]);

	call_user_func(array($this, "print_".$type), $a[0]);
	echo $desc;
	
    }
    function print_checkbox($a)
    {
	extract($a);
	echo "<input id='$id' name='$id' type='checkbox' value='1' ".(($value == 1)? "checked='checked' " : " ");
	foreach( $att as $k => $v ) {
	    echo $k."='$v' ";
	}
	echo "/> ";
    }
    function print_html($a)
    {
	extract($a);
	echo $value;
    }
    function print_radio($a)
    {
	extract($a);
	foreach( $e as $k => $v ) {
	    echo "<input id='$id' name='$id' type='radio' value='$k' ".(($value == $k)? "checked='checked'" : "")." />";
	    echo $v."<br />";
	}
    }
    // 0 = id, 1 = name, 2 = value
    function print_textfield($a)
    {
	extract($a);
	echo "<input id='$id' name='$id' value='$value' type='text' ";
	foreach( $att as $k => $v ) {
	    echo $k."='$v' ";
	}
	echo "/> ";
    }
    function print_combo($a)
    {
	extract($a);
	echo "<select id='$id' name='$id' ";
	if ( isset($att) && is_array($att) ) {
	    foreach( $att as $k => $v ) {
		echo $k."='$v' ";
	    }
	}
	echo ">";
	foreach( $e as $k => $v ) {
	    echo "<option ".(($k==$value)? "selected='selected' " : "")."value='$k'>$v</option>";
	}
	echo "</select> ";
    }
    function print_image_size($a)
    {
	extract($a);
	extract($value);
	$aa = array('size' => 4);
	if ($att) {
	    $aa['disabled'] = "yes";
	    $aa['style'] = "opacity:0.5";
	}
	if (isset($required)) {
	    $this->print_checkbox(array('id' => $id."_required", 'value' => $required, 'att' => $aa));
	    echo "enabled<br />";
	}	    
	echo "<div id='$id' class='tn3-complex-field'>";
	$this->print_textfield(array('id' => $id."_w", 'value' => $w, 'att' => $aa));
	echo " x ";
	$this->print_textfield(array('id' => $id."_h", 'value' => $h, 'att' => $aa));
	echo ", quality:";
	$aa['size'] = "1";
	$aa['maxlength'] = "3";
	$this->print_textfield(array('id' => $id."_q", 'value' => $q, 'att' => $aa));
	// we dont want crop option for some
	if ( $crop != 2 ) {
	    echo "crop:";
	    $this->print_checkbox(array('id' => $id."_crop", 'value' => $crop, 'att' => $aa));
	}
	echo "</div>";
    }	
    function print_presets($a)
    {
	extract($a);
	$presets = get_option("tn3_presets_".$e);
	echo '<select id="'.$id.'" name="'.$id.'" >';
	foreach( $presets as $k => $v ) {
	    echo "<option ".(($k==$value)? "selected='selected' " : "")."value='$k'>$k</option>";
	}
	echo "</select> ";
	echo '<input name="Submit" type="submit" id="'.$id.'_btn" class="button-secondary" value="Add New" />';
	echo '<input type="hidden" name="tn3_'.$e.'_presets_action" value="save">';
	echo '<input name="Submit" type="submit" id="'.$id.'_del" class="button-secondary" value="Delete" />';
    }
    function print_hidden($a)
    {
	extract($a);
	echo '<input type="hidden" name="'.$id.'" value="'.$value.'" />';
    }

}

?>
