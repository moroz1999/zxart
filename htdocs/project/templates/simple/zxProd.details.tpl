{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
<b>{translations name='zxprod.title'}:</b> {$element->title}<br>
{if $element->language}
    <b>{translations name='zxprod.language'}: </b>{$element->getSupportedLanguageString()}<br>
{/if}
<b>{translations name='zxProd.legalstatus'}: </b> {translations name="legalstatus.{$element->getLegalStatus()}"}<br>
{if $groups=$element->getGroupsList()}
    <b>{translations name='zxprod.groups'}:</b> {foreach $groups as $group}
        <a href="{$group->getUrl()}">{$group->title}</a>{if !$group@last}, {/if}
    {/foreach}<br>
{/if}
{if $publishers=$element->getPublishersList()}
    <b>{translations name='zxprod.publishers'}: </b>{foreach $publishers as $publisher}
        <a href="{$publisher->getUrl()}">{$publisher->title}</a>{if !$publisher@last}, {/if}
    {/foreach} <br>
{/if}
{if $authors=$element->getAuthorsInfo('prod')}
    <b>{translations name='zxprod.authors'}: </b>{foreach $authors as $info}
        <a href="{$info.authorElement->getUrl()}">{$info.authorElement->title}</a>{if $info.roles} ({foreach $info.roles as $role}{translations name="zxprod.role_$role"}{if !$role@last}, {/if}{/foreach}){/if}{if !$info@last}, {/if}
    {/foreach}<br>
{/if}
{if $element->getPartyElement()}
    <b>{translations name='zxprod.party'}: </b> {assign 'compoTitle' "compo_"|cat:$element->compo}
    <a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a>
    {if !empty($element->compo)}({if !empty($element->partyplace)}{$element->partyplace}, {/if}{translations name="party.$compoTitle"}){/if}<br>
{/if}
{if $element->year != '0'}
    <b>
        {translations name='zxprod.year'}:
    </b>
    <a href="{$picturesDetailedSearchElement->URL}startYear:{$element->year}/endYear:{$element->year}/">{$element->year}</a><br>
{/if}
{if $element->getTagsList()}
<b>{translations name='zxprod.tags'}:</b>{foreach from=$element->getTagsList() item=tag name=tags}
        <a href='{$tag->URL}'>{$tag->title}</a>{if !$smarty.foreach.tags.last}, {/if}
    {/foreach}<br>
{/if}
{include file=$theme->template('component.links.tpl')}
<b>{translations name='zxprod.votes'}:</b>{if !$element->isVotingDenied() && $element->getVotePercent()}{$element->votes}{/if}<br>
{assign var="userElement" value=$element->getUser()}
{if $userElement}
    <b>
        {translations name='zxprod.addedby'}:
    </b>
    {$userElement->userName}, {$element->dateCreated}
    <br>
{/if}
<br>{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>
{if $description = $element->getGeneratedDescription()}
    {$description}
{/if}
{if $releasesList=$element->getReleasesList()}{include file=$theme->template('component.releasestable.tpl') releasesList=$releasesList pager=false}{/if}
{if $filesList = $element->getFilesList('connectedFile')}
    {include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodImage' displayTitle=false}
{/if}
{*{if $filesList = $element->getFilesList('mapFilesSelector')}*}
    {*<h3>{translations name='zxprod.maps'}</h3>*}
    {*{include file=$theme->template('zxItem.images.tpl') filesList = $filesList preset='prodMapImage' displayTitle=true}*}
{*{/if}*}
<br>{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>
{if $description = $element->getDescription()}
    {$description}
    <br>{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>
{/if}

{include file=$theme->template('component.comments.tpl')}
{if $element->denyComments}{translations name="zxitem.commentsdenied"}<br>{/if}

{include file=$theme->template('component.voteslist.tpl')}
{if $element->denyVoting}<p>{translations name="zxitem.votingdenied"}</p>{/if}
{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>

{foreach from=$element->getPictures() item=picture}{include file=$theme->template("zxPicture.short.tpl") element=$picture}{/foreach}
{if $element->getTunes()}
    {include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>
    {include file=$theme->template("component.musictable.tpl") musicList=$element->getTunes() element=$element}
{/if}