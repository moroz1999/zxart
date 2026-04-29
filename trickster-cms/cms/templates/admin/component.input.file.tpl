<div class="form_field_{$fieldName} {if $formErrors.$fieldName} form_error {/if}form_items">
    <span class="form_label">
         {translations name="{$translationGroup}.{if !empty($item.translationName)}{$item.translationName}{else}{strtolower($fieldName)}{/if}"}:
    </span>
    <div class="form_field">
        <input class="fileinput_placeholder" type="file" name="{$formNames.$fieldName}{if $item.multiple}[]{/if}" {if $item.multiple}multiple{/if}/>
        {if $element->$fieldName}
            <div class="file_container">
                {if !empty($item.fileNameProperty)}<span class="file_container_filename">{$formData.{$item.fileNameProperty}}</span>{/if}
                <a class="button file_delete_button warning_button" href="{$element->URL}id:{$element->id}/action:deleteFile/file:{$fieldName}">
                    <span class="icon icon_delete"></span>
                    {translations name="$fieldName.deletefile"}
                </a>
            </div>
        {/if}
    </div>
</div>