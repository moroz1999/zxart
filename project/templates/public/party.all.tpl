{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{include file=$theme->template("component.partyinfo.tpl")}
		<div class="party_editing_controls editing_controls">
			{if isset($privileges.picturesUploadForm.batchUploadForm) && $privileges.picturesUploadForm.batchUploadForm == true}
				<a class="button" href="{$element->URL}type:picturesUploadForm/action:batchUploadForm/">{translations name='party.upload'}</a>
			{/if}
			{if isset($privileges.musicUploadForm.batchUploadForm) && $privileges.musicUploadForm.batchUploadForm == true}
				<a class="button" href="{$element->URL}type:musicUploadForm/action:batchUploadForm/">{translations name='party.upload_music'}</a>
			{/if}
			{if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
				<a class="button" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='party.upload_prods'}</a>
			{/if}
			{if isset($currentElementPrivileges.publicReceive) && $currentElementPrivileges.publicReceive}
				<a class="button" href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='party.edit'}</a>
			{/if}
			{if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
				<a class="button delete_button" href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='party.delete'}</a>
			{/if}
		</div>
		<a class="button" href="{$element->getSaveUrl()}">{translations name='party.save'}</a>
	{include file=$theme->template('component.mentions.tpl')}
	<div class='party_compos'>
		{foreach from=$element->getProdsComposJson() key=compoType item=compo}
			{assign "compoTitle" "compo_"|cat:$compoType}
			<div class='party_compos_item'>
				<h2>{translations name='label.compo'}: {translations name="party.$compoTitle"}</h2>
				<script>
					window.elementsData = window.elementsData ? window.elementsData : { };
					window.elementsData['{$element->id}{$compoType}'] = {$compo};
				</script>
				<app-zx-prods-list element-id="{$element->id}{$compoType}" property="prods"></app-zx-prods-list>
			</div>
		{/foreach}
	</div>
	<div class='party_compos gallery_pictures' id="gallery_{$element->id}">
		{foreach from=$element->getPicturesCompos() key=compoType item=compo}
			{assign "compoTitle" "compo_"|cat:$compoType}
			<div class='party_compos_item'>
				<h2>{translations name='label.compo'}: {translations name="zxPicture.$compoTitle"}</h2>
				{include file=$theme->template('component.pictureslist.tpl') pictures=$compo}
			</div>
		{/foreach}
	</div>
	<div class='party_compos'>
		{foreach from=$element->getTunesCompos() key=compoType item=compo}
			{assign "compoTitle" "compo_"|cat:$compoType}
			<div class='party_compos_item'>
				<h2>{translations name='label.compo'}: {translations name="musiccompo.$compoTitle"}</h2>
				{include file=$theme->template("component.musictable.tpl") musicList=$compo element=$element}
			</div>
		{/foreach}
	</div>
	{include $theme->template('component.comments.tpl')}
{/capture}
{assign moduleClass "party_details"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}