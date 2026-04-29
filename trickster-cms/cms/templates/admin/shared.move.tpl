<div class="tabs_block">
	<div class="tabs_list">
		{include file=$theme->template('shared.tabs.tpl')}
	</div>
	<div class="tabs_content">
		<div class="copydialog_component">
			<h1 class="form_inner_title copydialog_title">{translations name="move.selectdestination"}:</h1>
			{if $element->pasteAllowed}<div class="copydialog_destination">{translations name="move.selecteddestination"}: <span class="copydialog_destination_title">{$element->destinationElement->getTitle()}</span></div>{/if}
			<div class="copydialog_controls">
				<button type="button" class="button {if !$element->pasteAllowed}button_disabled{else}success_button{/if}" {if !$element->pasteAllowed}disabled="disabled"{/if} onclick="document.location.href='{$element->URL}action:pasteElements/id:{$element->id}/navigateId:{$element->destinationElement->id}/{if !empty($contentType)}view:{$contentType}/{/if}'">
					{translations name="move.approvemove"}
				</button>
			</div>
			<div class="treemenu_component copydialog_navigation">
				{include file=$theme->template("block.copytree.tpl") menuLevel=$element->navigationTree level=0 type="move"}
			</div>
		</div>
	</div>
</div>