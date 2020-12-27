window.musicRadioLogics = new function() {
	var radioMode = 'discover';

	var initLogics = function() {
		controller.addListener('musicPlayNextPressed', musicPlayNextPressedHandler);
	};

	var musicPlayNextPressedHandler = function(playListName) {
		if (playListName == 'random') {
			sendNextSongRequest();
		}
	};

	this.startPlay = function(newMode) {
		radioMode = newMode;
		sendNextSongRequest();
	};

	var sendNextSongRequest = function() {
		var url = '/randomTune/type:' + radioMode + '/';
		var request = new JsonRequest(url, receiveData);
		request.send();
	};

	var receiveData = function(responseStatus, requestName, responseData) {
		if (responseData) {
			if (responseData.zxMusic && responseData.zxMusic[0]) {
				var zxMusicData = responseData.zxMusic[0];
				musicLogics.setCurrentPlaylist('random', [zxMusicData]);
				musicLogics.resume(zxMusicData.id);
			}
		}
	};

	window.controller.addListener('initLogics', initLogics);
};