{if isset($item.method)}
	{assign var='options' value=$form->callElementMethod($item.method)}
{elseif isset($item.property)}
	{assign var='options' value=$form->getElementProperty($item.property)}
{elseif isset($item.options)}
	{assign var='options' value=$item.options}
{else}
	{assign var='options' value=""}
{/if}

{foreach from=$formData.$fieldName key=languageId item=languageFeedbackId}
	<div class="form_items{if $formErrors.$fieldName.$languageId} form_error{/if}">
		<span class="form_label">
			{translations name="{$structureType}.{$fieldName}.{$languageId}"}
		</span>
		<div class="form_field">
			<select class="dropdown_placeholder {$item.class}" name="{$formNames.$fieldName.$languageId}" autocomplete='off'>
				{if isset($option.default)}
					<option value='{$option.default.value}'>{{$option.default.name}}</option>
				{elseif $option.default.none}

				{else}
					<option value=''></option>
				{/if}
				{if $options != ""}
					{$flag = false}
					{foreach $options as $option}
						{if isset($option.select)}
							{$flag = $option.select}
						{/if}
						{if $option.id == $formData.$fieldName.$languageId}{$flag = true}{/if}
						<option value='{$option.id}'{if $flag} selected="selected"{/if}>
							{if isset($option.title) && $option.title !== ''}
								{$option.title}
							{else}
								{$option.structureName}
							{/if}
						</option>
					{/foreach}
				{/if}
			</select>
		</div>
	</div>
{/foreach}