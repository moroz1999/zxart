{$articles = $element->articles}
{if $articles !== []}
    <div class="mentions_block">
        <h1 class="mentions_heading">{translations name='author.articles'}</h1>
        <div class="mentions_articles">
        {foreach $articles as $article}
            {$pressElement = $article->getParent()}
            <div class="mentions_article">
                {if $pressElement}<a href="{$pressElement->getUrl()}">{$pressElement->getTitle()}</a>{if $pressElement->year} ({$pressElement->year}){/if} / {/if}<a href="{$article->getUrl()}">{$article->getTitle()}</a>
                {if $article->introduction}<div class="mentions_article_description">{$article->introduction}</div>{/if}
            </div>
        {/foreach}
        </div>
    </div>
{/if}