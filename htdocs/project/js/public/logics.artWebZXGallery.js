window.feedbackFormLogics = new function() {
	var initComponents = function() {
		var elements;
		elements = _('.gallery_pictures');
		for (var i = 0; i < elements.length; i++) {
			new artWebZXGallery(elements[i]);
		}
		elements = _('.settings_block');
		for (var i = 0; i < elements.length; i++) {
			new SettingsBlockComponent(elements[i]);
		}
		elements = _('.flicker_image');
		for (var i = 0; i < elements.length; i++) {
			new FlickerImageComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};