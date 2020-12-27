window.PictureDetailsComponent = function(componentElement) {
	var id;
	var init = function() {
		if (id = componentElement.dataset.id){
			window.zxPicturesLogics.logView(id);
		}
	};
	
	init();
};