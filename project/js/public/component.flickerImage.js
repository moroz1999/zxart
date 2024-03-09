window.FlickerImageComponent = function(componentElement) {
	var gifElement;
	var canvasElement;
	var contextObject;

	var currentFrameNumber;
	var nextFrameNumber;
	var nextFrame;

	var width;
	var height;
	var frames = [];
	var init = function() {
		gifElement = componentElement;
		canvasElement = document.createElement('canvas');
		canvasElement.className = gifElement.className;
		canvasElement.id = gifElement.id;
		attemptGifSourceDownload();
	};
	var attemptGifSourceDownload = function() {
		if (gifElement.complete) {
			if (gifElement.src) {
				ajaxManager.makeRequest({
					'requestURL': gifElement.src,
					'requestType': 'GET',
					'successCallBack': startParsing,
					'overrideMimeType': 'text/plain; charset=x-user-defined'
				})
			}
		}
		else {
			window.setTimeout(attemptGifSourceDownload, 300);
		}
	};

	var startParsing = function(responseText) {
		var parser = new AnimatedGifParser();
		parser.startParsing(responseText, parseCompletedHandler);
	};
	var parseCompletedHandler = function(parsedGifData) {
		gifElement.style.visibility = 'hidden';

		width = parsedGifData.width;
		height = parsedGifData.height;

		canvasElement.width = width;
		canvasElement.height = height;
		contextObject = canvasElement.getContext('2d');

		if (gifElement.parentNode) {
			gifElement.parentNode.insertBefore(canvasElement, gifElement);
			gifElement.parentNode.removeChild(gifElement);
		}
		gifElement = null;

		var tmpCanvas = document.createElement('canvas');
		tmpCanvas.width = width;
		tmpCanvas.height = height;
		var tmpContextObject = tmpCanvas.getContext('2d');

		for (var i = 0; i < parsedGifData.frames.length; i++) {
			tmpContextObject.putImageData(parsedGifData.frames[i].data, 0, 0);

			var imageElement = document.createElement('img');
			imageElement.width = width;
			imageElement.height = height;
			imageElement.src = tmpCanvas.toDataURL();

			frames.push({'data': imageElement, 'delay': parsedGifData.frames[i].delay});
		}
		canvasElement.complete = true;

		currentFrameNumber = 0;
		nextFrameNumber = 0;

		displayNextFrame();
	};
	var displayNextFrame = function() {
		currentFrameNumber = nextFrameNumber;
		nextFrameNumber = currentFrameNumber + 1;
		if (nextFrameNumber >= frames.length) {
			nextFrameNumber = 0;
		}

		nextFrame = frames[nextFrameNumber];

		contextObject.drawImage(nextFrame.data, 0, 0);

		if (window.requestAnimationFrame) {
			window.requestAnimationFrame(displayNextFrame);
		}
		else {
			window.setTimeout(displayNextFrame, 16.66);
		}

	};
	this.getCanvasElement = function() {
		return canvasElement;
	};

	init();
};