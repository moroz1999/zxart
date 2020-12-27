{capture assign="moduleContent"}
    <a href="{$release->getUrl()}">
        <span class="zxrelease_short_images">
            <img class="zxrelease_short_image" src="{$release->getImageUrl(0)}"/>
            <img class="zxrelease_short_image_second" src="{$release->getImageUrl(1)}"/>
        </span>
        <span class="zxrelease_short_title">{$release->getHumanReadableName()}</span>
    </a>
{/capture}
{$moduleSubContent = $moduleContent}
{assign moduleClass "zxrelease_short"}
{assign moduleAttributes ""}
{assign moduleTitle ""}
{assign moduleTitleClass "zxrelease_short_title"}
{assign moduleContentClass ""}
{include file=$theme->template("component.subcontentmodule_square.tpl")}