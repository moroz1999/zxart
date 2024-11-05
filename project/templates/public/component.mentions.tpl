{$articles = $element->mentions}
{if $articles}
    <h3>{translations name='press.mentions'}</h3>
    {foreach $articles as $article}
        <div>
            <a href="{$article->getUrl()}">{$article->getTitle()}</a>
        </div>
    {/foreach}
{/if}