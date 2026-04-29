//deprecated
// use subMenuLogics
window.mainMenuLogics = new function() {
    var initLogics = function() {
        if (window.menusInfo) {
            for (var i = 0; i < window.menusInfo.length; i++) {
                menuList.push(window.menusInfo[i]);
                menuIndex[window.menusInfo[i].id] = window.menusInfo[i];
            }
        }
    };
    var initComponents = function() {
        var elements = _('.menuitem_block');
        for (var i = 0; i < elements.length; i++) {
            new MainMenuComponent(elements[i]);
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
    var self = this;

    var menuList = [];
    var menuIndex = {};
    var childrenListsIndex = {};

    controller.addListener('initLogics', initLogics);
    controller.addListener('initDom', initComponents);
};