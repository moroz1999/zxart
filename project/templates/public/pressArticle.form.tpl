{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
    <table class='form_table'>
        <tr {if $formErrors.title} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.title'}:
            </td>
            <td class="form_field">
                <input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}"/>
            </td>
        </tr>
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
                {translations name='zxprod.authors'}:
            </td>
            <td class="form_field">
                <select class="select_multiple zxitem_form_authors_select" multiple="multiple"
                        name="{$formNames.authors}[]" autocomplete='off'>
                    <option value=''></option>
                    {foreach $formData.authors as $publisher}
                        <option value='{$publisher->id}' selected="selected">
                            {$publisher->getSearchTitle()}
                        </option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr {if $formErrors.introduction} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.introduction'}:
            </td>
            <td class="form_field">
                <textarea class='textarea_component' name="{$formNames.introduction}">{$formData.introduction}</textarea>
            </td>
        </tr>
        <tr {if $formErrors.content} class="form_error"{/if}>
            <td class="form_label">
                {translations name='pressArticle.content'}:
            </td>
            <td class="form_field">
                <textarea class='textarea_component' name="{$formNames.content}">{$formData.content}</textarea>
            </td>
        </tr>
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