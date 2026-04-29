{*{assign moduleTitle $element->title}*}
{assign moduleTag "div"}

{if $currentElement->socMedia_1_Name && $currentElement->socMedia_1_Icon}
	{$sharedUrl = "{$currentElement->getUrlEncoded($currentElement->URL)}"}
	{if $currentElement->generalOwnerName}
		{$generalOwnerName =  $currentElement->getUrlEncoded($currentElement->generalOwnerName|cat:'. ')}
	{/if}
	{if $currentElement->title}
		{$currentTitle = $currentElement->getUrlEncoded($currentElement->title)}
	{/if}
	{$sharedName = "{if $generalOwnerName}{$generalOwnerName}{/if}{if $currentTitle}{$currentTitle}{/if}"}

{*
	//'Facebook',
	//'Twitter',
	//'Google+',
	//'LinkedIn',
*}

	{$shareTitle = ''}
	{if $currentElement->socMedia_1_Name == 'fb'}
		{$href = "https://www.facebook.com/sharer/sharer.php?u={$sharedUrl}&t={$sharedName}"}
		{$shareTitle = 'Facebook'}
		{$smTarget = 'facebook'}
	{elseif $currentElement->socMedia_1_Name == 'tw'}
		{$href = "https://twitter.com/share?url={$sharedUrl}&text={$sharedName}"}
		{$shareTitle = 'Twitter'}
		{$smTarget = 'twitter'}
	{elseif $currentElement->socMedia_1_Name == 'gl'}
		{$href = "https://plus.google.com/share?url={$sharedUrl}"}
		{$shareTitle = 'Google+'}
		{$smTarget = 'google'}
	{elseif $currentElement->socMedia_1_Name == 'li'}
		{$href = "https://www.linkedin.com/shareArticle?mini=true&url={$sharedUrl}&title={$sharedName}&source={$generalOwnerName}"}
		{$shareTitle = 'LinkedIn'}
		{$smTarget = 'linkedin'}
	{/if}
	{$socMedia_1_Icon = "{$controller->baseURL}image/type:newsItemIcon/id:{$currentElement->socMedia_1_Icon}/filename:{$currentElement->socMedia_1_IconOriginalName}"}

	{capture assign="socMedia_1"}
		<a href="{$href}" class="sm_share {$smTarget}" data-sm-target="{$smTarget}" title="{translations name="news.share_on" s=$shareTitle}">
			{include file=$theme->template('component.elementimage.tpl') class="news_icon sm_target sm_$smTarget" src=$socMedia_1_Icon}
		</a>
	{/capture}

{/if}

{capture assign="moduleContent"}
{*
{$currentElement->cols}
{$currentElement->captionLayout}
*}

	<div class="news_card_info">
	{if $currentElement->generalOwnerAvatar}
		{assign var="iconAvatar" value="{$controller->baseURL}image/type:newsItemIcon/id:{$currentElement->generalOwnerAvatar}/filename:{$currentElement->generalOwnerAvatarOriginalName}"}
		<span class="news_card_info_element news_icon_wrapper news_ownwer_avatar">
			{include file=$theme->template('component.elementimage.tpl') type='newsItemIcon' class='news_icon avatar' src=$iconAvatar lazy=true}
		</span>
	{/if}

	{if $currentElement->generalOwnerName}
		<span class="news_card_info_element news_ownwer_name">{$currentElement->generalOwnerName}</span>
	{/if}

	{if $socMedia_1}
		<span class="news_card_info_element news_icons">
		<span class="news_icon_wrapper news_sm">
		{$socMedia_1}
		</span>
		</span>
	{/if}

	<span class='news_card_info_element news_date'>
		{$element->date|date_format:"%e. %B %Y"}
	</span>
</div>
<div class="news_card_about">
	{if $element->introduction}
		<div class='news_card_content html_content_cleaned'>
			<a href="{$element->URL}" class='content_readmore'>
				{$element->getCleanedText($element->introduction, 0, 180, '&hellip;')}
			</a>
		</div>
	{/if}
</div>
<div class="news_card_readmore_wrapper">
	{if $element->content}
		<a href="{$element->URL}" class='news_card_readmore'>
			<span class='button_text'>{translations name='news.news_short_readmore'}</span>
		</a>
	{/if}
</div>
{/capture}

{capture assign="moduleImageBlock"}
	<div class="news_card_image">
		{if $element->thumbImageOriginalName}
			{$elementThumbImagePath = "{$element->thumbImage}/filename:{$element->thumbImageOriginalName}"}
		{elseif $element->originalName}
			{$elementThumbImagePath = "{$element->image}/filename:{$element->originalName}"}
		{/if}

		{if !empty($elementThumbImagePath)}
		<a href="{$element->URL}" class='content_readmore'>
			{assign var="src" value="{$controller->baseURL}image/type:newsCardImage/id:{$elementThumbImagePath}"}
			{include file=$theme->template('component.elementimage.tpl') class='news_card_image' alt="{$element->title}" lazy=true}
		</a>
		{/if}
		{*{capture assign="moduleSideContent"}*}
		{*{/capture}*}
	</div>
{/capture}

{assign moduleClass "news_card"}
{assign moduleTitleClass "news_card_title"}
{assign moduleContentClass "news_card_content_container"}
{assign moduleImageBlockClass "news_card_image_container"}
{include file=$theme->template("component.subcontentmodule_set_cols.tpl") moduleTitle=false colsOnRow={$currentElement->cols}}
