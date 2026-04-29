<div class="form_fields">
	<div class="form_items">
		<div class="form_label">
			{translations name='seo.urlname'}
		</div>
		<div class="form_field">
			<input class='input_component' type="text" value="{$formData.structureName}" name="{$formNames.structureName}" />
			{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="structureName"}
		</div>
	</div>
	{foreach from=$formData.metaTitle key=languageId item=metaTitle}
	<div class="form_items">
		<div class="form_label">
			{translations name='seo.pagetitle'} ({$languageNames.$languageId})
		</div>
		<div class="form_field">
			<input class="input_component" type="text" value="{$metaTitle}" name="{$formNames.metaTitle.$languageId}" />
			{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="metaTitle"}
		</div>
	</div>
	{/foreach}
	{if isset($formData.h1)}
		{foreach from=$formData.h1 key=languageId item=h1}
			<div class="form_items">
				<div class="form_label">
					{translations name='seo.h1'} ({$languageNames.$languageId})
				</div>
				<div class="form_field">
					<input class="input_component" type="text" value="{$h1}" name="{$formNames.h1.$languageId}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="h1"}
				</div>
			</div>
		{/foreach}
	{/if}
	{foreach from=$formData.metaDescription key=languageId item=metaDescription}
		<div class="form_items">
			<div class="form_label">
				{translations name='seo.metadescription'} ({$languageNames.$languageId})
			</div>
			<div class="form_field">
				<textarea class="textarea_component" type="text" name="{$formNames.metaDescription.$languageId}" >{$metaDescription}</textarea>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="metaDescription"}
			</div>
		</div>
	{/foreach}
	<div class="form_items">
		<div class="form_label">
			{translations name='seo.canonicalurl'}
		</div>
		<div class="form_field">
			<input class='input_component' type="text" value="{$formData.canonicalUrl}" name="{$formNames.canonicalUrl}" />
			{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="canonicalUrl"}
		</div>
	</div>
	<div class="form_items">
		<div class="form_label">
			{translations name='seo.metadenyindex'}
		</div>
		<div class="form_field">
			<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.metaDenyIndex}"{if $element->metaDenyIndex} checked="checked"{/if}/>
			{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="metaDenyIndex"}
		</div>
	</div>
</div>
{if (!isset($actionName))}{$actionName = "receiveSeo"}{/if}
{include file=$theme->template('component.controls.tpl') action=$actionName}
