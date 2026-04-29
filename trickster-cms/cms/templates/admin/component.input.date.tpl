<div class="form_items{if $formErrors.$fieldName} form_error{/if}">
    <span class="form_label">
        {translations name="{$translationGroup}.{strtolower($fieldName)}"}
    </span>
    <div class="form_field">
        <div class="date_container">
            <input class='input_component input_date' type="text" value="{$formData.$fieldName}" name="{$formNames.$fieldName}"/>
            <span class="icon icon_calendar"></span>
        </div>
    </div>{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}
</div>