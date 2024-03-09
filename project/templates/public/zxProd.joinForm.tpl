{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
    <table class='form_table'>
        <tr class="{if $formErrors.joinAndDelete} form_error{/if}">
            <td class="form_label">
                {translations name='zxProd.joinanddelete'}:
            </td>
            <td class="form_field">
                <select class="zxitem_form_prod_select" name="{$formNames.joinAndDelete}" autocomplete='off'></select>
            </td>
        </tr>
        <tr>
            <td class="form_label">
                {translations name='zxprod.releasesOnly'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       autocomplete="off"
                       name="{$formNames.releasesOnly}"{if $element->releasesOnly} checked="checked"{/if}/>
            </td>
        </tr>
    </table>
    {include file=$theme->template('component.controls.tpl') action='join'}
</form>
