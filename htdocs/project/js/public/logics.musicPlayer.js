window.musicPlayerLogics = new function() {
	var currentMusicInfo;
	var currentId;
	var currentPlaylistName;
	var state;
	var loaded;
	var audioElement;
	var playlists = {};
	var self = this;
	var activePlayMode = 'play';

	var init = function() {
		audioElement = document.createElement('audio');

		broadcastLogics.addListener(broadcastListener);

		eventsManager.addHandler(audioElement, 'ended', onEndedHandler);
		eventsManager.addHandler(audioElement, 'timeupdate', onTimeupdateHandler);
		loaded = false;
		state = 'stopped';
	};

	var broadcastListener = function(type) {
		if (type == 'playerWindowCheck') {
			broadcastMessage('playerWindowExists', true);
		}
	};

	var mp3Supported = function() {
		var result = false;
		if (audioElement.canPlayType && audioElement.canPlayType('audio/mpeg')) {
			result = true;
		}
		return result;
	};

	this.setActivePlayMode = function(newMode) {
		activePlayMode = newMode;
	};

	this.resume = function(newId) {
		if (!newId) {
			newId = currentId;
		}

		currentMusicInfo = getMusicInfo(newId);
		if (currentMusicInfo) {
			if (currentId != newId) {
				audioElement.pause();
				currentId = newId;
				broadcastMessage('musicPlayPreload', currentMusicInfo);
				if (mp3Supported()) {
					audioElement.src = currentMusicInfo.mp3FilePath;
					playSound();
				}
			}
			else if (state == 'stopped' || state == 'paused') {
				broadcastMessage('musicPlayResumed', currentMusicInfo);
				audioElement.play();
			}
			state = 'playing';
		}
	};
	var playSound = function() {
		audioElement.play();
		broadcastMessage('musicPlayResumed', currentMusicInfo);
	};

	var getMusicInfo = function(id) {
		for (var i = 0; i < playlists[currentPlaylistName].length; i++) {
			if (playlists[currentPlaylistName][i].id == id) {
				return playlists[currentPlaylistName][i];
			}
		}
		return false;
	};

	this.pause = function() {
		if (audioElement) {
			state = 'paused';
			audioElement.pause();
		}
		broadcastMessage('musicPlayPaused', currentMusicInfo);
	};
	this.previous = function() {
		var previousSong;
		if (currentId) {
			for (var i = playlists[currentPlaylistName].length - 1; i >= 0; i--) {
				if (playlists[currentPlaylistName][i].id == currentId) {
					if (playlists[currentPlaylistName][i - 1] != undefined) {
						previousSong = playlists[currentPlaylistName][i - 1];
					}
					break;
				}
			}
		}
		if (previousSong) {
			self.resume(previousSong.id);
		}
	};
	this.next = function() {
		var nextSong;
		if (currentId) {
			for (var i = 0; i < playlists[currentPlaylistName].length; i++) {
				if (playlists[currentPlaylistName][i].id == currentId) {
					if (playlists[currentPlaylistName][i + 1] != undefined) {
						nextSong = playlists[currentPlaylistName][i + 1];
					}
					else if (activePlayMode == 'repeat') {
						nextSong = playlists[currentPlaylistName][0];
					}
					break;
				}
			}
		}
		if (nextSong) {
			self.resume(nextSong.id);
		}
		broadcastMessage('musicPlayNextPressed', currentPlaylistName);

	};

	this.stop = function() {
		state = 'stopped';
		if (audioElement) {
			audioElement.pause();
			audioElement.currentTime = 0;
		}
		broadcastMessage('musicPlayPaused', currentMusicInfo);
	};

	this.getLoaded = function() {
		return loaded;
	};

	var startApplicationHandler = function() {
		loaded = true;
	};

	this.addPlaylist = function(playlistName, playlist) {
		currentPlaylistName = playlistName;
		playlists[playlistName] = playlist;

		broadcastMessage('musicPlayListChanged', currentPlaylistName);
	};
	this.seek = function(percent) {
		audioElement.currentTime = audioElement.duration * percent;
		currentMusicInfo.currentTime = audioElement.currentTime;
		broadcastMessage('musicPlaySeek', currentMusicInfo);
	};

	var onEndedHandler = function() {
		//imitate full stop in case there is no more melodies in list.
		self.stop();

		broadcastMessage('musicPlayEnded', currentMusicInfo);

		//attempt to play next tune in playlist
		self.next();
	};
	var onTimeupdateHandler = function() {
		currentMusicInfo.currentTime = audioElement.currentTime;
		currentMusicInfo.duration = audioElement.duration;
		broadcastMessage('musicTimeUpdated', currentMusicInfo);
	};
	var broadcastMessage = function(type, value) {
		broadcastLogics.broadcastMessage(type, value);

		//manually send the message to local musicLogics, since storage event won't trigger in same window.
		musicLogics.setBroadcastInformation(type, value);
	};

	window.controller.addListener('initLogics', init);
	window.controller.addListener('startApplication', startApplicationHandler);
};
