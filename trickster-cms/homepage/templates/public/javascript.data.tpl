<script>

	window.rootURL = '{$controller->rootURL}';
	window.ajaxURL = '{$controller->baseURL}ajax/';
	window.baseURL = '{$controller->baseURL}';
	window.applicationName = '{$applicationName}';
	window.currentElementURL = '{$currentElement->URL}';
	window.javascriptUrl = '{$theme->getJavascriptUrl()}';
	window.currentElementTitle = '{$currentElement->getTitle()}';
	window.currentElementId = '{$currentElement->id}';
	window.newVisitor = {json_encode($newVisitor)};
	window.settings = {json_encode($settings)};
	window.currentLanguageCode = '{$currentLanguage->iso6393}';
	{if !empty($selectedCurrencyItem)}
	window.selectedCurrencyItem =
		{
			'symbol': '{$selectedCurrencyItem->symbol}'
		};
	{/if}
	window.galleriesInfo = window.galleriesInfo || {ldelim}{rdelim};
	{if isset($shoppingBasket)}
		window.shoppingBasketURL = '{$shoppingBasket->URL}';
		window.conditionsLink = '{$shoppingBasket->conditionsLink}';
		{$location = $breadcrumbsManager->getBreadcrumbs()}
			{if $location}
				window.shopLink = '{$location[0]['URL']}';
			{/if}
		window.addToBasketButtonAction = '{$shoppingBasket->addToBasketButtonAction}';
	{/if}
</script>
{if isset($shoppingBasket)}
	{include file=$theme->template('javascript.shoppingBasket.tpl') element=$shoppingBasket}
{/if}
{include file=$theme->template('javascript.translations.tpl')}
{include file=$theme->template('javascript.gtag.tpl')}
{include file=$theme->template('javascript.yandexMetrika.tpl')}