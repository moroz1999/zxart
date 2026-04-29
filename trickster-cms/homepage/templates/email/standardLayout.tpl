<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		<div class="background_block">
		{if isset($webLink)}
			<div class="email_websitelink">
				<a href="{$webLink}">{translations name="email.clicktoseefromwebsite"}</a>
			</div>
		{/if}
			<div class="main_block">
				<div class="header_block">
					<table class="header_table">
						<tr>
							<td class="header_left">
								<a class="logo_block" href="{$controller->baseURL}">
									{if $language = $dispatchmentType->getCurrentLanguageElement()}
										{$logo = $language->getLogoImageUrl()}
									{/if}
									<img class="logo_image" src="{$logo}" alt="Logo" />
								</a>
								{if $trackerUrl = $dispatchmentType->getTrackedBlankImage()}
									<img src="{$trackerUrl}" alt="" />
								{/if}
							</td>
							<td class="header_right">
								{translations name="email.header"}
							</td>
						</tr>
					</table>
				</div>
				<div class="content_block">
				    {include file=$contentTemplate}
				</div>
				<div class="footer_block">{translations name="email.footer"}</div>
			</div>
		</div>
	{if !empty($unsubscribeLink)}
		<div class="email_unsubscribelink">
			<a href="{$unsubscribeLink}">{translations name="email.unsubscribe"}</a>
		</div>
	{/if}
	</body>
</html>