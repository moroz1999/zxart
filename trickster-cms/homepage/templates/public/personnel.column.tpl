{capture assign="moduleContent"}
<div>
    {if $element->originalName != ''}
		<div class="personnel_short_image_container">
			{include file=$theme->template('component.elementimage.tpl') type='personnelImage' class='personnel_short_image' lazy=true}
		</div>
	{/if}
	{if $element->title != ''}<div class='personnel_short_title'>{$element->title}</div>{/if}
	{if $element->position != ''}<div class='personnel_short_position'>{$element->position}</div>{/if}
	{if $element->phone != ''}<div class='personnel_short_phone'>{translations name='personnel.phone'}: {$element->phone}</div>{/if}
    {if $element->mobilePhone != ''}<div class='personnel_short_mobile_phone'>{translations name='personnel.mobile_phone'}: {$element->mobilePhone}</div>{/if}
    {if $element->email != ''}<div class='personnel_short_email'>{translations name='personnel.email'}: <a href="mailto:{$element->email}">{$element->email}</a></div>{/if}
    {if $element->content != ''}<div class='personnel_short_content'>{$element->content}</div>{/if}
</div>
{/capture}

{assign moduleClass "personnel_short"}
{include file=$theme->template("component.columnmodule.tpl")}