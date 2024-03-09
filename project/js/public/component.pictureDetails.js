window.PictureDetailsComponent = function(componentElement) {
	var id;
	var init = function() {
		if (id = componentElement.dataset.id){
			window.zxPicturesLogics.logView(id);
			if (typeof ym !== "undefined"){
				ym(94686067,'reachGoal','viewimage')
			}
		}
	};
	
	init();
};