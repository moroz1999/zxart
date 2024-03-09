window.stagesAnimationLogics = new function() {
	var initDom = function() {
		if (isCanvasSupported() && isBinaryDownloadSupported()) {
			var elements = _('.stage_animation_gif');
			for (var i = 0; i < elements.length; i++) {
				new StagesAnimationComponent(elements[i]);
			}
		}
	};
	var isBinaryDownloadSupported = function() {
		if (window.XMLHttpRequest) {
			var request = new XMLHttpRequest();
			if (request.overrideMimeType) {
				return true;
			}

		}
		return false;
	};
	var isCanvasSupported = function() {
		var element = document.createElement('canvas');
		return !!(element.getContext && element.getContext('2d'));
	};
	controller.addListener('initDom', initDom);
};