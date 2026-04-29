window.subMenuLogics = new function() {
    var menuList = [];
    var menuIndex = {};
    var childrenListsIndex = {};

    var initLogics = function() {
        if (window.subMenusInfo) {
            // TODO: separate this info
            for (var id in window.subMenusInfo) {
                var menuInfo = window.subMenusInfo[id];
                for (var i = 0; i < menuInfo.length; i++) {
                    menuList.push(menuInfo[i]);
                    menuIndex[menuInfo[i].id] = menuInfo[i];
                }
            }
        }
    };
    var initComponents = function() {
        var elements = _('.submenu_item_haspopup');
        for (var i = 0; i < elements.length; i++) {
            new SubMenuItemComponent(elements[i]);
        }
    };
    this.getMenuInfo = function(menuId) {
        var result = false;
        if (typeof menuIndex[menuId] !== 'undefined') {
            result = menuIndex[menuId];
        }
        return result;
    };
    this.getSubMenuInfo = function(menuId) {
        var result = false;
        if (typeof childrenListsIndex[menuId] == 'undefined') {
            childrenListsIndex[menuId] = [];
            for (var i = 0; i < menuList.length; i++) {
                if (menuList[i].parentId == menuId) {
                    childrenListsIndex[menuId].push(menuList[i]);
                }
            }
            result = childrenListsIndex[menuId];
        }
        return result;
    };
    controller.addListener('initLogics', initLogics);
    controller.addListener('initDom', initComponents);
};