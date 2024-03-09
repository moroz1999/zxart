{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
    <table class='form_table'>
        {foreach $element->getSplitData() as $groupKey=>$itemsGroup}
            <tr>
                <td colspan="2"><h3>{$groupKey}</h3></td>
            </tr>
            {foreach $itemsGroup as $key=>$item}
                <tr class="{if $formErrors.splitData} form_error{/if}">
                    <td class="form_label">
                        {$key}:
                    </td>
                    <td class="form_field">
                        <label><input type="checkbox" class="checkbox_placeholder" value="1" name="{$formNames.splitData}[{$groupKey}][{$key}]"/> {$item}</label>
                    </td>
                </tr>
            {/foreach}
        {/foreach}
    </table>
    {include file=$theme->template('component.controls.tpl') action='split'}
</form>
