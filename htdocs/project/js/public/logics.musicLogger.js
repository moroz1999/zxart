window.musicLogger = new function() {
	var total = 0;
	var resumeTime;
	var currentInfo;
	var checkInterval;
	var logged = false;
	var init = function() {
		controller.addListener('musicPlayResumed', musicPlayResumedHandler);
		controller.addListener('musicPlayPaused', musicTimePausedHandler);
	};
	var musicPlayResumedHandler = function(info) {
		resumeTime = new Date();
		if (!currentInfo || currentInfo.id != info.id) {
			logged = false;
			total = 0;
			currentInfo = info;
		}

		clearInterval(checkInterval);
		checkInterval = setInterval(checkTimer, 1000);
	};

	var musicTimePausedHandler = function() {
		if (resumeTime) {
			var now = new Date();
			total += (now.getTime() - resumeTime.getTime()) / 1000;
		}
		clearInterval(checkInterval);
	};

	var checkTimer = function() {
		if (resumeTime) {
			var now = new Date();
			var timer = (total + (now.getTime() - resumeTime.getTime()) / 1000);
			var percent = (timer / currentInfo.duration);
			if (percent >= 0.75) {
				logPlay(currentInfo.id);
			}

			if (timer > currentInfo.duration) {
				resumeTime = new Date();
				total = 0;
				logged = false;
			}
		}
	};

	var logPlay = function(elementId) {
		if (!logged) {
			logged = true;
			var url = '/ajax/id:' + elementId + '/action:logPlay/';
			var request = new JsonRequest(url, receiveData);
			request.send();
		}
	};
	var receiveData = function(responseStatus, requestName, responseData) {
	};
	window.controller.addListener('initLogics', init);
};
