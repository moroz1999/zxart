<div class="tabs_block">
	<div class="tabs_list">
		{include file=$theme->template('shared.tabs.tpl')}
	</div>
	<div class="tabs_content">
		<div class="copydialog_component">
			<h1 class="form_inner_title copydialog_title">{translations name="copy.selectdestination"}:</h1>
			{if $element->pasteAllowed}<div class="copydialog_destination">{translations name="copy.selecteddestination"}: <span class="copydialog_destination_title">{$element->destinationElement->getTitle()}</span></div>{/if}
			<div class="copydialog_controls">
				<button type="button" class="button {if !$element->pasteAllowed}button_disabled{else}success_button{/if}" {if !$element->pasteAllowed}disabled="disabled"{/if} onclick="document.location.href='{$element->URL}action:pasteElements/id:{$element->id}/navigateId:{if $element->destinationElement}{$element->destinationElement->id}{/if}/view:{$contentType}/'">
				{translations name="copy.approvecopy"}
				</button>
			</div>
			<div class="treemenu_component copydialog_navigation">
				{include file=$theme->template("block.copytree.tpl") menuLevel=$element->navigationTree level=0 type="copy"}
			</div>
		</div>
	</div>
</div>