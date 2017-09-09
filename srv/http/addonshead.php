<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RuneAudio - Addons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msapplication-tap-highlight" content="no" />
    <link rel="stylesheet" href="assets/css/runeui.css">
    <link rel="stylesheet" href="assets/css/addons.css">
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">
</head>
<body>

<!-- hide vertical scrollbar on desktop -->
<script>
var div = document.createElement('div');
div.className = 'divscrollbar';
document.body.appendChild(div);
var scrollbarWidth = div.offsetWidth - div.clientWidth;
document.body.removeChild(div);

if (scrollbarWidth !== 0) {
	var css = '.hidescrollv {\n'
				+'	width: 100%;\n'
				+'	overflow: hidden;\n'
				+'}\n'
				+'pre {\n'
				+'	width: calc(100% + '+ ( scrollbarWidth + 1 ) +'px);\n'
				+'}';
	var style = document.createElement('style');
	style.appendChild(document.createTextNode(css));
	document.head.appendChild(style);
}
</script>

<div id="loader" style="display: none;">
	<div id="loaderbg"></div>
	<div id="loadercontent"><i class="fa fa-refresh fa-spin"></i>connecting...</div>
</div>
