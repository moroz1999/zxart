{if $authors=$element->getAuthorsInfo('group')}
<div class="group_details_authors">
    <h2>{translations name='group.authors'} {$element->getTitle()}</h2>
    <table class='table_component'>
        <thead>
        <tr>
            <th>
                {translations name='group.table_author'}
            </th>
            <th>
                {translations name='group.table_startdate'}
            </th>
            <th>
                {translations name='group.table_enddate'}
            </th>
            <th>
                {translations name='group.table_roles'}
            </th>
        </tr>
        </thead>
        {foreach from=$authors item=info}
            <tr>
                <td>
                    <a href="{$info.authorElement->getUrl()}">{$info.authorElement->title}</a>
                </td>
                <td>
                    {$info.startDate}
                </td>
                <td>
                    {$info.endDate}
                </td>
                <td>
                    {foreach from=$info.roles item=role}{translations name="group.role_$role"}{if !$role@last}, {/if}{/foreach}
                </td>
            </tr>
        {/foreach}
    </table>
</div>
{/if}