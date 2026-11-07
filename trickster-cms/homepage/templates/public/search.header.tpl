{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
{stripdomspaces}
	<div class="header_search">
		<form class="search_form" action="{$element->URL}action:perform/id:{$element->id}/" method="get" enctype="multipart/form-data" role="search">
			<input class="input_component search_input" name='phrase' type='text' value="{$formData.phrase}" autocomplete="off"/>
			<div class='header_search_controls'>
				<span tabindex="0" class='header_search_button search_button'>
					<span class='button_text'>{translations name='header.search'}</span>
				</span>
			</div>
		</form>
	</div>
{/stripdomspaces}