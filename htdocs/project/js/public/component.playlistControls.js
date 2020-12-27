window.PlaylistControlsComponent = function(componentElement, id) {
	var init = function() {
		if (!id) {
			id = parseInt(componentElement.className.split('elementid_')[1], 10);
		}
		if (id) {
			createDomStructure();
		}
	};
	var createDomStructure = function() {
		if (!componentElement) {
			componentElement = document.createElement('div');
			componentElement.className = 'playlist_controls';
		}
		if (window.userName == 'anonymous') {
			var popup = new TipPopupComponent(componentElement, window.translationsLogics.get('playlist.registration_required'));
			popup.setDisplayDelay(100);
		}
		else {
			eventsManager.addHandler(componentElement, 'click', clickHandler);
		}

	};
	var clickHandler = function(event) {
		event.preventDefault();
		eventsManager.cancelBubbling(event);
		var popup = playlistLogics.getPopup();
		popup.setReferenceElement(componentElement);
		popup.setElementId(id);
		popup.display();
	};
	this.getComponentElement = function() {
		return componentElement;
	};
	init();
};
window.PlaylistControlsPopupComponent = function() {
	var self = this;
	var componentElement;
	var listElement;
	var bottomElement;
	var inputElement;
	var addButtonElement;

	var referenceElement;
	var elementId;
	var listItems;
	var playlistIds;
	var init = function() {
		createDomStructure();
		self.initAutoAdjustedPopup({
			'componentElement': componentElement
		});
		controller.addListener('playlistsUpdated', playlistsUpdatedHandler);
		playlistsUpdatedHandler();
	};
	var playlistsUpdatedHandler = function() {
		listItems = [];
		var playlists = playlistLogics.getPlaylists();
		while (listElement.firstChild) {
			listElement.firstChild.parentNode.removeChild(listElement.firstChild);
		}
		for (var i = 0; i < playlists.length; i++) {
			var listItem = new PlaylistControlsListItemComponent(playlists[i], self);
			listElement.appendChild(listItem.getComponentElement());
			listItems.push(listItem);
		}
		if (elementId) {
			self.updateElementPlaylistIds();
		}
	};
	var createDomStructure = function() {
		componentElement = self.makeElement('div', 'playlist_controls_popup');
		listElement = self.makeElement('div', 'playlist_controls_list', componentElement);
		bottomElement = self.makeElement('div', 'playlist_controls_bottom', componentElement);
		inputElement = self.makeElement('input', {
			'className': 'playlist_controls_input input_components',
			'type': 'text',
			'placeholder': translationsLogics.get('playlist.add_placeholder')
		}, bottomElement);
		addButtonElement = self.makeElement('input', {
			'type': 'button',
			'className': 'playlist_controls_button button',
			'value': translationsLogics.get('playlist.add_submit')
		}, bottomElement);
		document.body.appendChild(componentElement);
		eventsManager.addHandler(document.body, 'click', documentClickHandler);
		eventsManager.addHandler(componentElement, 'click', componentClickHandler);
		eventsManager.addHandler(addButtonElement, 'click', addButtonClickHandler);
	};
	var addButtonClickHandler = function() {
		var title = inputElement.value.trim();
		if (title != '') {
			playlistLogics.savePlaylist(title, successfulSaveCallback);
		}
	};
	var successfulSaveCallback = function() {
		inputElement.value = '';
	};
	this.display = function() {
		playlistLogics.requestPlaylistIds(elementId, playlistIdsRequestCallback);
	};
	var playlistIdsRequestCallback = function() {
		self.updateElementPlaylistIds();
		displayComponent();
	};
	this.updateElementPlaylistIds = function() {
		playlistIds = playlistLogics.getPlaylistIds(elementId);
		if (playlistIds) {
			for (var i = 0; i < listItems.length; i++) {
				if (playlistIds.indexOf(listItems[i].getId()) >= 0) {
					listItems[i].setAdded();
				}
				else {
					listItems[i].unsetAdded();
				}
			}
		}
	};
	var displayComponent = function() {
		if (referenceElement) {
			self.adjustPositionAndSize(referenceElement);
		}
		componentElement.style.display = 'block';

		inputElement.focus();
	};
	this.hide = function() {
		componentElement.style.display = 'none';
	};
	this.setReferenceElement = function(newReferenceElement) {
		referenceElement = newReferenceElement;
	};
	var documentClickHandler = function() {
		self.hide();
	};
	var componentClickHandler = function(event) {
		eventsManager.cancelBubbling(event);
	};
	this.getElementId = function() {
		return elementId;
	};
	this.setElementId = function(id) {
		if (elementId != id) {
			elementId = id;
			playlistIds = false;
		}
	};
	init();
};
AutoAdjustedPopup.call(PlaylistControlsPopupComponent.prototype);
DomHelperMixin.call(PlaylistControlsPopupComponent.prototype);
DomElementMakerMixin.call(PlaylistControlsPopupComponent.prototype);

window.PlaylistControlsListItemComponent = function(info, parentObject) {
	var self = this;
	var componentElement;
	var titleElement;
	var iconElement;
	var descriptionElement;
	var added = false;
	var init = function() {
		componentElement = self.makeElement('div', 'playlist_controls_item');
		titleElement = self.makeElement('div', 'playlist_controls_item_title', componentElement);
		iconElement = self.makeElement('div', 'playlist_controls_item_icon', titleElement);
		descriptionElement = self.makeElement('div', 'playlist_controls_item_description', titleElement);
		var titleValueElement = self.makeElement('span', '', titleElement);
		titleValueElement.innerHTML += info.title;

		eventsManager.addHandler(componentElement, 'click', clickHandler);
	};
	this.getComponentElement = function() {
		return componentElement;
	};
	this.getId = function() {
		return info.id;
	};
	this.setAdded = function() {
		added = true;
		iconElement.style.display = 'block';
		descriptionElement.innerHTML = translationsLogics.get('playlist.remove');
	};
	this.unsetAdded = function() {
		added = false;
		iconElement.style.display = 'none';
		descriptionElement.innerHTML = translationsLogics.get('playlist.add');
	};
	var clickHandler = function() {
		if (added) {
			playlistLogics.removeFromPlaylist(info.id, parentObject.getElementId(), updateCallback);
		}
		else {
			playlistLogics.addToPlaylist(info.id, parentObject.getElementId(), updateCallback);
		}
	};
	var updateCallback = function() {
		parentObject.updateElementPlaylistIds();
	};
	init();
};
DomElementMakerMixin.call(PlaylistControlsListItemComponent.prototype);
