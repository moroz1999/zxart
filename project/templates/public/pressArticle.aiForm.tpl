{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
    <table class='form_table'>
        <tr>
            <td class="form_label">
                {translations name='pressArticle.aiRestartFix'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.aiRestartFix}"{if $element->aiRestartFix} checked="checked"{/if}/>
            </td>
        </tr>
        <tr>
            <td class="form_label">
                {translations name='pressArticle.aiRestartTranslate'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.aiRestartTranslate}"{if $element->aiRestartTranslate} checked="checked"{/if}/>
            </td>
        </tr>
        <tr>
            <td class="form_label">
                {translations name='pressArticle.aiRestartParse'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.aiRestartParse}"{if $element->aiRestartParse} checked="checked"{/if}/>
            </td>
        </tr>
        <tr>
            <td class="form_label">
                {translations name='pressArticle.aiRestartSeo'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.aiRestartSeo}"{if $element->aiRestartSeo} checked="checked"{/if}/>
            </td>
        </tr>
   </table>

    {include file=$theme->template('component.controls.tpl') action="receiveAiForm"}
</form>