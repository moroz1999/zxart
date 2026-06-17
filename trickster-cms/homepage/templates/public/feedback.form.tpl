{capture assign="moduleContent"}
	{if $element->title}
		{capture assign="moduleTitle"}{$element->title}{/capture}
	{/if}
	<div class="feedback_block">
		{if $element->content != ''}
			<div class="feedback_content html_content">
				{$element->content}
			</div>
		{/if}
		<zx-feedback-form element-id="{$element->id}"></zx-feedback-form>
	</div>
{/capture}

{assign moduleClass "feedback_block_container"}
{assign moduleTitleClass "feedback_heading"}
{assign moduleAttributes "id=\"feedback-form-{$element->id}\""}
{include file=$theme->template("component.contentmodule.tpl")}
