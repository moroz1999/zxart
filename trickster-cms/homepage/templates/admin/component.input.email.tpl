<div class="form_items{if $formErrors.$fieldName} form_error{/if}">
    <span class="form_label">
        {translations name="{$translationGroup}.{strtolower($fieldName)}"}
    </span>
    <div class="form_field">
        <input class="input_component" type="text" value="{$formData.destination}" name="{$formNames.destination}" />
        <div class="form_field_tip">{translations name="field.default"}: {$settings.default_sender_email}</div>
    </div>
</div>