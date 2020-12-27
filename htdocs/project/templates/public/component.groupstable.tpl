{if isset($pager)}
    <div class='groups_list_top_controls'>
        {include file=$theme->template("pager.tpl") pager=$pager}
    </div>
{/if}
{if !isset($number)}{$number=1}{/if}

<div class='groups_list_block'>
    <table class='groups_list_table table_component'>
        <thead>
        <tr>
            <th>
            </th>
            <th>
                {translations name='group.table_title'}
            </th>
            <th>
            </th>
            <th>
                {translations name='group.table_country'}
            </th>
            <th>
                {translations name='group.table_city'}
            </th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$groupsList item=group name=groupsList}
            {$odd = $smarty.foreach.groupsList.iteration is odd}
            {include file=$theme->template($group->getTemplate('table')) element=$group odd=$odd}
            {$number=$number+1}
        {/foreach}
        </tbody>
    </table>
</div>
{if isset($pager)}
    <div class='groups_list_top_controls'>
        {include file=$theme->template("pager.tpl") pager=$pager}
    </div>
{/if}