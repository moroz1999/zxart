{if empty($propertyName)}
    {$propertyName='connectedFile'}
{/if}
{if empty($imagePreset)}
    {$imagePreset='adminImage'}
{/if}
{if $filesList = $element->getFilesList($propertyName)}
    <tr {if $formErrors.$propertyName} class="form_error"{/if}>
        <td colspan="2">

            <div class="form_images">
                {foreach $filesList as $number =>$file}
                    <div class="form_image">
                        {if $file->isImage()}
                            <img class="form_image_picture" src='{$file->getImageUrl($imagePreset)}'
                                 alt="{$file->title}"/>
                        {/if}
                        <div class="form_image_title">{$file->title}</div>
                        <div class="form_image_title">
                            {if isset($privileges.file.delete) && $privileges.file.delete}
                                <a class="button delete_button" href="{$file->URL}id:{$file->id}/action:delete/">
                                    {translations name="{$element->structureType}.delete_file"}
                                </a>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        </td>
    </tr>
{/if}
<tr {if $formErrors.$propertyName} class="form_error"{/if}>
    <td class="form_label">
        {translations name=$element->structureType|cat:'.files_upload'}:
    </td>
    <td class="form_field">
        <input class="fileinput_placeholder" type="file" name="{$formNames.$propertyName}[]" multiple="multiple"/>
    </td>
</tr>
