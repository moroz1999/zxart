window.musicLogics = new function() {
	var currentPlaylist;
	var currentPlaylistName;
	var musicIndex = {};
	var self = this;

	var init = function() {
		broadcastLogics.addListener(self.setBroadcastInformation);
		if (typeof window.musicList !== 'undefined') {
			self.importData(window.musicList);
		}
	};

	this.setCurrentPlaylist = function(name, playlist) {
		currentPlaylist = playlist;
		currentPlaylistName = name;
		self.importData(playlist);
	};
	var initComponents = function() {
		var i;
		var elements = _('.music_controls_short');
		for (i = 0; i < elements.length; i++) {
			new MusicShortComponent(elements[i]);
		}
		elements = _('.music_controls_full');
		for (i = 0; i < elements.length; i++) {
			new MusicFullComponent(elements[i]);
		}
	};
	var startApplicationHandler = function() {
		if (document.location.href.search('autoplay') > 0) {
			if (currentPlaylist.length > 0) {
				var first = currentPlaylist[0];
				self.resume(first.id);
			}
		}
	};

	this.seekPercent = function(percent) {
		musicPlayerLogics.seek(percent);
	};

	this.resume = function(newId) {
		if (currentPlaylist) {
			musicPlayerLogics.addPlaylist(currentPlaylistName, currentPlaylist);
		}
		musicPlayerLogics.resume(newId);
	};

	this.pause = function() {
		musicPlayerLogics.pause();
	};

	this.previous = function() {
		musicPlayerLogics.previous();
	};

	this.next = function() {
		musicPlayerLogics.next();
	};

	this.stop = function() {
		musicPlayerLogics.stop();
	};

	this.setBroadcastInformation = function(type, value) {
		controller.fireEvent(type, value);
		if (type == 'musicPlayListChanged') {
			currentPlaylist = false;
			currentPlaylistName = false;
		}
	};

	this.getMusicInfo = function(id) {
		if (typeof musicIndex[id] != 'undefined') {
			return musicIndex[id];
		}
		return false;
	};
	this.importData = function(data) {
		for (var i = 0; i < data.length; i++) {
			musicIndex[data[i].id] = data[i];
		}
	};

	this.setPlayMode = function(playMode) {
		musicPlayerLogics.setActivePlayMode(playMode);
	};

	window.controller.addListener('initLogics', init);
	window.controller.addListener('initDom', initComponents);
	window.controller.addListener('startApplication', startApplicationHandler);
};
