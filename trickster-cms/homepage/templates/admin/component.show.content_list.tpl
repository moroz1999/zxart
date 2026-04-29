{assign 'contentList' $currentElement->getContentList()}
{if $contentList}
    <table class='content_list'>
        <thead>
        <tr>
            <th class='checkbox_column'>
                <input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
            </th>
            <th class="name_column">
                {translations name='label.name'}
            </th>
            <th class='edit_column'>
                {translations name='label.edit'}
            </th>
            <th class='type_column'>
                {translations name='label.type'}
            </th>
            <th class='date_column'>
                {translations name='label.date'}
            </th>
            <th class='date_column'>
                {translations name='news.date_modified'}
            </th>
            <th class='delete_column'>
                {translations name='label.delete'}
            </th>
        </tr>
        </thead>
        <tbody>
        {foreach $contentList as $contentItem}
            {if $contentItem->structureType != 'positions'}
                {assign var='typeName' value=$contentItem->structureType}
                {assign var='typeLowered' value=$contentItem->structureType|strtolower}
                {assign var='type' value="element."|cat:$typeLowered}
                {assign var='privilege' value=$privileges.$typeName}
                <tr class="content_list_item elementid_{$contentItem->id}">
                    <td class="checkbox_cell">
                        <input class='singlebox checkbox_placeholder' type="checkbox" name="{$formNames.elements}[{$contentItem->id}]" value="1" />
                    </td>
                    <td class='name_column'>
                        <a class="content_element_title" href="{$contentItem->URL}">
                            {stripdomspaces}
                                <span class='icon icon_{$contentItem->structureType}'></span>
                                <span class="content_item_title">
									{$contentItem->getTitle()}
								</span>
                            {/stripdomspaces}
                        </a>
                    </td>
                    <td class='edit_column'>
                        {if isset($privilege.showForm) && $privilege.showForm}
                            <a href="{$contentItem->URL}id:{$contentItem->id}/action:showForm" class='icon icon_edit'></a>
                        {/if}
                    </td>
                    <td class='type_column'>
                        {translations name=$type}
                    </td>
                    <td>
                        {$contentItem->date}
                    </td>
                    <td>
                        {$contentItem->dateModified}
                    </td>
                    <td class="delete_column">
                        {if isset($privilege.delete) && $privilege.delete}
                            <a href="{$contentItem->URL}id:{$contentItem->id}/action:delete" class='icon icon_delete content_item_delete_button'></a>
                        {/if}
                    </td>
                </tr>
            {/if}
        {/foreach}
        </tbody>
    </table>
{/if}
</form>
<div class="content_list_bottom">
    {if isset($pager) && $currentElement->getChildrenList()}
        {include file=$theme->template("pager.tpl") pager=$pager}
    {/if}
</div>