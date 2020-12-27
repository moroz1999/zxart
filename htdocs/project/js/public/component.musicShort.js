window.MusicShortComponent = function(componentElement) {
	var id;
	var state;
	var playButton;
	var init = function() {
		state = 'paused';
		if (id = parseInt(componentElement.className.split('elementid_')[1], 10)) {
			createDomStructure();

			componentElement.style.display = 'inline-block';

			refreshStatus();
			controller.addListener('musicPlayPaused', musicPlayPausedHandler);
			controller.addListener('musicPlayResumed', musicPlayResumedHandler);
		}
	};
	var createDomStructure = function() {
		playButton = document.createElement('div');
		playButton.className = 'music_controls_button play';
		eventsManager.addHandler(playButton, 'click', playClickHandler);
		componentElement.appendChild(playButton);
	};
	var musicPlayPausedHandler = function(info) {
		if (id == info.id) {
			state = 'paused';
		}

		refreshStatus();
	};
	var musicPlayResumedHandler = function(info) {
		if (id == info.id) {
			state = 'playing';
		}
		else {
			state = 'paused';
		}
		refreshStatus();
	};
	var playClickHandler = function() {
		if (state == 'paused') {
			musicLogics.setCurrentPlaylist(window.currentElementId, window.musicList);
			musicLogics.resume(id);
		}
		else {
			musicLogics.pause(id);
		}
		refreshStatus();
	};
	var refreshStatus = function() {
		if (state == 'paused') {
			domHelper.removeClass(playButton, 'pause');
			domHelper.addClass(playButton, 'play');
		}
		else {
			domHelper.addClass(playButton, 'pause');
			domHelper.removeClass(playButton, 'play');

		}
	};
	init();
};