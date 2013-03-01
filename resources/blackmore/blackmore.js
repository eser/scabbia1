function insertHtmlToParent(html) {
	var data = window.parent.cleditorData;

	data.editor.execCommand('inserthtml', html, null, data.button);
	data.editor.focus();
	window.parent.Shadowbox.close();
}

function insertImageToParent(src) {
	insertHtmlToParent('<img src="' + src + '" />');
}

$l.ready(function() {
	$('#pageMiddleSidebarToggle').click(function() {
		var element = $('#pageMiddleSidebar');
		if(element.css('display') == 'none') {
			element.css('display', '');
			$l.cookies.set('sidebar', 'true');
		}
		else {
			element.css('display', 'none');
			$l.cookies.set('sidebar', 'false');
		}

		return false;
	});

	var cookie = $l.cookies.get('sidebar');
	if(cookie != null && cookie == 'false') {
		$('#pageMiddleSidebar').css('display', 'none');
	}

	$('.cleditor').cleditor({ width : '98%', height : '500px' });
	$.cleditor.buttons.image.buttonClick = function(e, data) {
		window.cleditorData = data;

		Shadowbox.open({
			content : location.origin + $l.baseLocation + '/editor/fileselect',
			player : 'iframe',
			title : _fileselectTitle,
			height : 350,
			width : 900
		});

		return false;
	};

	$('.tablesorter')
			.tablesorter({ widgets : ['zebra'], disableSortingOnLastColumn : true })
			.tablesorterPager({ size : 25, container : $('#tablepager') });

	$('.file').change(function() {
		var me = $(this);
		var parent = me.parent();
		parent.find('.file-text').val(me.val());
	});

	$('.delete').click(function() {
		if(confirm(_deleteConfirmation) == false) {
			return false;
		}
	});

	Shadowbox.init({
		// handleOversize: 'drag',
		modal : true
	});

	$('.tipsyFocus').tipsy({fade : true, gravity : 'w', trigger : 'focus'});
	$('.tipsyHover').tipsy({fade : true, gravity : 'w', live : true});

	$l.ui.datepicker($('.input_datetime'), {})
});