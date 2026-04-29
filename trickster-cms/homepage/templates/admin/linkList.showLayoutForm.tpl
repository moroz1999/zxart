{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component" method="post" enctype="multipart/form-data" >
    {include file=$theme->template('component.input.layouts_selection.tpl') layouts=$element->getLayoutsSelection() defaultLayout=$element->getDefaultLayout() structureType=$element->structureType}
    {include file=$theme->template('component.controls.tpl') action="receiveLayout"}
</form>