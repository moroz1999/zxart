{foreach from=$formData.$fieldName key=languageId item=valueTextarea}
	<div class="form_items{if !empty($item.class)} {$item.class}{/if}"{if !empty($item.style)} style="{$item.style}"{/if}>
		<span class="form_label">
			{$languageNames.$languageId}
		</span>
		<div class="form_field">
			<textarea class="textarea_component" type="text" name="{$formNames.$fieldName.$languageId}" >{$valueTextarea}</textarea>
		</div>
		{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}
	</div>
{/foreach}