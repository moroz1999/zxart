{* Form works with Ajax now so this template is unused and exists
   only for potential backwards compatibility *}
{capture assign="moduleContent"}
	<div class='feedback_left'>
		{$element->content}
	</div>
	<div class='feedback_right'>
		<h1 class='feedback_title'>{$element->title}</h1>
		<div class='feedback_result'>
			{$element->resultMessage}
		</div>
	</div>
{/capture}

{assign moduleClass "feedback_form"}

{include file=$theme->template("component.contentmodule.tpl")}