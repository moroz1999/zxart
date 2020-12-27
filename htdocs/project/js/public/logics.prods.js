window.prodsLogics = new function() {
	var prodsIndex = {};
	var self = this;

	var init = function() {
		if (typeof window.prodsList !== 'undefined') {
			self.importData(window.prodsList);
		}
	};

	var initComponents = function() {
		var i;
		// var elements = _('.music_controls_short');
		// for (i = 0; i < elements.length; i++) {
		// 	new MusicShortComponent(elements[i]);
		// }
	};

	this.getProdInfo = function(id) {
		if (typeof prodsIndex[id] !== 'undefined') {
			return prodsIndex[id];
		}
		return false;
	};
	this.importData = function(data) {
		for (var i = 0; i < data.length; i++) {
			prodsIndex[data[i].id] = data[i];
		}
	};
	window.controller.addListener('initLogics', init);
	window.controller.addListener('initDom', initComponents);
};
