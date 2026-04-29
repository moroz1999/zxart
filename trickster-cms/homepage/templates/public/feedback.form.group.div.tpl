<div class='form_table feedback_form_table feedback_form_group'>
    {if $groupElement->title}
    <h3 class="feedback_form_groups_grouptitle">{$groupElement->title}</h3>
    {/if}
    {foreach from=$groupElement->getFormFields() item=formField}
        {assign var='fieldName' value=$formField->fieldName}
        {if $placeholders} {$placeholder=" placeholder='{$formField->title}'"}{else}{$placeholder = ''}{/if}

        <div data-fieldname="{$formNames.$fieldName}" class='form_table_row_{$formField->fieldType}{if $formErrors.$fieldName} form_error{/if} field{$formField->id} feedback_field_container' data-type="container_{$formField->fieldType}">
            <div class="form_label feedback_field_label{if $formField->fieldType == 'select'}{if $formField->getSelectionType()} {$formField->getSelectionType()}{/if}{/if}">
                <span class="form_element_label{if $formField->required} required{/if}">{$formField->title}</span>
            </div>

            <div class="form_field feedback_field_{$formField->fieldType}">
                {if $formField->fieldType == 'input'}
                    {*value="{$formData.$fieldName}"*}
                    <div class="form_element">
                        <input data-name='{$formNames.$fieldName}' class="input_component" type="text" name="{$formNames.$fieldName}"{$placeholder} />
                    </div>
                {elseif $formField->fieldType == 'textarea'}
                    <div class="form_element">
                        <textarea data-name='{$formNames.$fieldName}' class="textarea_component" name='{$formNames.$fieldName}'{$placeholder}>{$formData.$fieldName}</textarea>
                    </div>
                {elseif $formField->fieldType == 'checkbox'}
                    <div class="form_field_checkbox form_element">
                        <input data-name='{$formNames.$fieldName}' id="checkbox_{$formField->id}" class="checkbox_placeholder" type="checkbox" name='{$formNames.$fieldName}' value="1" {if $formData.$fieldName == 1}checked="checked"{/if} />
                        <label class="checkbox_label" for="checkbox_{$formField->id}">{$formField->title}</label>
                    </div>

                {* select from list: may be: select, radio, checkbox *}
                {elseif $formField->fieldType == 'select'}
                    <div class='form_field_select'>
                        {if $formField->getSelectionType() == 'dropdown'}
                        <div class="form_field_dropdown form_element">
{*                            {$option.id}"{if !empty($option.select)} selected="selected"{/if}*}
                            <select data-name='{$formNames.$fieldName}' data-referral="form_element_referral" {if $formField->required} data-required="form_element_required"{/if} class="dropdown_placeholder" id="{$fieldName}" name='{$formNames.$fieldName}'>
                                {foreach from=$formField->getOptionsList() key=nr item=optionElement}
                                    <option aria-disabled="true" class="{if $nr==0} disabled{/if}" value="{if $nr==0}{else}{$optionElement->title}{/if}" {if $formData.$fieldName == $optionElement->title}selected="selected"{/if}>{$optionElement->title}</option>
                                {/foreach}
                            </select>
                        </div>

                        {elseif $formField->getSelectionType() == 'radiobutton' || $formField->getSelectionType() == 'checkbox'}
                            <div class="form_field_selector form_field_{$formField->getSelectionType()}_selector">
                                {foreach from=$formField->getOptionsList() item=optionElement}
                                    <div class="form_field_{$formField->getSelectionType()} form_element">
                                        {if $formField->getSelectionType() == 'radiobutton'}
                                            <input data-name='{$formNames.$fieldName}' id="{$optionElement->id}" class="radio_holder" type="radio" name='{$formNames.$fieldName}' value="{$optionElement->title}" />
                                            <label class="radiobutton_label" for="{$optionElement->id}">{$optionElement->title}</label>
                                        {else}
                                            <input data-name='{$formNames.$fieldName}' id="checkbox_{$optionElement->id}" class="checkbox_placeholder" type="checkbox" name='{$formNames.$fieldName}[{$optionElement->id}]' value="{$optionElement->title}" />
                                            <label class="checkbox_label" for="checkbox_{$optionElement->id}">{$optionElement->title}</label>
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>

                        {/if}
                    </div>


                {elseif $formField->fieldType == 'fileinput'}
                    {*<input class="fileinput_placeholder" name='{$formNames.$fieldName}' type="file" />*}
                    <div class="fileinput_wrapper">
{*
                     1.   data-parent - parent selector - if you need to move current fileinput-item in DOM (js)
                     2.   data-first-child - p.1 and selector in parent selector above current fileinput-item in DOM (js)
*}
                        <div class="form_element">
                            <div class="form_droparea"><div class="droparea_logo"></div><span class="droparea_text">{translations name="feedback.drop"}</span></div>
                            <input data-name='{$formNames.$fieldName}' class="fileinput_placeholder" name='{$formNames.$fieldName}' type="file" data-parent=".fileinput_wrapper .form_element"  data-first-child="" />
                        </div>
                    </div>
                {elseif $formField->fieldType == 'dateInput'}
                    <div class="form_element">
                        <input data-name='{$formNames.$fieldName}' class="input_component input_date" type="text" value="{$formData.$fieldName}" name="{$formNames.$fieldName}"{$placeholder} />
                    </div>
                {/if}

            </div>
        </div>
    {/foreach}
</div>