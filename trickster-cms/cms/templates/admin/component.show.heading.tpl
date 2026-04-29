{if !empty($item.translationGrupp)}
    {$translationGrupp = $item.translationGrupp}
{else}
    {$translationGrupp = $structureType}
{/if}
<div class="form_items {if !empty($item.class)}{$item.class}{/if}">
	<div class="form_label"></div>
	<div class="form_label heading">
		<h2 class="content_list_title">
            {translations name="{$translationGrupp}.{$fieldName}"}
		</h2>
	</div>
</div>