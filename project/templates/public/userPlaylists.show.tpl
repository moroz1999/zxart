{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<table class="table_component userplaylists_table">
	{foreach $element->getPlaylists() as $playlist}
		<tr class="userplaylists_item">
			<td class="userplaylists_item_title_cell"><a href="{$playlist->URL}">{$playlist->title}</a></td>
			<td class="userplaylists_item_delete_cell"><a class="userplaylists_item_delete button" href="{$playlist->URL}action:delete/id:{$playlist->id}/">{translations name="playlist.delete"}</a></td>
		</tr>
	{/foreach}
	</table>
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}