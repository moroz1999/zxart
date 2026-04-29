{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
{assign var='allowedSearchTypes' value=$element->getSearchTypesString()}

{capture assign="moduleContent"}
	<form action="{$element->getFormActionURL()}" {if $allowedSearchTypes}data-types="{$allowedSearchTypes}"{/if} class='search_form' method="post" enctype="multipart/form-data" role="search">
			<input class='{if $element->bAjaxSearch}ajaxsearch_input {/if}input_component' name='{$formNames.phrase}' type='text' value="{$formData.phrase}"/>
		<div class='search_controls'>
			<span tabindex="0" class='button search_button'>
				<span class='button_text'>{translations name="search.submit"}</span>
			</span>
			<input type="hidden" value="{$element->id}" name="id" />
			<input type="hidden" value="perform" name="action" />
		</div>
	</form>
{/capture}

{assign moduleClass "search_block search_column"}
{assign moduleContentClass "search_content"}

{include file=$theme->template("component.columnmodule.tpl")}