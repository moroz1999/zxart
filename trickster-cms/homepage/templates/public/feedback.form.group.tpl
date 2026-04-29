{if $groupElement->title}
    <h3 class="feedback_form_groups_grouptitle">{$groupElement->title}</h3>
{/if}
<table class='form_table feedback_form_table feedback_form_group'>

    {foreach from=$groupElement->getFormFields() item=formField}
        {assign var='fieldName' value=$formField->fieldName}
        {if $formField->fieldType == 'input'}
            <tr class='form_table_row_input{if $formErrors.$fieldName} form_error{/if} field{$formField->id}'>
                <td class='form_label'>
                    {$formField->title}:
                </td>
                <td class='form_star'>{if $formField->required}*{/if}</td>
                <td class='form_field'>
                    <input class="input_component" type="text" value="{$formData.$fieldName}" name="{$formNames.$fieldName}" />
                </td>
                <td class="form_extra"></td>
            </tr>
        {elseif $formField->fieldType == 'textarea'}
            <tr class='form_table_row_textarea{if $formErrors.$fieldName} form_error{/if} field{$formField->id}'>
                <td class='form_label'>
                    {$formField->title}:
                </td>
                <td class='form_star'>{if $formField->required}*{/if}</td>
                <td class='form_field'>
			<textarea class="textarea_component" name='{$formNames.$fieldName}'>{$formData.$fieldName}</textarea>
                </td>
                <td class="form_extra"></td>
            </tr>
        {elseif $formField->fieldType == 'checkbox'}
            <tr class='form_table_row_checkbox{if $formErrors.$fieldName} form_error{/if} field{$formField->id}'>
                <td class='form_label'></td>
                <td class='form_star'>{if $formField->required}*{/if}</td>
                <td class='form_field'>
                    <div class="form_field_checkbox">
                        <input id="checkbox_{$formField->id}" class="checkbox_placeholder" type="checkbox" name='{$formNames.$fieldName}' value="1" {if $formData.$fieldName == 1}checked="checked"{/if} />
                        <label class="checkbox_label" for="checkbox_{$formField->id}">{$formField->title}</label>
                    </div>
                </td>
                <td class="form_extra"></td>
            </tr>
        {elseif $formField->fieldType == 'select'}
            <tr class='form_table_row_{$formField->getSelectionType()}{if $formErrors.$fieldName} form_error{/if} field{$formField->id}'>
                <td class='form_label {$formField->getSelectionType()}_label_top_align'>
                    {$formField->title}:
                </td>
                <td class='form_star {$formField->getSelectionType()}_label_top_align'>{if $formField->required}*{/if}</td>
                <td class='form_field'>
                    {if $formField->getSelectionType() == 'dropdown'}
                        <select class="dropdown_placeholder" name='{$formNames.$fieldName}'>
                            {foreach from=$formField->getOptionsList() item=optionElement}
                                <option value="{$optionElement->title}" {if $formData.$fieldName == $optionElement->title}selected="selected"{/if}>{$optionElement->title}</option>
                            {/foreach}
                        </select>
                    {elseif $formField->getSelectionType() == 'radiobutton' || $formField->getSelectionType() == 'checkbox'}
                        <div class="form_field_{$formField->getSelectionType()}_selector">
                            {foreach from=$formField->getOptionsList() item=optionElement}
                                <div class="form_field_{$formField->getSelectionType()}">
                                    {if $formField->getSelectionType() == 'radiobutton'}
                                        <input id="{$optionElement->id}" class="radio_holder" type="radio" name='{$formNames.$fieldName}' value="{$optionElement->title}" />
                                        <label class="radiobutton_label" for="{$optionElement->id}">{$optionElement->title}</label>
                                    {else}
                                        <input id="checkbox_{$optionElement->id}" class="checkbox_placeholder" type="checkbox" name='{$formNames.$fieldName}[{$optionElement->id}]' value="{$optionElement->title}" />
                                        <label class="checkbox_label" for="checkbox_{$optionElement->id}">{$optionElement->title}</label>
                                    {/if}
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                </td>
                <td class="form_extra"></td>
            </tr>
        {elseif $formField->fieldType == 'fileinput'}
            <tr class='form_table_row_fileinput{if $formErrors.$fieldName} form_error{/if} field{$formField->id}'>
                <td class='form_label'>
                    {$formField->title}:
                </td>
                <td class='form_star'>{if $formField->required}*{/if}</td>
                <td class='form_field'>
                    <input class="fileinput_placeholder" name='{$formNames.$fieldName}' type="file" />
                </td>
                <td class="form_extra"></td>
            </tr>
        {elseif $formField->fieldType == 'dateInput'}
            <tr class='form_table_row_input{if $formErrors.$fieldName} form_error{/if}'>
                <td class='form_label'>
                    {$formField->title}:
                </td>
                <td class='form_star'>{if $formField->required}*{/if}</td>
                <td class='form_field'>
                    <input class="input_component input_date" type="text" value="{$formData.$fieldName}" name="{$formNames.$fieldName}" />
                </td>
                <td class="form_extra"></td>
            </tr>
        {/if}
    {/foreach}
</table>