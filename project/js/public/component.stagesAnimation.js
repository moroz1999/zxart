window.StagesAnimationComponent = function(gifElement) {
	var canvasElement;
	var contextObject;

	var currentFrameNumber;
	var currentFrame;
	var nextFrameNumber;
	var nextFrame;
	var nextFrameDelay;
	var transitionOpacity = 0;

	var width;
	var height;
	var frames = [];

	var init = function() {
		attemptGifSourceDownload();
	};
	var attemptGifSourceDownload = function() {
		gifElement.style.visibility = 'hidden';
		if (gifElement.src) {
			ajaxManager.makeRequest({
				'requestURL': gifElement.src,
				'requestType': 'GET',
				'successCallBack': startParsing,
				'overrideMimeType': 'text/plain; charset=x-user-defined'
			})
		}
	};
	var startParsing = function(responseText) {
		var parser = new AnimatedGifParser();
		parser.startParsing(responseText, parseCompletedHandler);
	};
	var parseCompletedHandler = function(parsedGifData) {
		width = parsedGifData.width;
		height = parsedGifData.height;

		canvasElement = document.createElement('canvas');
		canvasElement.style.display = 'block';
		canvasElement.width = width;
		canvasElement.height = height;
		contextObject = canvasElement.getContext('2d');

		gifElement.parentNode.insertBefore(canvasElement, gifElement);
		gifElement.parentNode.removeChild(gifElement);
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

			frames.push({'data': imageElement, 'delay': parsedGifData.frames[i].delay * 10});
		}

		currentFrameNumber = 0;
		nextFrameNumber = 0;

		displayInstantFrame(currentFrameNumber);
		window.setTimeout(displayNextFrame, frames[currentFrameNumber].delay);
	};
	var displayInstantFrame = function(number) {
		var frame = frames[number];
		contextObject.drawImage(frame.data, 0, 0);
	};
	var displayNextFrame = function() {
		currentFrameNumber = nextFrameNumber;
		nextFrameNumber = currentFrameNumber + 1;
		if (nextFrameNumber >= frames.length) {
			nextFrameNumber = 0;
		}

		currentFrame = frames[currentFrameNumber];
		nextFrame = frames[nextFrameNumber];
		if (nextFrameNumber == frames.length - 1) {
			nextFrameDelay = 2500;
		}
		else {
			nextFrameDelay = nextFrame.delay;
		}

		animateTransition();
	};
	var animateTransition = function() {
		contextObject.globalAlpha = 1;
		contextObject.drawImage(currentFrame.data, 0, 0);
		contextObject.globalAlpha = transitionOpacity;
		contextObject.drawImage(nextFrame.data, 0, 0);

		if (transitionOpacity >= 1) {
			transitionOpacity = 0;
			window.setTimeout(displayNextFrame, nextFrameDelay);
		}
		else {
			transitionOpacity = transitionOpacity + 0.03;
			if (window.requestAnimationFrame) {
				window.requestAnimationFrame(animateTransition);
			}
			else {
				window.setTimeout(animateTransition, 17);
			}
		}
	};
	init();
};