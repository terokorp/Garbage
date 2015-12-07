<?php
require_once("config.php");

error_reporting(E_ALL & E_NOTICE);
$ignore = array("index.php", "thumb.php", "footer.php", ".", "..", ".img", "archive", "dropbox", "thumb", ".git");

$types['image']=array("jpg", "jpeg", "gif", "png");
$types['music']=array("mp3", "ogg", "mid", "flac");
$types['docum']=array("txt", "html", "doc", "md");
$types['video']=array("avi", "mp4", "mov", "mkv");
$types['arch']=array("zip", "tar", "gz", "rar", "lzh");
$types['exec']=array("exe", "bin");
$types['flash']=array("swf");

$perpage=100;

$webroot = realpath(dirname(__FILE__));
$requested = realpath(dirname(__FILE__) . $_SERVER['QUERY_STRING']);
$requestedparent = realpath(dirname(__FILE__) . $_SERVER['QUERY_STRING'] . "/..");

if(substr($requested, 0, strlen($webroot)) == $webroot) {
	$path = substr($requested, strlen($webroot));
	$parent = substr($requestedparent, strlen($webroot));
	$folder = $webroot . $path;
}
else {
	die("Invalid folder");
}

function listfiles() {
	global $folder, $path, $ignore, $cfg;
	$folders=array();
	$files=array();
	$others=array();
	if($handle = opendir($folder)) {
		$count = 0;
		while (false !== ($name = readdir($handle))) {
			if(!in_array($name, $ignore)) {
				$type="";
				$count++;
				if(is_dir($folder."/".$name)) {
					$folders[$name. "d".filemtime($name)." ".$count] = array(
						'name' => $name,
						'created' => date("d.m.Y H:i",filemtime($name)),
						'type' => "dir",
					);
				}
				elseif(is_file($folder."/".$name)) {
					$ext=explode(".", $name);
					$ext=$ext[count($ext)-1];
					$files["d".filemtime($name)." ".$count] = array(
						'name' => $name,
						'created' => date("d.m.Y H:i",filemtime($name)),
						'type'=>"file",
						'ext' => strtolower($ext),
						'size' => filesize($name),
					);
				}
				else {
					$others["d".filemtime($name)." ".$count] = array(
						'name' => $name,
						'created' => date("d.m.Y H:i", filemtime($name)),
						'type'=>"unknow",
					);
				}
			}
		}
		closedir($handle);
	}
	ksort($folders, SORT_STRING);
	ksort($files, SORT_STRING);
	ksort($others, SORT_STRING);
	$files = array_reverse($files);
	$stack=array();
	foreach ($folders as $key => $data) {
		$stack[] = $data;
	}
	foreach ($files as $key => $data) {
		$stack[] = $data;
	}
	foreach ($others as $key => $data) {
		$stack[] = $data;
	}
	return $stack;
}
function byteConvert(&$bytes){
	$b = (int)$bytes;
	$s = array('B', 'kB', 'MB', 'GB', 'TB');
	if($b <= 0){
		return "0 ".$s[0];
	}
	$con = 1024;
	$e = (int)(log($b,$con));
	return number_format($b/pow($con,$e),2,',','.').' '.$s[$e];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fi" lang="fi">
<head>
	<title> Garbage - <?=$path; ?> </title>
	<meta name="Author" content="Tero 'Thasan' Korpela" />
	<meta name="Generator" content="GNU nano 2.0.2" />
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<style type="text/css">
		#filelist {
			overflow: hidden;
		}
		.box {
			overflow: hidden;
			margin: 0;
			padding: 0;
			height: 220px;
			width: 170px;
			background-color: #eeeeee;
			border: 1px solid;
			float: left;
			text-align: center;
		}
		img { border: 0; }
		h1 {
			margin-bottom: 0px;
		}
	</style>
<?php /* Analytics code supoosed to be here too */ ?>
</head>
<body>
<div id="container">
<h1>Index of <?=$path;?></h1>
<a href="<?=$cfg['WEBROOT']."list.php?".$parent;?>">&lt;--</a><br />
<?php
$files = listfiles();
if(isset($_GET['page'])) $page = $_GET['page'];
if(empty($page) || !is_numeric($page)) $page = 1;
if($page > ceil(count($files) / $perpage)) $page = ceil(count($files) / $perpage);
if($page < 1) $page = 1;

echo '<a href="?page='.($page-1).'">&lt;&lt;</a> ';
for($i=1 ; $i < ceil(count($files) / $perpage)+1 ; $i++) {
	echo '<a href ="?page='.$i.'">'.$i.'</a> ';
}
echo '<a href="?page='.($page+1).'">&gt;&gt;</a>';

echo "<hr />";

$start = $perpage * ($page-1);
$end   = $perpage * ($page);
if($end > count($files)) $end = count($files);
echo "" . ($start+1) . " - " . $end . "<br />";

	echo $cfg['WEBDIR'];

echo '<div id="filelist">';
for($i = $start ; $i < $end ; $i++) {
	$data = $files[$i];
	$target = $data['name'];
	$imgfile=$cfg['WEBROOT']."img/ico_unknow.jpg";

	if(!isset($data['ext'])) $data['ext'] = "";
	if(in_array($data['ext'], $types['image'])) {
		$imgfile=$cfg['WEBDIR']."/thumb.php?".$path."/".$data['name'];
		$target=$cfg['WEBDIR'].$path."/".$data['name'];
	}

	if($data['type']== "dir") {
		$imgfile=$cfg['WEBDIR']."/img/ico_folder.jpg";
		$target=$cfg['WEBDIR']."/list.php?".$path."/".$data['name'];
	}

	if($data['ext'] == "url") {
		$imgfile=$cfg['WEBDIR']."/img/ico_link.jpg";
		preg_match_all("/URL\=([a-z0-9.,:\/&?% ^+-]+)/i", file_get_contents($data['name']), $matches);
		$target=$matches[1][0];
	}

	foreach($types as $key => $type) {
		if(!empty($imgfile)) break;
		if(in_array($data['ext'], $types[$key]) ) {
			$imgfile=$cfg['WEBROOT']."/img/ico_".$key.".jpg";
			$target=$cfg['WEBROOT']."".$path."/".$data['name'];
		}
	}

	if(empty($imgfile)) $imgfile=$cfg['WEBROOT']."/img/ico_unknow.jpg";

	echo '<div class="box">';
	echo '	<div class="name"><a href="'.$target.'">'.$data['name'].'</a></div>';
	echo '	<div class="img"><a href="'.$target.'"><img src="'.$imgfile.'" alt="'.$data['name'].'" /></a></div>';
	echo '	<div class="date">'.$data['created'].'</div>';
	echo '	<div class="size">'.($data['type']=="file" ? byteConvert($data['size']) : "").'</div>';
	echo "</div>\n";
}
?>
</div>

<?php if(is_file("footer.php")) {
	echo '<br clear="all" />';
	include("footer.php");
	echo '<br clear="all" />';
} else {
	echo '<a href="?page='.($page-1).'">&lt;&lt;</a> ';
	for($i=1 ; $i < ceil(count($files) / $perpage)+1 ; $i++) {
		echo '<a href ="?page='.$i.'">'.$i.'</a> ';
	}
	echo '<a href="?page='.($page+1).'">&gt;&gt;</a>';
}
?>
<hr />
<p>
	<a href="http://validator.w3.org/check?uri=referer">
	<img src="http://www.w3.org/Icons/valid-xhtml10-blue" alt="Valid XHTML 1.0 Transitional" height="31" width="88" />
	</a>
	<a href="http://jigsaw.w3.org/css-validator/">
	<img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss-blue" alt="Valid CSS!" />
	</a>
</p>

</div>
</body>
</html>
