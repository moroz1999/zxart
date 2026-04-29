<div class="form_items{if $formErrors.mapCode} form_error{/if}">
    <span class="form_label">
        {translations name="{$translationGroup}.{strtolower($fieldName)}"}
    </span>
    <div class="form_field">
        <textarea class="textarea_component" name="{$formNames.mapCode}" >{$formData.mapCode}</textarea>
    </div>
</div>