{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
<img src='{$element->getImageUrl()}'/>
{include file=$theme->template("component.pictureinfo.tpl")}
{include file=$theme->template('component.comments.tpl')}
{if $element->denyComments}<p>{translations name="zxitem.commentsdenied"}</p>{/if}
{include file=$theme->template('component.voteslist.tpl')}
{if $element->denyVoting}<p>{translations name="zxitem.votingdenied"}</p>{/if}
{if $element->getReleaseElement()}
{assign bestPictures $element->getReleaseElement()->getBestPictures(3, $element->id)}
{if $bestPictures}
{translations name="picture.morefromgame"}<br><br>
{foreach from=$bestPictures item=picture}
	{include file=$theme->template('zxPicture.short.tpl') element=$picture}
{/foreach}
{/if}
{else}
	{assign bestPictures $element->getBestAuthorsPictures(3)}
	{if $bestPictures}
		{translations name="picture.morefromauthor"}<br><br>
		{foreach from=$bestPictures item=picture}
			{include file=$theme->template('zxPicture.short.tpl') element=$picture}
		{/foreach}
	{/if}
{/if}