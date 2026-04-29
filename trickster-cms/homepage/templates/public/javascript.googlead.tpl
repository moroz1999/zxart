{$googleAD = $configManager->get('google.ad')}
{if !empty($googleAD)}
	{if $googleAD['main_id']}
		<script async src="https://www.googletagmanager.com/gtag/js?id={$googleAD['main_id']}"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			{literal}function gtag(){dataLayer.push(arguments)}{/literal}
			gtag('js', new Date());
			gtag('config', '{$googleAD['main_id']}');
            {if !empty($googleAD["country"])}
			gtag('set', {
				'country': '{$googleAD["country"]}',
				'currency': '{$googleAD["currency"]}'
			});
			{/if}
			window.google = window.google || { } ;
			window.google.ad = {
				mainId :'{$googleAD["main_id"]}',
				buyId : '{$googleAD["event_buy_id"]}',
				feedbackId : '{$googleAD["event_feedback_id"]}',
				emailId : '{$googleAD["event_email_id"]}'
			};
		</script>
	{/if}
{/if}