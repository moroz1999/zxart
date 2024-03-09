window.RedirectFormComponent = function(componentElement) {
	var destinationInput;
	var init = function() {
		destinationInput = _(".redirect_destinationinput", componentElement)[0];

		var searchInputElement = _(".redirect_searchinput", componentElement)[0];
		searchInputElement.value = "";

		new AjaxItemSearchComponent(componentElement, searchInputElement, {
			'types': "zxPicture,author,party,game,tag,article,news,folder",
			'apiMode': "public"
		});
	};
	init();
};