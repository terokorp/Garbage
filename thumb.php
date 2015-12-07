<?php
$webroot = realpath(dirname(__FILE__));
$requested = realpath(dirname(__FILE__)) . $_SERVER['QUERY_STRING'];

if(substr($requested, 0, strlen($webroot)) == $webroot) {
        $path = substr($requested, strlen($webroot));
        $file = $webroot . $path;
}
else {
        die("Invalid folder");
}

function createthumb($name,$new_w,$new_h){
	$ext=explode('.',$name);
	$ext=strtolower($ext[count($ext)-1]);
	if (preg_match('/jpg|jpeg/',$ext)){
		$src_img=imagecreatefromjpeg($name);
	}
	if (preg_match('/gif/',$ext)){
		$src_img=imagecreatefromgif($name);
	}
	if (preg_match('/png/',$ext)){
		$src_img=imagecreatefrompng($name);
	}


	$md5 = md5_file($name);
	$thumbdir = realpath(dirname(__FILE__)."/thumbs")."/".substr($md5, 0, 2)."/";
	if(!is_dir($thumbdir)) {mkdir($thumbdir, 0777);}

	$eTag = '"'.$md5.'t"';
	header('Etag: '.$eTag);

	$thumbfile=$thumbdir.$md5.".".$ext.".thumb";

	header('Content-Type: image/jpeg');
	if ($eTag == $_SERVER['HTTP_IF_NONE_MATCH'] && $_SERVER['Cache-control']!="no-cache") {
		header("HTTP/1.0 304 Not Modified");
		header('Content-Length: 0');
		exit();
	} elseif(is_file($thumbfile)) {
		readfile($thumbfile);
	} else {
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);

		if ($old_x > $old_y) {
			$thumb_w=$new_w;
			$thumb_h=$old_y*($new_h/$old_x);
		}
		if ($old_x < $old_y) {
			$thumb_w=$old_x*($new_w/$old_y);
			$thumb_h=$new_h;
		}
		if ($old_x == $old_y) {
			$thumb_w=$new_w;
			$thumb_h=$new_h;
		}

		$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecolortransparent($dst_img, imagecolorallocate($dst_img, 0, 0, 0));
		imagealphablending($dst_img, false);
		imagesavealpha($dst_img, true);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 


		imagejpeg($dst_img, $thumbfile, 50);
		imagejpeg($dst_img);
		imagedestroy($dst_img);
		imagedestroy($src_img);
	}
}
createthumb($file,150,160);
