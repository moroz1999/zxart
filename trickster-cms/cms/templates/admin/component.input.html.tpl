{if !isset($item.class)}
    {$item.class = ''}
{/if}
<div class="form_field_{$fieldName} form_items">
    <span class="form_label">
        {translations name="{$translationGroup}.{strtolower($fieldName)}"}:
    </span>
    <div class="form_field content_editor">
        {include file=$theme->template('component.htmleditor.tpl') data=$formData.$fieldName name=$formNames.$fieldName fieldName=$fieldName className=$item.class}
    </div>
    {include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name=$fieldName}
</div>