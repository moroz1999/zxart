<aside class="right_column">
    {capture assign="moduleTitle"}{/capture}
    {capture assign="moduleContent"}
        <a target="_blank" class="button"
           href="/userfiles/file/zxart_offline.torrent">📦 {translations name="site.download"}</a>
    {/capture}
    {assign moduleClass ""}
    {assign moduleTitleClass ""}
    {assign moduleContentClass ""}
    {include file=$theme->template("component.columnmodule.tpl")}
    <zx-radio-remote></zx-radio-remote>

    {if $currentUser->hasAds()}
        <script async
                src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6845376753120137"
                crossorigin="anonymous"></script>
        <!-- right column -->
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-6845376753120137"
             data-ad-slot="1817296316"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
        {$supportElement = $structureManager->getElementByMarker('support', $currentLanguage->id)}
    {if $supportElement}
        <a href="{$supportElement->getUrl()}">{translations name="label.remove_ads"}</a>
    {/if}
    {/if}

    {if isset($commentsElement) && $commentsElement}
        <zx-latest-comments all-comments-url="{$commentsElement->getParentUrl()}"></zx-latest-comments>
    {/if}

    <zx-recent-ratings></zx-recent-ratings>
</aside>
