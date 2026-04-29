<div class="form_items{if $formErrors.password} form_error{/if}">
    <span class="form_label">
        {translations name="{$translationGroup}.{strtolower($fieldName)}"}:
    </span>
    <div class="form_field">
        <input class="input_component" type="password" value="" name="{$formNames.password}" autocomplete='off' />
    </div>
</div>