window.groupFormLogics = new function() {
	var initComponents = function() {
		var elements = _('.group_form');
		for (var i = elements.length; i--;) {
			new GroupFormComponent(elements[i]);
		}
	};
	window.controller.addListener('initDom', initComponents);
};