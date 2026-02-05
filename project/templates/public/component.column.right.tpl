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
    {capture assign="moduleTitle"}
        {translations name="radiocontrols.title"}
    {/capture}
    {capture assign="moduleContent"}
        <div class="radio_icon"></div>
        <input type="button" class="button button_sm button_outlined" data-radiotype="discover"
               value="{translations name="radiocontrols.discover"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="randomgood"
               value="{translations name="radiocontrols.randomgood"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="games"
               value="{translations name="radiocontrols.games"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="demoscene"
               value="{translations name="radiocontrols.demoscene"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="lastyear"
               value="{translations name="radiocontrols.lastyear"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="ay"
               value="{translations name="radiocontrols.ay"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="beeper"
               value="{translations name="radiocontrols.beeper"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="exotic"
               value="{translations name="radiocontrols.exotic"}"/>
        <input type="button" class="button button_sm button_outlined" data-radiotype="underground"
               value="{translations name="radiocontrols.underground"}"/>
    {/capture}
    {assign moduleClass "radio_controls"}
    {assign moduleTitleClass ""}
    {assign moduleContentClass ""}
    {include file=$theme->template("component.columnmodule.tpl")}

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

    {capture assign="moduleTitle"}{translations name='label.comments'}{/capture}
    {capture assign="moduleContent"}
        {foreach from=$currentLanguage->getLatestComments() item=comment}
            {include file=$theme->template("comment.column.tpl") element=$comment}
        {/foreach}
        {if isset($commentsElement) && $commentsElement}
            <a class="lastcomments_allcomments" href="{$commentsElement->getParentUrl()}">
                <img loading="lazy" src="{$theme->getImageUrl("icon_comment.png")}"
                     alt="{translations name='label.allcomments'}"/>{translations name='label.allcomments'}
            </a>
        {/if}
    {/capture}
    {assign moduleClass "lastcomments"}
    {assign moduleTitleClass ""}
    {assign moduleContentClass ""}
    {include file=$theme->template("component.columnmodule.tpl")}

    {capture assign="moduleTitle"}{translations name='label.votes'}{/capture}
    {capture assign="moduleContent"}
        <table class="votes_list_table table_component">
            <tbody>
            {foreach from=$currentLanguage->getLatestVotes(20) item=voteInfo name=votes}
                <tr class="">
                    <td>{include file=$theme->template("component.username.tpl") userName=$voteInfo.userName userUrl=$voteInfo.userUrl userType=$voteInfo.userType}</td>
                    <td>{$voteInfo.value}</td>
                    <td><a href="{$voteInfo.imageUrl}">{$voteInfo.imageTitle}</a></td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/capture}
    {assign moduleClass "lastvotes"}
    {assign moduleTitleClass ""}
    {assign moduleContentClass ""}
    {include file=$theme->template("component.columnmodule.tpl")}
</aside>