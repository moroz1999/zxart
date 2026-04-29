{foreach $deliveryTypes as $deliveryType}
    <div class="form_items">
        <span class="form_label">
            {$deliveryType->title}
        </span>
        <div class="form_field">
            <input class='input_component' type="text" value="{$element->getDiscountForDeliveryType($deliveryType->id)}"
                   name="{$formNames.deliveryTypesDiscountInfoForm}[{$deliveryType->id}]"/>
            {include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="deliveryTypesDiscountInfoForm"}
        </div>
    </div>
{/foreach}