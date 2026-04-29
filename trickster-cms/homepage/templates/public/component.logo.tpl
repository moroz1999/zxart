{if $logoImage = $currentLanguage->getLogoImageUrl()}
	{if $firstPageElement = $currentLanguage->getFirstPageElement()}
		<a href="{$firstPageElement->getUrl()}" class="logo_block">
			<img class="logo_image" src="{$logoImage}" alt="Logo" />
		</a>
	{/if}
{/if}