{if $element->title}
    {capture assign="moduleTitle"}
        {$element->title}
    {/capture}
{/if}
{capture assign="moduleContent"}
    {include file=$theme->template("component.location_controls.tpl")}
    {include file=$theme->template('component.zxmap.tpl') locations=$element->getCitiesList()}
    {include file=$theme->template("component.location_lists.tpl")}
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}