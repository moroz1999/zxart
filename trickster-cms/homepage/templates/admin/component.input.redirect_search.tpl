<div class="form_items">
        <span class="form_label">
            {translations name="{$translationGroup}.{strtolower($fieldName)}"}
        </span>
    <div class="form_field">
        <input class='input_component redirect_searchinput' type="text" value="" name="" placeholder="{translations name='field.search'}..." />
        <input class="ajaxitemsearch_resultid" type="hidden" value="{$formData.destinationElementId}" name="{$formNames.destinationElementId}" />
        <div class="ajaxitemsearch_result">
            <span class="ajaxitemsearch_result_text">
                {if $destinationElement}{$destinationElement->title}{/if}
            </span>
            <div class="ajaxitemsearch_result_remover"></div>
        </div>
    </div>
</div>