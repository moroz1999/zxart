{foreach from=$formData.$fieldName key=languageId item=introduction}
    <div class="form_items {if !empty($item.class)}{$item.class}{/if} {if $formErrors.$fieldName.$languageId} form_error{/if}" {if !empty($item.style)}style="{$item.style}"{/if}>
        <span class="form_label">
            {translations name="{$translationGroup}.{strtolower($fieldName)}"} ({$languageNames.$languageId})
        </span>
        <div class="form_field content_editor">
            {include file=$theme->template('component.htmleditor.tpl') data=$introduction name=$formNames.$fieldName.$languageId}
        </div>
        {include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}
    </div>
{/foreach}