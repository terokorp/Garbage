<?php
/*
* Project Name : Garbage
* @author $Author: thasan $
*
***********************************/

require_once("config.php");

# Denied filetypes
$deniedfiletypes = array(
	"php",
	"php3",
	"php4",
	"php5",
	"phtml",
	"cgi",
	"htm",
	"html",
	"xhtm",
	"xhtml",
	"shtml"
);


$banned = array(
	"/^192.168.66.*$/",	// Example ban
);


/* ############################################################ */
/* #          DO NOT MODIFY ANYHING BELOW THIS LINE!          # */
/* ############################################################ */

define("VERSION", "V1.10.0");

if(DEBUG == true) error_reporting(E_ALL ^ E_NOTICE);
	else error_reporting(0);

defined('CLOSED') &&  die('<html><head><title>'.NAME.'</title></head><body><table border=0 width=100% height=80%><tr><td><h3 align=center>'.CLOSED.'</h3><p align=center>Garbage '.VERSION.' &copy; koodaa.net</p></td></tr></table></body></html>');
$datafolder = $folders[0][0];
foreach ($folders as $folder) {
	if($_POST['folder'] == $folder[0]) {
		$datafolder = $folder[0];
		$type = $folder[1];
		break;
	}
	if($_GET['folder'] == $folder[0]) {
		$datafolder = $folder[0];
		$type = $folder[1];
		break;
	}
}

if(!is_dir($datafolder . "/")) {mkdir($datafolder . "/", 0777) or die("System error (sysop: turn debug on :)"); }


$error = "";
if(file_exists($_FILES['file']['tmp_name'])) {
	if($type == "date") $dir = $datafolder . "/" . date("Y-m") . "/";
	else $dir = $datafolder . "/";
	if(!is_dir($dir)) {
		mkdir($dir, 0777) or die("System error (sysop: turn debug on :)");
		symlink("../index.php", $dir."index.php");
		symlink("../thumb.php", $dir."thumb.php");
	}

	if(file_exists($dir.$_FILES['file']['name'])) $error .= "Duplicate file " . $_FILES['file']['name']."<br>";
	else {

		foreach($deniedfiletypes as $denied) {
			if (ereg("^(.*)\.$denied$",strtolower($_FILES['file']['name']))){
				$error .= "Filetype " . $denied . " denied. (" . $_FILES['file']['name'] . ")";
			}
		}
		if (ereg("^\.ht(.*)$", strtolower($_FILES['file']['name']))){
			$error .= "Filetype is denied. (" . $_FILES['file']['name'] . ")";
		}
		if($error == "") {
			move_uploaded_file($_FILES['file']['tmp_name'], $dir.$_FILES['file']['name']); // tallennetaan tiedosto serverille
			$url='http://' . $_SERVER['HTTP_HOST'];
			if(dirname($_SERVER['PHP_SELF'])=="/") $url.=""; else $url.= dirname($_SERVER['PHP_SELF']);
			$error .= "The file <a href=\"$url/$dir" . $_FILES['file']['name'] . "\">" . $_FILES['file']['name'] . "</a> has been copied successfully :)<br>\n";
			$error .= "<b>$url/$dir" . $_FILES['file']['name'] . "</b><br>\n";
			system("beep");
			$md5sum=md5_file($dir.$_FILES['file']['name']);
			$filesize=filesize($dir.$_FILES['file']['name']);
			$mimetype=mime_content_type($dir.$_FILES['file']['name']);

			$fh = fopen(".htlist", 'a');
			fwrite($fh, date("r"). " - " .getip(). " " . $dir . $_FILES['file'.$i]['name']."\n");
			fclose($fh);
		}
	}
}
function getip() {
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$host = "Proxy: ".$_SERVER["REMOTE_ADDR"]." ".$_SERVER["HTTP_X_FORWARDED_FOR"];
	} else {
		$host = $_SERVER["REMOTE_ADDR"];
	}
	return $host;
}

function isbanned($ip) {
	global $banned;
	foreach($banned as $ban) {
		if (preg_match($ban, $ip)) {
			return true;
		}
	}
	return false;
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?=NAME?></title>
	<meta http-equiv="Cache-Control" content="NO-CACHE">
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="Description" content="a bin for all of my cool junk">
	<meta name="Generator" content="GNU nano 2.2.6">
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" type="image/x-icon" href="favicon.ico">
	<link rel="stylesheet" type="text/css" href="style.css">
	<script src="/drag.js" type="text/javascript"></script>
        <style type="text/css">
        <!--
	html, body, div, p { padding: 0; margin: 0; }

	body {
		font-size: small;
		color: #333;
		line-height: 140%;
		background: #fff url("img/fade.png") repeat-x;
		font-family: serif;
	}

	.h1, h1 {
		font-size: 25;
		font-weight: bold;
		margin-top: 60;
		margin-left: 5;
	}
	table, .links {
		margin-left: 5;
	}
	#message {
		background: #DDDDDD;
		width: 400px;
	}


	div.empty,
	div.open,
	div.oneFile,
	div.manyFiles
	{
		width: 149px;
		height: 117px;
		background-repeat: no-repeat;
		background-image: url(folders-2.png);
	}

	--></style>
	<link rel="stylesheet" type="text/css" href="style.css">
	<?php if(file_exists("analtytic.inc")) readfile("analtytic.inc"); ?>
	</head>
	<body>
	<div align=center>
		<?php if(SHOWTITLE != false) echo '<div class="h1">'.NAME.'</div><p>'.SLOGAN.'</p><br>'; ?>
		<a href="<?php echo $folders[0][0];?>">
			<img id="drop" src="/img/<?=IMG?>" width="<?=IMG_WIDTH?>" height="<?=IMG_HEIGHT?>" border="0" alt="" />
		</a><br />
		<?php foreach ($folders as $folder) { echo '<a href="'.$folder[0].'">['.$folder[2].']</a> ';} ?><br>
		<?php if(!isbanned(getip())) { ?>
		<form action="" method="post" ENCTYPE="multipart/form-data">
			<input type="file" name="file" size="20">
			<select name="folder" id="folder"><?php
				$foldernum=0;
				foreach ($folders as $folder) {
					if($folder[3]==true) {
						if($foldernum==0) echo "<option value=\"$folder[0]\" selected>$folder[2]</option>";
						else echo "<option value=\"$folder[0]\">".$folder[2]."</option>";
						$foldernum++;
					}
				}
			?></select>
		<input type="submit" value="Throw it" onclick="this.style.color='#AAAAAA'">
		</form>
		<div id="output" style="min-height: 100px; white-space: pre; border: 1px solid black;display:none">
		</div>

		<?php } ?>
		<?php if($error != "") echo "<div id=\"message\">$error</div>"; ?><hr width="400px">
		Garbage <?=VERSION?> &copy; <a href="http://garbage.koodaa.net/">garbage.koodaa.net</a>
		<?php if($_SERVER['HTTP_HOST'] == "gar.koodaa.net") echo '<br>also used by: <a href="http://garbage.tiiffi.ath.cx">garbage.tiiffi.ath.cx</a>'; ?>
	</div>
	<div id="extra"><span></span></div>
</body>
</html>
