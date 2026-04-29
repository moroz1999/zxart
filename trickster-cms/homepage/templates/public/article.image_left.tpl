{if $element->hideTitle==0}
    {if $h1 = $element->getH1()}
        {capture assign="moduleTitle"}
            {$h1}
        {/capture}
    {/if}
{/if}
{capture assign="moduleContent"}
    {if $element->originalName != ''}
        <div class="article_image_wrap">
            {include file=$theme->template('component.elementimage.tpl') type='articleDefaultImage' class='article_image' lazy=true}
        </div>
    {/if}
    {if !empty($element->content)}
        <div class="html_content">
            {$element->content}
        </div>
    {/if}
    {if $subArticles = $element->getSubArticles()}
        {$layout = $element->getCurrentLayout('subLayout')}
        {if $layout == 'accordeon'}
            <div class="article_subarticles article_subarticles_{$layout} accordeon" data-accordeon-mode="click">
                {foreach $subArticles as $subArticle}
                    {include file=$theme->template($subArticle->getTemplate($layout)) element=$subArticle}
                {/foreach}
            </div>
        {else}
            <div class="article_subarticles article_subarticles_{$layout}">
                {foreach $subArticles as $subArticle}
                    {include file=$theme->template($subArticle->getTemplate($layout)) element=$subArticle}
                {/foreach}
            </div>
        {/if}
    {/if}
{/capture}
{assign moduleClass "article_block article_layout_{$element->getCurrentLayout()}"}
{assign moduleTitleClass "article_heading"}
{assign moduleContentClass "article_content"}
{include file=$theme->template("component.contentmodule.tpl")}