{$googleAD = $configManager->get('google.ad')}
{if !empty($googleAD)}
	{$googleAdId = $googleAD['main_id']}
	{$googleId = $googleAdId}
{/if}
{if $analyticsId = $configManager->get('google.analytics.id')}
	{$googleId = $analyticsId}
{else}
	{$analyticsId = $configManager->get('main.googleAnalyticsId')}
{/if}

{if !empty($googleAdId) || !empty($analyticsId)}
	<script async src="https://www.googletagmanager.com/gtag/js?id={$googleId}"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		window.google = window.google || { } ;
		{literal}function gtag(){dataLayer.push(arguments)}{/literal}
		gtag('js', new Date());

		{if !empty($googleAdId)}
			gtag('config', '{$googleAdId}');
			window.google.ad = {
				mainId :'{$googleAD["main_id"]}',
				buyId : '{$googleAD["event_buy_id"]}',
				feedbackId : '{$googleAD["event_feedback_id"]}',
				emailId : '{$googleAD["event_email_id"]}'
			};
			{if !empty($googleAD["country"])}
			gtag('set', {
				'country': '{$googleAD["country"]}',
				'currency': '{$googleAD["currency"]}'
			});
			{/if}
		{/if}
		{if !empty($analyticsId)}
			gtag('js', new Date());
			gtag('config', '{$analyticsId}');
			{if !empty($googleAD["country"])}
			gtag('set', {
				'country': '{$googleAD["country"]}',
				'currency': '{$googleAD["currency"]}'
			});
			{/if}
		{/if}
		{if $configManager->get('google.ecommerce.enabled')}
			window.google.ecommerce = {
				'enabled': true
			};
		{else}
			window.google.ecommerce = {
				'enabled': false
			};
		{/if}
	</script>
	{/if}