<div class="form_items">
    <div class="form_table_layout">
        <div class="layout_content">
            <div class="form_table_layout_item">
                <label for="form_table_layout_item_input_{$item.label}" class="form_table_layout_item_label">
                    <img class="form_table_layout_item_image" src="{$theme->getImageUrl("{$structureType}_{$fieldName}.png")}"/>
                    <div class="form_table_layout_item_text">{translations name="{$structureType}.layout_{$fieldName}"}</div>
                    <input type="radio" class="radio_holder" name="{$formNames.layout}" value="{$fieldName}" {if $formData.layout == $fieldName || !$formData.layout} checked="checked"{/if} id="form_table_layout_item_input_{$fieldName}">
                </label>
            </div>
        </div>
    </div>
</div>