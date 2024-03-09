window.broadcastLogics = new function() {
	var messageId = 0;
	var listeners = [];
	var init = function() {
		eventsManager.addHandler(window, 'storage', storageHandler);
	};
	this.addListener = function(callBack) {
		listeners.push(callBack);
	};
	this.broadcastMessage = function(type, value) {
		messageId++;
		var message = {'type': type, 'value': value, messageId: 'messageId'};
		localStorage.setItem('broadcastmessage', JSON.stringify(message));
	};
	var storageHandler = function(event) {
		if (event.key == 'broadcastmessage') {
			var message = JSON.parse(event.newValue);
			var type = message.type;

			for (var i = 0; i < listeners.length; i++) {
				listeners[i](type, message.value);
			}
		}
	};
	init();
};