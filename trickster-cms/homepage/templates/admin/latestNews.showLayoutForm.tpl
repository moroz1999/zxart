{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component" method="post" enctype="multipart/form-data" >
    {include file=$theme->template('component.input.layouts_selection.tpl') propertyName="layout" layouts=$element->getLayoutsSelection("layout") defaultLayout=$element->getDefaultLayout("layout") structureType=$element->structureType}
    {include file=$theme->template('component.input.layouts_selection.tpl') layouts=$element->getLayoutsSelection('column') defaultLayout=$element->getDefaultLayout('column') structureType=$element->structureType propertyName='column'}
    {include file=$theme->template('component.controls.tpl') action="receiveLayout"}
</form>