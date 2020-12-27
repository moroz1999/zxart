window.ZxItemFormComponent = function(componentElement) {
    const init = function() {
        let i;
        let connectionsSelectElements = componentElement.querySelectorAll('.zxitem_form_authors_select');
        for (i = connectionsSelectElements.length; i--;) {
            new AjaxSelectComponent(connectionsSelectElements[i], 'author,authorAlias', 'public');
        }
        let prodReleaseSelectElements = componentElement.querySelectorAll('.zxitem_form_prodrelease_select');
        for (i = prodReleaseSelectElements.length; i--;) {
            new AjaxSelectComponent(prodReleaseSelectElements[i], 'zxProd,zxRelease', 'public');
        }
        let prodSelectElements = componentElement.querySelectorAll('.zxitem_form_prod_select');
        for (i = prodSelectElements.length; i--;) {
            new AjaxSelectComponent(prodSelectElements[i], 'zxProd', 'public');
        }
        let partySelectElements = componentElement.querySelectorAll('.zxitem_form_party_select');
        for (i = partySelectElements.length; i--;) {
            new AjaxSelectComponent(partySelectElements[i], 'party', 'public');
        }
        let authorSelectElement = componentElement.querySelectorAll('.author_form_select');
        for (i = authorSelectElement.length; i--;) {
            new AjaxSelectComponent(authorSelectElement[i], 'author,authorAlias');
        }
        let groupsSelectElement = componentElement.querySelectorAll('.zxitem_form_groups_select');
        for (i = groupsSelectElement.length; i--;) {
            new AjaxSelectComponent(groupsSelectElement[i], 'group,groupAlias');
        }
        let publishersSelect = componentElement.querySelectorAll('.zxitem_form_publishers_select');
        for (i = publishersSelect.length; i--;) {
            new AjaxSelectComponent(publishersSelect[i], 'author,authorAlias,group,groupAlias');
        }
    };
    init();
};