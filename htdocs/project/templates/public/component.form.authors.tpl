<tr>
    <td class="form_label">
        {translations name="$translationsGroup.add_member"}:
    </td>
    <td class="form_field">
        <div class="authorship_form_component">
            <div class="authorship_form_title">
            <select class="author_form_select" name="{$formNames.addAuthor}" autocomplete='off'></select>
            </div>
            {if $displayDate}
                <input class='authorship_form_startdate input_component' type="text" value="{$formData.addAuthorStartDate}"
                       name="{$formNames.addAuthorStartDate}[new]"
                       placeholder="{translations name="group.add_author_start"}"/>
                <input class='authorship_form_enddate input_component' type="text" value="{$formData.addAuthorEndDate}"
                       name="{$formNames.addAuthorEndDate}[new]"
                       placeholder="{translations name="group.add_author_end"}"/>
            {/if}
            <select class="authorship_form_roles select_multiple" name="{$formNames.addAuthorRole}[new][]" autocomplete='off'
                    multiple="multiple">
                {foreach $element->getAuthorRoles() as $role}
                    <option value="{$role}">{translations name="$translationsGroup.role_$role"}</option>
                {/foreach}
            </select>
        </div>
    </td>
</tr>
{if $element->hasActualStructureInfo()}
    {foreach from=$element->getAuthorsInfo({$type}) item=info}
        <tr>
            <td class="form_label">
                {$info.authorElement->title} ({$info.authorElement->id})
            </td>
            <td class="form_field">
                <div class="authorship_form_component">
                    {if isset($currentElementPrivileges.deleteAuthor) && $currentElementPrivileges.deleteAuthor}
                        <a href="{$element->URL}id:{$element->id}/action:deleteAuthor/authorId:{$info.authorElement->id}/"
                           class="authorship_form_remove form_table_remove"><span
                                    class="form_table_remove_icon"></span> {translations name="$translationsGroup.remove_member"}
                        </a>
                    {/if}
                    {if $displayDate}
                        <input class='authorship_form_startdate input_component' type="text" value="{$info.startDate}"
                               name="{$formNames.addAuthorStartDate}[{$info.authorId}]"
                               placeholder="{translations name="$translationsGroup.add_author_start"}"/>
                        <input class='authorship_form_enddate input_component' type="text" value="{$info.endDate}"
                               name="{$formNames.addAuthorEndDate}[{$info.authorId}]"
                               placeholder="{translations name="$translationsGroup.add_author_end"}"/>
                    {/if}
                    <select class="authorship_form_roles select_multiple" name="{$formNames.addAuthorRole}[{$info.authorId}][]" autocomplete='off'
                            multiple="multiple">
                        {foreach $element->getAuthorRoles() as $role}
                            <option value="{$role}"
                                    {if in_array($role, $info.roles)}selected{/if}>{translations name="$translationsGroup.role_$role"}</option>
                        {/foreach}
                    </select>
                </div>

            </td>
        </tr>
    {/foreach}
{/if}