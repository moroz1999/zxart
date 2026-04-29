window.spoilerLogics = new function() {
	let spoilers = [];
	let initComponents = function() {
		let elements = _('.spoiler_component');
		for (let i = 0; i < elements.length; i++) {
			spoilers.push(new SpoilerComponent(elements[i]));
		}

	};
	controller.addListener('initDom', initComponents);
};