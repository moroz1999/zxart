{capture assign="moduleContent"}
    <a href="{$element->getUrl()}">
        <span class="zxrelease_short_images">
            <img class="zxrelease_short_image" src="{$element->getImageUrl(0)}"/>
            <img class="zxrelease_short_image_second" src="{$element->getImageUrl(1)}"/>
        </span>
        <span class="zxrelease_short_title">{$element->getTitle()}</span>
    </a>
{/capture}
{$moduleSubContent = $moduleContent}
{assign moduleClass "zxrelease_short"}
{assign moduleAttributes ""}
{assign moduleTitle ""}
{assign moduleTitleClass "zxrelease_short_title"}
{assign moduleContentClass ""}
{include file=$theme->template("component.subcontentmodule_square.tpl")}