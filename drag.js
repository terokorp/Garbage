var count = 0;
var k = 1024;
var m = k * k;
var maxFileSizeMB = 50;
var width = 200;
var drop;
var folder;

function updateBytes(evt) {
	if (evt.lengthComputable) {
		evt.target.curLoad = evt.loaded;
		evt.target.log.parentNode.parentNode.previousSibling.textContent = Number(evt.loaded/k).toFixed() + "/"+ Number(evt.total/k).toFixed() + "kB";
	}
}

function updateSpeed(target) {
	if (!target.curLoad) return;
	target.log.parentNode.parentNode.previousSibling.previousSibling.textContent = Number((target.curLoad - target.prevLoad)/k).toFixed() + "kB/s";
	target.prevLoad = target.curLoad;
}

function updateProgress(evt) {
	updateBytes(evt);
	if (evt.lengthComputable) {
		var loaded = (evt.loaded / evt.total);
		 if (loaded < 1) {
			var newW = loaded * width;
			if (newW < 10) newW = 10;
			evt.target.log.style.width = newW + "px";
		}
	}
}

function loadError(evt) {
	evt.target.log.setAttribute("status", "error");
	evt.target.log.parentNode.parentNode.previousSibling.previousSibling.textContent = "error";
	clearTarget(evt.target);
}

function loaded(evt) {
	updateBytes(evt);
	evt.target.log.style.width = width + "px";
	evt.target.log.setAttribute("status", "loaded");
	evt.target.log.parentNode.parentNode.previousSibling.previousSibling.textContent = "";
	clearTarget(evt.target);
}

function clearTarget(target) {
	clearInterval(target.interval);
	target.onprogress = null;
	target.onload = null;
	target.onerror = null;
}

function start(file) {
	++count;

	drop = document.getElementById("folder");
	var formData = new FormData();
	var xhr = new XMLHttpRequest();
	
	formData.append("folder", folder.value);
	formData.append("file", file);
	xhr.open("POST", "index.php", true);
	xhr.onload = function(e) {

	};

	xhr.send(formData);
}

var totalCount = 0;
function dodrop(event) {
	event.stopPropagation(); 
	event.preventDefault(); 
	var dt = event.dataTransfer;
	var files = dt.files;

	var count = files.length;
	totalCount += count;
	for (var i = 0; i < count; i++) {
		var types = dt.mozTypesAt(i);
		for (var t = 0; t < types.length; t++) {
		 	if (types[t] == "application/x-moz-file") {
				try {
					start(files[i]);
	    			} catch (ex) {
					dump(ex);
					output("<<error>>\n");
				}
			}
		}
	}
}

function output(text) {
	document.getElementById("output").textContent += text;
	dump(text);
}

function dragEnter(event) {
//		if (totalCount == 0)
//			document.getElementById('folder').className = 'open';
}
function dragExit(event) {
//		document.getElementById('folder').className = 'empty';
	event.stopPropagation(); 
	event.preventDefault();
}

window.onload = function () {

	drop = document.getElementById("drop");
	folder = document.getElementById("folder");
	drop.ondragenter = drop.ondragover = function (e) {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
		return false;
	}

	drop.addEventListener('ondrop', dodrop, true)
	drop.ondrop = function (e) {
		dodrop(e);
		e.preventDefault();
		return false;
	}
}
