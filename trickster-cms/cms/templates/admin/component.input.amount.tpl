<div class="form_items {if $formErrors.displayAmount} form_error{/if}">
    <span class="form_label">
        {translations name="{$translationGroup}.{strtolower($fieldName)}"}
    </span>
       <div class="form_field">
        <input class="focused_input input_component" type="text" value="{if $formData.$fieldName === null}10{else}{$formData.$fieldName}{/if}" name="{$formNames.$fieldName}" />
    </div>
    {include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}
</div>