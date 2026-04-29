{foreach $element->getSpecialFields() as $field}
    {assign "fieldName" $field@key}

    {if isset($field.hidden) && $field.hidden}
        {continue}
    {/if}

    {if isset($field.multiLanguage) && $field.multiLanguage}
        {if $field.format == "text"}
            {foreach $formData.$fieldName as $languageField}
                <div class="form_items{if $formErrors.$fieldName.{$languageField@key}} form_error{/if}">
                    <span class="form_label">
                        {translations name='import_plugin.'|cat:$fieldName} ({$languageNames.{$languageField@key}})
                    </span>
                    <div class="form_field">
                        <input class='input_component' type="text" value="{$languageField}" name="{$formNames.$fieldName.{$languageField@key}}" />
                    </div>
                </div>
            {/foreach}
        {/if}
    {else}
        <div class="form_items{if $formErrors.$fieldName} form_error{/if}">
            <span class="form_label">
                {translations name='import_plugin.'|cat:$fieldName}
            </span>
            <div class="form_field">
                {if $field.format == "text"}
                    <input class="input_component" type="text" value="{$formData.$fieldName}" name="{$formNames.$fieldName}" />
                {elseif $field.format == "file"}
                    {if $element->$fieldName}
                        {$element->{$fieldName|cat:"Name"}}
                        <a href="{$element->URL}id:{$element->id}/action:deleteFile/file:{$fieldName}" >
                            {translations name='label.deletefile'}
                        </a>
                    {/if}
                    {*<br />*}
                    <input class="fileinput_placeholder" type="file" name="{$formNames.$fieldName}" />
                {/if}
            </div>
        </div>
    {/if}
{/foreach}