window.SubMenusPopupItemComponent = function(menuInfo) {
	var self = this;
	this.componentElement = false;

	var init = function() {
		self.componentElement = document.createElement('a');
		self.componentElement.href = menuInfo.URL;
		self.componentElement.className = 'submenuitem_popup_item';

		var subElement1 = document.createElement('div');
		subElement1.className = 'submenuitem_popup_item_left';
		self.componentElement.appendChild(subElement1);
		var subElement2 = document.createElement('div');
		subElement2.className = 'submenuitem_popup_item_center';
		self.componentElement.appendChild(subElement2);
		var contentElement = document.createElement('div');
		contentElement.className = 'submenuitem_popup_item_content';
		self.componentElement.appendChild(contentElement);

		contentElement.innerHTML = menuInfo.title;
	};
	init();
};