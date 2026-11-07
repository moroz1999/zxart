window.zxPicturesLogics = new function() {
	var replaceFlickering = false;
	var picturesIndex = {};
	var init = function() {
		detectReplaceFlickering();
	};
	var detectReplaceFlickering = function() {
		var ua = navigator.userAgent.toLowerCase();
		if (ua.indexOf('windows nt 5.1') != false) {
			replaceFlickering = true;
		}
	};
	this.getReplaceFlickering = function() {
		return replaceFlickering;
	};
	var receiveData = function(responseStatus, requestName, responseData) {
	};
	this.logView = function(elementId) {
		var url = '/ajax/id:' + elementId + '/action:logView/';
		var request = new JsonRequest(url, receiveData);
		request.send();
	};
	this.importData = function(data) {
		for (var i = 0; i < data.length; i++) {
			picturesIndex[data[i].id] = data[i];
		}
	};

	window.controller.addListener('initLogics', init);
};