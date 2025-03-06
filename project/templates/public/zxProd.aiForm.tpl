{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="zxitem_form" enctype="multipart/form-data">
    <table class='form_table'>
        <tr>
            <td class="form_label">
                {translations name='zxprod.aiRestartSeo'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.aiRestartSeo}"{if $element->aiRestartSeo} checked="checked"{/if}/>
            </td>
            <td class="form_field">
                {$element->getQueueStatus(ZxArt\Queue\QueueType::AI_SEO)}
            </td>
        </tr>
        <tr>
            <td class="form_label">
                {translations name='zxprod.aiRestartIntro'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.aiRestartIntro}"{if $element->aiRestartIntro} checked="checked"{/if}/>
            </td>
            <td class="form_field">
                {$element->getQueueStatus(ZxArt\Queue\QueueType::AI_INTRO)}
            </td>
        </tr>
        <tr>
            <td class="form_label">
                {translations name='zxprod.aiRestartCategories'}:
            </td>
            <td class="form_field">
                <input class='checkbox_placeholder' type="checkbox" value="1"
                       name="{$formNames.aiRestartCategories}"{if $element->aiRestartCategories} checked="checked"{/if}/>
            </td>
            <td class="form_field">
                {$element->getQueueStatus(ZxArt\Queue\QueueType::AI_CATEGORIES_TAGS)}
            </td>
        </tr>
   </table>

    {include file=$theme->template('component.controls.tpl') action="receiveAiForm"}
</form>