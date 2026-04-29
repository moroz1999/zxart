{capture assign="moduleContent"}
	{assign var='formData' value=$element->getFormData()}
	{assign var='formErrors' value=$element->getFormErrors()}
	{assign var='formNames' value=$element->getFormNames()}

	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}
	<div class='feedback_block'>
	<form action="{$currentElement->URL}" class='feedback_form' method="post" enctype="multipart/form-data" role="form">
		<div class='feedback_form_container'>

			{*{if $element->title != ''}<h1 class="feedback_heading">{$element->title}</h1>{/if}*}
			{if $element->content != ''}
				{if !$element->resultMessage and !$element->errorMessage}
					<div class='feedback_content html_content ajax_form_hide_on_success'>
						{$element->content}
					</div>
				{/if}
			{/if}

			<div class='feedback_content heading_3 feedback_message_wrapper'><span class="form_result_message ajax_form_success_message"></span>
				</div>

				<div class='form_error_message ajax_form_error_message' role="alert">
				</div>

			<div class="feedback_form_block">
				{if !$element->resultMessage}
					<div class="ajax_form_hide_on_success">
						<div class='feedback_form_groups form_fields'>
							{foreach from=$element->getCustomFieldsGroups() item=groupElement name=groups}
								{include file=$theme->template("feedback.form.group.div.tpl") element=$groupElement placeholders=false}
							{/foreach}
						</div>

						<div class="feedback_controls">
							<div class="form_element">
								<a href="" class="button ajax_form_submit form_submit feedback_submit">
									<span class='button_text'>{if $element->buttonTitle}{$element->buttonTitle}{else}{translations name="feedback.send"}{/if}</span>
								</a>
							</div>
						</div>
					</div>
				{/if}
				<input type="hidden" value="{$element->id}" name="id" />
				<input type="hidden" value="send" name="action" />
			</div>
		</div>
	</form>
</div>
{/capture}

{assign moduleClass "{if !empty($referral)}referral_{$referral} {/if}feedback_block_container{if !empty($element->getCurrentLayout('colorLayout'))} bg_color bg_color_{$element->getCurrentLayout('colorLayout')}{/if}{if !empty($currentElement->getCurrentLayout('layout'))} bg_img bg_img_{$currentElement->getCurrentLayout('layout')}{/if}"}
{assign moduleTitleClass "feedback_heading"}
{assign moduleAttributes "id=\"feedback-form-{$element->id}\""}
{include file=$theme->template("component.contentmodule.tpl")}
{include file=$theme->template('javascript.hiddenFieldsData.tpl')}
