{stripdomspaces}
{foreach $subMenus as $subMenu}
    {$subMenuList = $subMenu->getSubMenuList()}

    <div class="submenu_item submenu_item_level_{$level}{if $subMenu->requested} submenu_item_active{/if}">
        <a href="{$subMenu->URL}"
            {if $subMenuList}data-childs="has_childs"{/if}
            class="submenu_item_link submenu_item_link_level_{$level}{if !empty($usePopup) && ($levels == $level)}{if $subMenuList} submenu_item_haspopup{/if}{/if} menuid_{$subMenu->id}{if !empty($verticalPopup)} vertical_popup{/if}">
            <span class="submenu_item_icon"></span>
            <span class="submenu_item_text"
                  role="menuitem">{$subMenu->title}</span>
        </a>{if $subMenuList && !empty($submenuSignatureSelector)}{$submenuSignatureSelector}{/if}
        {if $level < $levels || ($subMenu->requested && $level < $element->maxLevels)}
            {if $subMenuList}
                <div class="submenu_item_submenus{if $subMenu->getCurrentLayout('colorLayout')} bg_color_{$subMenu->getCurrentLayout('colorLayout')}{/if}" role="menu">
                    {if !empty($submenuWrapperClass)}
                    <div class='{$submenuWrapperClass}'>
                        {/if}
                        {include file=$theme->template("subMenuList.items.tpl") level=$level+1 subMenus=$subMenuList}
                        {if !empty($submenuWrapperClass)}
                    </div>
                    {/if}
                </div>
            {/if}
        {/if}
    </div>
{/foreach}
{/stripdomspaces}