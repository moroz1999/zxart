<aside class="right_column">
{capture assign="moduleTitle"}
	{translations name="radiocontrols.title"}
{/capture}
{capture assign="moduleContent"}
	<div class="radio_icon"></div>
	<input type="button" class="button radio_controls" data-radiotype="discover" value="{translations name="radiocontrols.discover"}" />
	<input type="button" class="button radio_controls" data-radiotype="randomgood" value="{translations name="radiocontrols.randomgood"}" />
	<input type="button" class="button radio_controls" data-radiotype="games" value="{translations name="radiocontrols.games"}" />
	<input type="button" class="button radio_controls" data-radiotype="demoscene" value="{translations name="radiocontrols.demoscene"}" />
	<input type="button" class="button radio_controls" data-radiotype="lastyear" value="{translations name="radiocontrols.lastyear"}" />
	<input type="button" class="button radio_controls" data-radiotype="ay" value="{translations name="radiocontrols.ay"}" />
	<input type="button" class="button radio_controls" data-radiotype="beeper" value="{translations name="radiocontrols.beeper"}" />
	<input type="button" class="button radio_controls" data-radiotype="exotic" value="{translations name="radiocontrols.exotic"}" />
	<input type="button" class="button radio_controls" data-radiotype="underground" value="{translations name="radiocontrols.underground"}" />
{/capture}
{assign moduleClass "radio_controls"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}
{include file=$theme->template("component.columnmodule.tpl")}

<div class="lastcomments">
    <h3>{translations name='label.comments'}</h3>
	{foreach from=$currentLanguage->getLatestComments() item=comment}
		{include file=$theme->template("comment.column.tpl") element=$comment}
	{/foreach}
	{if isset($commentsElement) && $commentsElement}
	    <a class="lastcomments_allcomments" href="{$commentsElement->getParentUrl()}">
	        <img loading="lazy" src="{$theme->getImageUrl("icon_comment.png")}" alt="{translations name='label.allcomments'}"/>{translations name='label.allcomments'}
	    </a>
	{/if}
</div>
<div class="lastvotes">
    <h3>{translations name='label.votes'}</h3>
		<table class="votes_list_table table_component">
		<tbody>
			{foreach from=$currentLanguage->getLatestVotes() item=voteInfo name=votes}
			<tr class="">
				<td>{$voteInfo.userName}</td>
				<td>{$voteInfo.value}</td>
				<td><a href="{$voteInfo.imageUrl}">{$voteInfo.imageTitle}</a></td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
<div class="zxbn">
	{if $currentLanguage->iso6393 == 'rus'}
		<!--/*
		  *
		  * Revive Adserver Asynchronous JS Tag
		  * - Generated with Revive Adserver v4.0.1
		  *
		  */-->

		<ins data-revive-zoneid="2" data-revive-target="_blank" data-revive-id="7b21834437781b35285bb6ea887b8b50"></ins>
		<script async src="//zxbn.maros.pri.ee/www/delivery/asyncjs.php"></script>
	{else}
		<!--/*
		  *
		  * Revive Adserver Asynchronous JS Tag
		  * - Generated with Revive Adserver v4.0.1
		  *
		  */-->

		<ins data-revive-zoneid="3" data-revive-id="7b21834437781b35285bb6ea887b8b50"></ins>
		<script async src="//zxbn.maros.pri.ee/www/delivery/asyncjs.php"></script>
	{/if}
</div>
</aside>