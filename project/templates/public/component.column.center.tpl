<div class="center_column" role="main">
    {if $firstPageElement->final}
        <zx-firstpage></zx-firstpage>
    {else}
        {include file=$theme->template("component.breadcrumbs.tpl")}
        {include file=$theme->template("component.letters.tpl")}
        {include file=$theme->template("component.years.tpl")}
        {include file=$theme->template($currentElement->getTemplate()) element=$currentElement}
        {if $currentUser->hasAds()}
            <div class="center-column-ads">
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6845376753120137"
                        crossorigin="anonymous"></script>
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-format="autorelaxed"
                     data-ad-client="ca-pub-6845376753120137"
                     data-ad-slot="2039769540"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            </div>
        {/if}
    {/if}
</div>
