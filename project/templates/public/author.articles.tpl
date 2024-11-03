{$articles = $element->articles}
{if $articles}
    <h3>{translations name='author.articles'}</h3>
    {foreach $articles as $article}
        <div>
            <a href="{$article->getUrl()}">{$article->getTitle()}</a>
        </div>
    {/foreach}
{/if}