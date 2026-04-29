{foreach $element->getSpecialFields() as $field}
	{assign "fieldNameLocal" $field@key}
	{if isset($field.hidden) && $field.hidden}
		{continue}
	{/if}

	{if isset($field.multiLanguage) && $field.multiLanguage}
		{if $field.format == "text"}
			{foreach $formData.$fieldName as $languageField}
				<div class="form_items{if $formErrors.$fieldNameLocal.{$languageField@key}} form_error{/if}">
                        <span class="form_label">
                            {translations name="$translationGroup.$fieldNameLocal"}: ({$languageNames.{$languageField@key}}
																				)
                        </span>
					<div class="form_field">
						<input class='input_component' type="text" value="{$languageField}"
							   name="{$formNames.$fieldNameLocal.{$languageField@key}}" />
					</div>
				</div>
			{/foreach}
		{/if}
	{else}
		<div class="form_items{if $formErrors.$fieldNameLocal} form_error{/if}">
                <span class="form_label">
                    {translations name="$translationGroup.$fieldNameLocal"}
                </span>
			<div class="form_field">
				{if $field.format == "text"}
					<input class="input_component" type="text" value="{$formData.$fieldNameLocal}"
						   name="{$formNames.$fieldNameLocal}" />
				{elseif $field.format == "file"}
					{if $element->$fieldNameLocal}
						{$element->{$fieldNameLocal|cat:"Name"}}
						<a href="{$element->URL}id:{$element->id}/action:deleteFile/file:{$fieldNameLocal}">
							{translations name="$structureType.deletefile"}
						</a>
					{/if}
					{*<br />*}
					<input class="fileinput_placeholder" type="file" name="{$formNames.$fieldNameLocal}" />
				{/if}
			</div>
		</div>
	{/if}
{/foreach}