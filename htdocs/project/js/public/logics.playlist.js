window.playlistLogics = new function() {
	var playPopup;
	var playlistsIdsIndex = {};
	var playlistsList = [];
	var playlistsIndex = {};
	var init = function() {

	};
	var importPlaylists = function() {
		if (window.playlists != undefined) {
			importData(window.playlists);
		}
	};
	var initComponents = function() {
		if (typeof PlaylistControlsComponent != 'undefined') {
			var elements = _('.playlist_controls');
			for (var i = 0; i < elements.length; i++) {
				new PlaylistControlsComponent(elements[i]);
			}
		}
		if (typeof UserPlaylistTableItemComponent != 'undefined') {
			var elements = _('.userplaylists_item');
			for (var i = 0; i < elements.length; i++) {
				new UserPlaylistTableItemComponent(elements[i]);
			}
		}
	};
	this.getPopup = function() {
		if (!playPopup) {
			playPopup = new PlaylistControlsPopupComponent();
		}
		return playPopup;
	};
	this.savePlaylist = function(title, callback) {
		if (window.playlistsElementUrl != undefined) {
			var actionURL = window.playlistsElementUrl.replace(window.rootURL, window.rootURL + 'ajax/');
			var titleName = 'formData[' + window.playlistsElementUrl.replace(window.rootURL, '/') + 'type:playlist/action:receive/][title]';
			var parameters = {
				'id': 'type:playlist/',
				'type': 'playlist',
				'action': 'receive'
			};
			parameters[titleName] = title;

			new JsonRequest(actionURL, function(responseStatus, requestName, responseData) {
				receiveData(responseStatus, requestName, responseData);
				if (callback) {
					callback();
				}
			}, 'addPlaylist', parameters).send();
		}
	};
	this.addToPlaylist = function(playlistId, elementId, callback) {
		if (playlistsIndex[playlistId] != undefined) {
			var actionURL = window.ajaxURL + 'playlistId:' + playlistId + '/';
			var parameters = {
				'id': elementId,
				'action': 'addToPlaylist'
			};

			new JsonRequest(actionURL, function(responseStatus, requestName, responseData) {
				receiveData(responseStatus, requestName, responseData);
				if (callback) {
					callback();
				}
			}, 'addToPlaylist', parameters).send();
		}
	};
	this.removeFromPlaylist = function(playlistId, elementId, callback) {
		if (playlistsIndex[playlistId] != undefined) {
			var actionURL = window.ajaxURL + 'playlistId:' + playlistId + '/';
			var parameters = {
				'id': elementId,
				'action': 'removeFromPlaylist'
			};

			new JsonRequest(actionURL, function(responseStatus, requestName, responseData) {
				receiveData(responseStatus, requestName, responseData);
				if (callback) {
					callback();
				}
			}, 'removeFromPlaylist', parameters).send();
		}
	};
	var importData = function(playlists) {
		for (var i = 0; i < playlists.length; i++) {
			if (!playlistsIndex[playlists[i].id]) {
				playlists[i].id = parseInt(playlists[i].id, 10);
				playlistsIndex[playlists[i].id] = playlists[i];
				playlistsList.push(playlists[i]);
			}
		}
		controller.fireEvent('playlistsUpdated', playlistsList);
	};
	this.getPlaylists = function() {
		return playlistsList;
	};
	this.requestPlaylistIds = function(elementId, callback) {
		var url = '/ajax/id:' + elementId + '/action:getPlaylistIds/';
		var request = new JsonRequest(url, function(responseStatus, requestName, responseData) {
			receiveData(responseStatus, requestName, responseData);
			if (callback) {
				callback();
			}
		}, 'getPlaylistIds');
		request.send();
	};
	var receiveData = function(responseStatus, requestName, responseData) {
		if (requestName == 'getPlaylistIds' || requestName == 'addToPlaylist' || requestName == 'removeFromPlaylist') {
			if (responseData['zxMusic'] != undefined) {
				importPlaylistIds(responseData['zxMusic']);
				musicLogics.importData(responseData['zxMusic']);
			}
			if (responseData['zxPicture'] != undefined) {
				importPlaylistIds(responseData['zxPicture']);
				zxPicturesLogics.importData(responseData['zxPicture']);
			}
			if (responseData['zxProd'] != undefined) {
				importPlaylistIds(responseData['zxProd']);
				// zxProdLogics.importData(responseData['zxProd']);
			}
		}
		if (requestName == 'addPlaylist') {
			if (responseData.playlist) {
				importData(responseData.playlist);
			}
		}
	};

	var importPlaylistIds = function(data) {
		for (var i = 0; i < data.length; i++) {
			playlistsIdsIndex[data[i].id] = [];
			for (var j = 0; j < data[i].playlistIds.length; j++) {
				playlistsIdsIndex[data[i].id].push(parseInt(data[i].playlistIds[j], 10));
			}
		}
	};

	this.getPlaylistIds = function(elementId) {
		if (playlistsIdsIndex[elementId] != undefined) {
			return playlistsIdsIndex[elementId];
		}
		return [];
	};
	window.controller.addListener('initLogics', init);
	window.controller.addListener('initDom', initComponents);
	window.controller.addListener('startApplication', importPlaylists);
};