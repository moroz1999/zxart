{foreach from=$formData.$fieldName key=languageId item=object}
    <div class="form_items{if !empty($item.class)} {$item.class}{/if}{if $formErrors.$fieldName.$languageId} form_error{/if}" {if !empty($item.style)}style="{$item.style}"{/if}>
        <span class="form_label">
            {translations name="{$translationGroup}.{strtolower($fieldName)}"} ({$languageNames.$languageId})
        </span>
        <div class="form_field">
            <input class='input_component'{if !empty($item.stepValue)} step="{$item.stepValue}"{/if}{if !empty($item.minValue)} min="{$item.minValue}"{/if}{if !empty($item.maxValue)} max="{$item.maxValue}"{/if} type="{if !empty($item.inputType)}{$item.inputType}{else}text{/if}" value="{$object}" name="{$formNames.$fieldName.$languageId}" />
        </div>
        {include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}
    </div>
{/foreach}