{capture assign="moduleContent"}
	{if $element->originalName != ''}
		<div class="personnel_short_image_container">
			{include file=$theme->template('component.elementimage.tpl') type='personnelImage' class='personnel_short_image' lazy=true}
		</div>
	{/if}

	{if $element->title != ''}
		<div class="personnel_short_title">
			{if $element->status != ''}
				<span class="personnel_short_status">{$element->status} </span>
			{/if}
			<span class="personnel_short_name">{$element->title}</span>
		</div>
	{/if}

	{if $element->position != ''}<div class='personnel_short_position'>{$element->position}</div>{/if}
	{if $element->phone != ''}<div class='personnel_short_phone'><span class="personnel_short_lable">{translations name='personnel.phone'}: </span> {$element->phone}</div>{/if}
	{if $element->mobilePhone != ''}<div class='personnel_short_mobile_phone'><span class="personnel_short_lable">{translations name='personnel.mobile_phone'}: </span> {$element->mobilePhone}</div>{/if}
	{if $element->email != ''}<div class='personnel_short_email'><span class="personnel_short_lable">{translations name='personnel.email'}: </span> <a href="mailto:{$element->email}">{$element->email}</a></div>{/if}
	{if $element->content != ''}<div class='personnel_short_content'>{$element->content}</div>{/if}
{/capture}
{assign moduleClass "personnel_short"}
{assign moduleTitle ""}
{include file=$theme->template("component.subcontentmodule_square.tpl")}