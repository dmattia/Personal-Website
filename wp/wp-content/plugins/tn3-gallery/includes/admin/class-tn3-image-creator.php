<?php

class TN3_Image_Creator
{
    var $file, $img, $dirname, $basename, $w, $h, $umask;

    function __construct($f)
    {
	$this->file = $f;
	$info = pathinfo($this->file);
	// dirname, basename, extension, filename
	extract($info);
	$this->dirname = $dirname;
	$this->basename = $basename;
	$ext = strtolower($extension);
	if ($ext == "jpg") $ext = "jpeg";

	$this->img = call_user_func('imagecreatefrom'.$ext, $this->file);
	
	$this->w = imagesx($this->img);
	$this->h = imagesy($this->img);

	$this->umask - umask(0022);
    }

    function create($size, $opts)
    {
	
	$newpath = $this->dirname.DIRECTORY_SEPARATOR.$size;
	if (!is_dir($newpath)) @mkdir($newpath);
	$newpath .= DIRECTORY_SEPARATOR.$this->basename;

	foreach ($opts as $k => $v) if ($v == '') unset($opts[$k]);

	$this->createGD($opts, $newpath);

    }
    function createWithCrop($size, $cops)
    {
	$dest = $this->dirname.DIRECTORY_SEPARATOR.$size;
	if (!is_dir($dest)) @mkdir($dest);
	$dest .= DIRECTORY_SEPARATOR.$this->basename;

	// x, y, dw, dh, w, h, q
	extract($cops);
	$new = imagecreatetruecolor($dw, $dh);
	imagecopyresampled($new, $this->img, 0, 0, $x, $y, $dw, $dh, $w, $h);

	// Output
	imagejpeg($new, $dest, $q);
    
	imagedestroy($new);
    }
    private function createGD($opts, $dest)
    {
	$fin = $this->getFinalCrop($opts);

	// x, y, w, h, wid, hei
	extract($fin);
	if ($wid == $this->w && $hei == $this->h) {
	    copy($this->file, $dest);
	} else {

	    $new = imagecreatetruecolor($wid, $hei);
	    imagecopyresampled($new, $this->img, $x, $y, 0, 0, $w, $h, $this->w, $this->h);

	    // Output
	    imagejpeg($new, $dest, $opts['q']);
	
	    imagedestroy($new);
	}
	
    }
    function destroy()
    {
	imagedestroy($this->img);
	umask($this->umask);
    }
    function getFinalCrop($opts)
    {
	$op = $this->w / $this->h;

	extract($opts);
	if (!isset($center)) $center = 0.5;

	if (!isset($w)) $w = $h * $op;
	if (!isset($h)) $h = $w / $op;

	$p = $w / $h;

	$r = array();
	$r['x'] = 0;
	$r['y'] = 0;
	
	if (isset($crop)) {
	    if ($p >= $op) {
		$r['w'] = min($w, $this->w);
		$r['h'] = $r['w'] / $op;
		$r['y'] = -max(0, ($r['h']*$center) - ($h / 2));
	    } else {
		$r['h'] = min($h, $this->h);
		$r['w'] = $r['h'] * $op;
		$r['x'] = -max(0, ($r['w']*$center) - ($w / 2));
	    }
	    $r['wid'] = $w;
	    $r['hei'] = $h;   

	} else {
	    if ($p >= $op) {
		$r['h'] = min($h, $this->h);
		$r['w'] = $op * $r['h'];
	    } else {
		$r['w'] = min($w, $this->w);
		$r['h'] = $r['w'] / $op;
	    }
	    $r['wid'] = $r['w'];
	    $r['hei'] = $r['h'];   
	}
	return $r;
    }



}
?>
