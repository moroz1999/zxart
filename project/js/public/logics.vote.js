window.votesLogics = new function() {
	var picturesInfo = [];
	var initLogics = function() {
		if (window.picturesList != undefined) {
			for (var i = 0; i < window.picturesList.length; i++) {
				picturesInfo[window.picturesList[i].id] = window.picturesList[i];
			}
		}
	};
	var initComponents = function() {
		var elements = _('.vote_controls');
		for (var i = 0; i < elements.length; i++) {
			if (elements[i].id.search('vote_id_') != -1) {
				var elementId = elements[i].id.split('_')[2];
				if (picturesInfo[elementId] != undefined) {
					new VoteControls(elements[i], picturesInfo[elementId]);
				} else if (window.musicLogics.getMusicInfo(elementId)) {
					new VoteControls(elements[i], window.musicLogics.getMusicInfo(elementId));
				} else if (window.prodsLogics.getProdInfo(elementId)) {
					new VoteControls(elements[i], window.prodsLogics.getProdInfo(elementId));
				}
			}
		}
	};
	var receiveData = function(responseStatus, requestName, responseData) {
		if (responseData) {
			if (responseData.zxPicture && responseData.zxPicture[0]) {
				var zxPictureData = responseData.zxPicture[0];
				controller.fireEvent('voteRecalculated', zxPictureData);
			}
			if (responseData.zxMusic && responseData.zxMusic[0]) {
				var zxMusicData = responseData.zxMusic[0];
				controller.fireEvent('voteRecalculated', zxMusicData);
			}
			if (responseData.zxProd && responseData.zxProd[0]) {
				var zxProdData = responseData.zxProd[0];
				controller.fireEvent('voteRecalculated', zxProdData);
			}
			if (responseData.comment && responseData.comment[0]) {
				var commentData = responseData.comment[0];
				controller.fireEvent('voteRecalculated', commentData);
			}
		}
	};
	this.makeVote = function(elementId, vote) {
		var url = '/ajax/id:' + elementId + '/action:vote/value:' + vote + '/';
		var request = new JsonRequest(url, receiveData);
		request.send();
	};
	window.controller.addListener('initLogics', initLogics);
	window.controller.addListener('initDom', initComponents);
};

