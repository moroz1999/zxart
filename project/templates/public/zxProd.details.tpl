{$moduleTitle = $element->getH1()}
{capture assign="moduleContent"}
    <zx-prod-details element-id="{$element->id}"></zx-prod-details>
{/capture}

{assign moduleClass "zxprod_details"}
{assign moduleAttributes "id='gallery_{$currentElement->id}'"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}
