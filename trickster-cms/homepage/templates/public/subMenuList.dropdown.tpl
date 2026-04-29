<nav class="submenulist_dropdown" role="navigation">
	<select class="submenulist_dropdown_select dropdown_placeholder redirect_select" role="menu">
		{include file=$theme->template('subMenuList.dropdown.items.tpl') level=1 levels=$element->levels usePopup=false subMenus=$element->getSubMenuList()}
	</select>
</nav>