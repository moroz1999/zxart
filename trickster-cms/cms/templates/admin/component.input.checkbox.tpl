<div class="form_field_{$fieldName} form_items">
    <span class="form_label checkbox_header">
        {translations name="{$translationGroup}.{strtolower($fieldName)}"}
    </span>
    <div class="form_field">
        <input class="{if !empty($item.class)} {$item.class} {/if}checkbox_placeholder" type="checkbox" value="1" name="{$formNames.$fieldName}"{if $formData.$fieldName} checked="checked"{/if} />
    </div>
    <div class="cell">{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}</div>
</div>