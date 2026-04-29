<div class="submenu_block submenu_footer{if isset($className)} {$className}{/if}">
	{if $element->popup}
		<script>

			window.subMenusInfo = window.subMenusInfo || {ldelim}{rdelim};
			window.subMenusInfo['{$element->id}'] = {json_encode($element->getMenusInfo())};

		</script>
	{/if}
	<nav class='submenu_content'>
		<div class='submenu_items_block'>
			{include file=$theme->template("subMenuList.items.tpl") level=1 levels=$element->levels usePopup=$element->popup subMenus=$element->getSubMenuList() verticalPopup=!empty($verticalPopup)}
		</div>
	</nav>
</div>