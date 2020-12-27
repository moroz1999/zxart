window.UserPlaylistTableItemComponent = function(componentElement) {
	var deleteButton;
	var init = function() {
		if (deleteButton = componentElement.querySelector('.userplaylists_item_delete')) {
			eventsManager.addHandler(deleteButton, 'click', deleteButtonClickHandler);
		}
	};
	var deleteButtonClickHandler = function(event) {
		if (!confirm(translationsLogics.get('playlist.confirmdelete'))) {
			eventsManager.preventDefaultAction(event);
		}
	};

	init();
};