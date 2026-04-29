{if $element->getSubMenuList()}
    {if $element->displayHeadingAutomatically && empty($noTitle)}
        {capture assign="moduleTitle"}{$currentMainMenu->title}{/capture}
    {else}
        {if $element->title && empty($noTitle)}
            {capture assign="moduleTitle"}{$element->title}{/capture}
        {/if}
    {/if}
    {capture assign="moduleContent"}
        {if $element->popup}
            <script>
                window.subMenusInfo = window.subMenusInfo || {ldelim}{rdelim};
                window.subMenusInfo['{$element->id}'] = {json_encode($element->getMenusInfo())};
            </script>
        {/if}
        <nav class='submenu_items_block'>
            {include file=$theme->template("subMenuList.items.tpl") level=1 levels=$element->levels usePopup=$element->popup subMenus=$element->getSubMenuList()}
        </nav>
    {/capture}
    {assign moduleTitleClass "submenu_column_title"}
    {assign moduleClass "submenu_block submenu_column{if $element->layout} submenu_column_{$element->layout}{/if}"}
    {assign moduleContentClass "submenu_column_content submenu_content"}
    {include file=$theme->template("component.columnmodule.tpl")}
{/if}