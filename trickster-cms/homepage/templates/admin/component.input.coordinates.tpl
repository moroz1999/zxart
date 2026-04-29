<div class=" form_items{if $formErrors.coordinates}  form_error{/if}">
    <span class="form_label">
        {translations name='map.coordinates'}:
    </span>
	<div class="form_field">
		<input class="input_component map_coordinates_input" type="text" {if !empty($formData.coordinates)}value="{$formData.coordinates}"{/if} name="{$formNames.coordinates}" />
	</div>
</div>
<div class="form_items">
	<span class="form_label"></span>
	<div class="form_field">
		<a class="button primary_button map_geocoding_button" href="#">{translations name='map.search'}</a>
		<div class="map_geocoding_result"></div>
	</div>
</div>