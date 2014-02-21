function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function setArtColors(hex) {
	var rgb = hexToRgb(hex);
	var rgbString = rgb.r + ', ' + rgb.g + ', ' + rgb.b;

	var css = '';
	css += 'body.arts a, body.arts ul.buttons a { color:#' + hex +'; } ';
	css += 'body.arts #page-header { background-color:#' + hex+ '; } ';
	css += 'body.arts .tabs ul li > span.selected { background-color:#' + hex+ '; border-color:#' + hex+ ' !important; }';
	css += 'body.arts #awesomebar li.hover, body.arts #awesomebar li.hover:hover { background-color: #' + hex+ '  !important; }';

	css += '#page-header-gradient:before {';
		css += 'background: -moz-linear-gradient(left,  rgba(' + rgbString + ',1) 0%, rgba(' + rgbString + ',0.7) 10%, rgba(' + rgbString + ',0) 33%, rgba(' + rgbString + ',0) 100%);';
		css += 'background: -webkit-gradient(linear, left top, right top, color-stop(0%,rgba(' + rgbString + ',1)), color-stop(10%,rgba(' + rgbString + ',0.7)), color-stop(33%,rgba(' + rgbString + ',0)), color-stop(100%,rgba(' + rgbString + ',0)));';
		css += 'background: -webkit-linear-gradient(left,  rgba(' + rgbString + ',1) 0%,rgba(' + rgbString + ',0.7) 10%,rgba(' + rgbString + ',0) 33%,rgba(' + rgbString + ',0) 100%);';
		css += 'background: -o-linear-gradient(left,  rgba(' + rgbString + ',1) 0%,rgba(' + rgbString + ',0.7) 10%,rgba(' + rgbString + ',0) 33%,rgba(' + rgbString + ',0) 100%);';
		css += 'background: -ms-linear-gradient(left,  rgba(' + rgbString + ',1) 0%,rgba(' + rgbString + ',0.7) 10%,rgba(' + rgbString + ',0) 33%,rgba(' + rgbString + ',0) 100%);';
		css += 'background: linear-gradient(to right,  rgba(' + rgbString + ',1) 0%,rgba(' + rgbString + ',0.7) 10%,rgba(' + rgbString + ',0) 33%,rgba(' + rgbString + ',0) 100%);';
	css += '}';

	$('#artStyle').html(css);
}

function artModeOn() {
	$('body').addClass('arts');
}
