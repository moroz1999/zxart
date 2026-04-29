window.mobileControlLogics = new function() {
    var init = function() {
        var leftMenuBtn = document.querySelector('.left_menu_toggle');
        var leftMenuHideBtn = document.querySelector('.left_menu_hide_button');
        var tabsMenuBtn = document.querySelector('.tabs_items_toggle');
        var leftMenu = document.querySelector('.left_panel');
        var tabsMenu = document.querySelector('.tabs_list');

        new animationComponent(leftMenuBtn, leftMenu, null, leftPanelAnimation);
        new animationComponent(tabsMenuBtn, tabsMenu, null, tabsListAnimation);
    };

    var tabsListAnimation = function() {
        var tabsMenuBtn = document.querySelector('.tabs_items_toggle');
        var tabslistActiveItem = document.querySelector('.tabs_list_active ');
        var tabsList = document.querySelector('.tabs_list');
        var tabsItemsToggle = document.querySelector('.tabs_content_item ');

        if (tabslistActiveItem && tabsItemsToggle) {
            tabsItemsToggle.innerHTML = tabslistActiveItem.innerHTML;
        }
        eventsManager.addHandler(tabsMenuBtn, 'click', function() {
            if (tabsList.classList.contains('tabs_list_show')) {
                tabsList.classList.remove('tabs_list_show');
                tabsList.classList.add('tabs_list_hide');
            } else if (!tabsList.classList.contains('tabs_list_show')) {
                tabsList.classList.add('tabs_list_show');
                tabsList.classList.remove('tabs_list_hide');
            }
        });
    };

    var leftPanelAnimation = function() {
        var leftMenuBtn = document.querySelector('.left_menu_toggle');
        var leftMenuHideBtn = document.querySelector('.left_menu_hide_button');
        var leftMenu = document.querySelector('.left_panel');

        eventsManager.addHandler(leftMenuBtn, 'click', function() {
            leftMenu.classList.add('left_panel_show');
            leftMenu.classList.remove('left_panel_hide');
        });

        eventsManager.addHandler(leftMenuHideBtn, 'click', function() {
            leftMenu.classList.remove('left_panel_show');
            leftMenu.classList.add('left_panel_hide');
        });
    };

    controller.addListener('initDom', init);
};