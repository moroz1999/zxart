function VoteControls(componentElement, elementInfo) {
	var voteValueElement;
	var starsElement;
	var userVoteElement;
	var skipElement;
	var starElements = [];
	var starsHovered = 0;
	var maxVote = 5;
	var votingEnabled;

	var init = function() {
		createDomStructure();
		if (window.userName == 'anonymous') {
			var popup = new TipPopupComponent(componentElement, window.translationsLogics.get('label.registration_required'));
			popup.setDisplayDelay(100);
			votingEnabled = false;
		} else {
			votingEnabled = true;
			eventsManager.addHandler(starsElement, 'mouseout', mouseOutHandler);
			controller.addListener('voteRecalculated', voteCalculatedHandler);
		}
	};
	var createDomStructure = function() {
		if (!componentElement) {
			componentElement = document.createElement('div');
			componentElement.className = 'vote_controls';
		}

		userVoteElement = document.createElement('div');
		userVoteElement.className = 'vote_controls_uservote';
		componentElement.appendChild(userVoteElement);

		starsElement = document.createElement('div');
		starsElement.className = 'vote_controls_stars';
		componentElement.appendChild(starsElement);

		voteValueElement = document.createElement('div');
		voteValueElement.className = 'vote_controls_value';
		starsElement.appendChild(voteValueElement);

		skipElement = document.createElement('div');
		skipElement.className = 'vote_controls_skip';
		skipElement.innerHTML = 'x';
		componentElement.appendChild(skipElement);

		for (var i = 0; i < maxVote; i++) {
			var starElement = document.createElement('div');
			starElement.className = 'vote_controls_star';
			starElement.style.left = i + 'em';
			starsElement.appendChild(starElement);

			if (window.userName != 'anonymous') {
				eventsManager.addHandler(starElement, 'click', function(i) {
					return function() {
						clickHandler(i + 1);
					};
				}(i));
				eventsManager.addHandler(starElement, 'mouseover', function(i) {
					return function() {
						mouseOverHandler(i + 1);
					};
				}(i));

				eventsManager.addHandler(skipElement, 'click', skip);

			}
			starElements.push(starElement);
		}
		refreshValue();
	};
	var voteCalculatedHandler = function(newElementInfo) {
		if (elementInfo.id == newElementInfo.id) {
			elementInfo = newElementInfo;
			refreshValue();
		}
	};
	var mouseOverHandler = function(number) {
		starsHovered = number;
		refresh();
	};
	var mouseOutHandler = function() {
		starsHovered = 0;
		refresh();
	};
	var clickHandler = function(number) {
		if (votingEnabled) {
			votesLogics.makeVote(elementInfo.id, number)
		}
	};
	var skip = function() {
		if (votingEnabled) {
			votesLogics.makeVote(elementInfo.id, 0)
		}
	};
	var refreshValue = function() {
		voteValueElement.style.width = elementInfo.votePercent + '%';
		starsElement.className = 'vote_controls_stars';
		if (elementInfo.userVote && elementInfo.userVote > 0) {
			userVoteElement.innerHTML = elementInfo.userVote + ' <span class="vote_controls_uservote_delimiter">/</span> ';
		}
		else {
			userVoteElement.innerHTML = '';
		}
		if (elementInfo.userVote === 0 || elementInfo.userVote === '0') {
			skipElement.className = 'vote_controls_skip active';
		} else {
			skipElement.className = 'vote_controls_skip';
		}
	};
	var refresh = function() {
		for (var i = 0; i < starElements.length; i++) {
			if (i < starsHovered) {
				starElements[i].className = 'vote_controls_star vote_controls_star_gold';
			}
			else {
				starElements[i].className = 'vote_controls_star';
			}
		}
	};
	this.getComponentElement = function() {
		return componentElement;
	};

	init();
}