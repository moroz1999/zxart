window.MusicFullComponent = function(componentElement) {
	var state;
	var buttonsBlock;
	var previousButton;
	var playButton;
	var stopButton;
	var nextButton;
	var progressBlock;
	var titleBlock;
	var progressBodyBlock;
	var currentInfo;
	var type;
	var ratingsBlock;
	var linkElement;

	var init = function() {
		if (componentElement.className.indexOf('music_controls_full_static') != -1) {
			type = 'static';
		}
		else {
			type = 'fixed';
		}
		state = 'paused';
		createDomStructure();
		controller.addListener('musicPlayPaused', musicPlayPausedHandler);
		controller.addListener('musicPlayResumed', musicPlayResumedHandler);
		controller.addListener('musicTimeUpdated', musicTimeUpdatedHandler);
		refreshStatus();
	};
	var createDomStructure = function() {
		buttonsBlock = document.createElement('div');
		buttonsBlock.className = 'music_controls_buttons';
		componentElement.appendChild(buttonsBlock);

		previousButton = document.createElement('div');
		previousButton.className = 'music_controls_button previous';
		eventsManager.addHandler(previousButton, 'click', previousClickHandler);
		buttonsBlock.appendChild(previousButton);

		playButton = document.createElement('div');
		playButton.className = 'music_controls_button play';
		eventsManager.addHandler(playButton, 'click', playClickHandler);
		buttonsBlock.appendChild(playButton);

		stopButton = document.createElement('div');
		stopButton.className = 'music_controls_button stop';
		eventsManager.addHandler(stopButton, 'click', stopClickHandler);
		buttonsBlock.appendChild(stopButton);

		nextButton = document.createElement('div');
		nextButton.className = 'music_controls_button next';
		eventsManager.addHandler(nextButton, 'click', nextClickHandler);
		buttonsBlock.appendChild(nextButton);

		progressBlock = document.createElement('div');
		progressBlock.className = 'music_controls_progress';
		eventsManager.addHandler(progressBlock, 'click', progressClickHandler);
		componentElement.appendChild(progressBlock);

		progressBodyBlock = document.createElement('div');
		progressBodyBlock.className = 'music_controls_progress_body';
		progressBlock.appendChild(progressBodyBlock);

		titleBlock = document.createElement('div');
		titleBlock.className = 'music_controls_title';
		progressBlock.appendChild(titleBlock);

		linkElement = document.createElement('a');
		linkElement.className = 'music_controls_link';
		linkElement.addEventListener('click', linkElementClick);
		componentElement.appendChild(linkElement);

		ratingsBlock = document.createElement('div');
		ratingsBlock.className = 'music_controls_ratings';
		componentElement.appendChild(ratingsBlock);
	};
	var linkElementClick = function(event) {
		event.preventDefault();
		window.open(linkElement.href);
	};
	var musicPlayPausedHandler = function(info) {
		currentInfo = info;
		state = 'paused';
		refreshStatus();
	};
	var musicPlayResumedHandler = function(info) {
		currentInfo = info;
		state = 'playing';
		refreshStatus();
	};
	var musicTimeUpdatedHandler = function(info) {
		var duration = info.duration;
		var currentTime = info.currentTime;
		progressBodyBlock.style.width = (currentTime / duration) * progressBlock.offsetWidth + 'px';
	};
	var progressClickHandler = function() {
		var positions = domHelper.getElementPositions(progressBlock);
		var mousePositionX = mouseTracker.mouseX;
		var percent = (mousePositionX - positions.x) / progressBlock.offsetWidth;

		musicLogics.seekPercent(percent);
	};
	var playClickHandler = function() {
		if (state == 'paused') {
			musicLogics.resume(currentInfo.id);
		}
		else {
			musicLogics.pause();
		}
	};
	var stopClickHandler = function() {
		musicLogics.stop();
	};
	var previousClickHandler = function() {
		musicLogics.previous();
	};
	var nextClickHandler = function() {
		musicLogics.next();
	};
	var refreshStatus = function() {
		if (currentInfo) {
			titleBlock.innerHTML = currentInfo.author + ' - ' + currentInfo.title;
			componentElement.style.display = 'block';
			ratingsBlock.innerHTML = '';
			linkElement.href = currentInfo.url;
			var voteComponent = new VoteControls(null, currentInfo);
			ratingsBlock.appendChild(voteComponent.getComponentElement());

			var playListComponent = new PlaylistControlsComponent(null, currentInfo.id);
			ratingsBlock.appendChild(playListComponent.getComponentElement());
		}
		else if (type == 'fixed') {
			componentElement.style.display = 'none';
		}
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