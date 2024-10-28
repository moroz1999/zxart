{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
    <table class='form_table'>
        {foreach from=$formData.title key=languageId item=title}
            <tr {if $formErrors.title.$languageId} class="form_error"{/if}>
                <td class="form_label">
                    {translations name='pressArticle.title'} ({$languageNames.$languageId}):
                </td>
                <td class="form_field">
                    <input class='input_component' type="text" value="{$title}" name="{$formNames.title.$languageId}" />
                </td>
            </tr>
        {/foreach}
        <tr {if $formErrors.externalLink} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.externalLink'}:
            </td>
            <td class="form_field">
                <input class='input_component' type="text" value="{$formData.externalLink}"
                       name="{$formNames.externalLink}"/>
            </td>
        </tr>
        <tr {if $formErrors.authors} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.authors'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_authors_select" multiple="multiple"
                        name="{$formNames.authors}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.authors as $author}
                        <option value='{$author->id}' selected="selected">
                            {$author->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr {if $formErrors.people} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.people'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_authors_select" multiple="multiple"
                        name="{$formNames.people}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.people as $author}
                        <option value='{$author->id}' selected="selected">
                            {$author->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr {if $formErrors.software} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.software'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_prod_select" multiple="multiple"
                        name="{$formNames.software}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.software as $author}
                        <option value='{$author->id}' selected="selected">
                            {$author->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr {if $formErrors.groups} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.groups'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_groups_select" multiple="multiple"
                        name="{$formNames.groups}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.groups as $author}
                        <option value='{$author->id}' selected="selected">
                            {$author->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr {if $formErrors.parties} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.parties'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_party_select" multiple="multiple"
                        name="{$formNames.parties}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.parties as $author}
                        <option value='{$author->id}' selected="selected">
                            {$author->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr {if $formErrors.tunes} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.tunes'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_tunes_select" multiple="multiple"
                        name="{$formNames.tunes}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.tunes as $author}
                        <option value='{$author->id}' selected="selected">
                            {$author->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr {if $formErrors.pictures} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.pictures'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_pictures_select" multiple="multiple"
                        name="{$formNames.pictures}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.pictures as $author}
                        <option value='{$author->id}' selected="selected">
                            {$author->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>

        {foreach from=$formData.introduction key=languageId item=introduction}
            <tr {if $formErrors.introduction.$languageId} class="form_error"{/if}>
                <td class="form_label">
                    {translations name='pressArticle.introduction'} ({$languageNames.$languageId}):
                </td>
                <td class="form_field">
                    <textarea class='textarea_component' name="{$formNames.introduction.$languageId}">{$introduction}</textarea>
                </td>
            </tr>
        {/foreach}
        {foreach from=$formData.content key=languageId item=content}
            <tr {if $formErrors.content.$languageId} class="form_error"{/if}>
                <td class="form_label">
                    {translations name='pressArticle.content'} ({$languageNames.$languageId}):
                </td>
                <td class="form_field">
                    <textarea class='textarea_component' name="{$formNames.content.$languageId}">{$content}</textarea>
                </td>
            </tr>
        {/foreach}
        <tr>
            <td class="form_label">
                {translations name='pressArticle.allowcomments'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.allowComments}"{if $element->allowComments} checked="checked"{/if}/>
            </td>
        </tr>
   </table>

    {include file=$theme->template('component.controls.tpl') action="publicReceive"}
</form>