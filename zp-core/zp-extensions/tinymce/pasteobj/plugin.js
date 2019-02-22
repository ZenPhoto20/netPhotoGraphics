/**
 *
 * pasteobj plugin for tinyMCE
 * @author Stephen Billard (sbillard)
 *
 * @Copyright 2014 by Stephen L Billard for use in {@link https://%GITHUB% netPhotoGraphics and derivatives}
 *
 */

tinymce.PluginManager.add('pasteobj', function (editor, url) {
	function _onAction(editor, url) {
		// Open window with a specific url
		editor.windowManager.open({
			title: 'netPhotoGraphics:obj',
			width: 800,
			height: 600,
			buttons: [],
			url: url.replace('/plugins/pasteobj', '/pasteobj/pasteobj.php')
		});
	}

	// Add a button that opens a window
	editor.addButton('pasteobj', {
		icon: "paste",
		tooltip: "netPhotoGraphics:obj",
		onclick: function () {
			_onAction(editor, url);
		}
	});
	// Adds a menu item to the tools menu
	editor.addMenuItem('pasteobj', {
		icon: "paste",
		text: 'netPhotoGraphics:obj...',
		onclick: function () {
			_onAction(editor, url);
		}
	});
});