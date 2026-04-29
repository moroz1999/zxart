<div class="form_items">
	<span class="form_label">
		{translations name="{$structureType}.autocomplete"}:
	</span>
	<div class="form_field">
		<select class="dropdown_placeholder" name="{$formNames.autocomplete}">
			<option value='' {if $formData.autocomplete==''}selected='selected'{/if}></option>
			<option value='company' {if $formData.autocomplete=='company'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_company"}</option>
			<option value='userName' {if $formData.autocomplete=='userName'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_username"}</option>
			<option value='fullName' {if $formData.autocomplete=='fullName'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_fullname"}</option>
			<option value='firstName' {if $formData.autocomplete=='firstName'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_firstname"}</option>
			<option value='lastName' {if $formData.autocomplete=='lastName'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_lastname"}</option>
			<option value='email' {if $formData.autocomplete=='email'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_email"}</option>
			<option value='phone' {if $formData.autocomplete=='phone'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_phone"}</option>
			<option value='address' {if $formData.autocomplete=='address'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_address"}</option>
			<option value='city' {if $formData.autocomplete=='city'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_city"}</option>
			<option value='country' {if $formData.autocomplete=='country'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_country"}</option>
			<option value='postIndex' {if $formData.autocomplete=='postIndex'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_postindex"}</option>
			<option value='dpdRegion' {if $formData.autocomplete=='dpdRegion'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_dpdregion"}</option>
			<option value='dpdPoint' {if $formData.autocomplete=='dpdPoint'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_dpdpoint"}</option>
			<option value='post24Region' {if $formData.autocomplete=='post24Region'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_post24region"}</option>
			<option value='post24Automate' {if $formData.autocomplete=='post24Automate'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_post24automate"}</option>
			<option value='smartPostRegion' {if $formData.autocomplete=='smartPostRegion'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_smartpost_region"}</option>
			<option value='smartPostAutomate' {if $formData.autocomplete=='smartPostAutomate'}selected='selected'{/if}>{translations name="{$structureType}.autocomplete_smartpost_automate"}</option>
			<option value='product'{if $formData.autocomplete=='product'} selected='selected'{/if}>{translations name="{$structureType}.autocomplete_product"}</option>
		</select>
	</div>
</div>