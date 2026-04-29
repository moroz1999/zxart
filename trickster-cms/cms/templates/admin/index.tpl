<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Administration{if $currentElement} - {$currentElement->getTitle()}{/if}</title>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="{$controller->baseURL}css/set:{$theme->getCode()}/file:{$CSSFileName}.css" />
	{if method_exists($theme, 'getFavicon')}
		<link rel="shortcut icon" href="{$theme->getFavicon()}"/>{/if}

	<script src="{$controller->baseURL}libs/ckeditor/ckeditor.js"></script>
	<script src="{$controller->baseURL}libs/ckfinder/ckfinder.js"></script>
	<script src="{$controller->baseURL}libs/chartjs/dist/Chart.js"></script>
	<script src="{$controller->baseURL}libs/jscolor/jsColorPicker.min.js"></script>
</head>
<body>
{if isset($loginForm) && $loginForm->displayForm()}
	<div class="loginpage">
		<div class="loginpage_background_alternate"></div>
		<canvas class="loginpage_background"></canvas>
		<script>
			window.addEventListener('load', loadHandler);

			function loadHandler() {

				var canvas = document.querySelector(".loginpage_background");
				var altBG = document.querySelector(".loginpage_background_alternate");
				var triangle = new TriangleBG({
					canvas: canvas,
					alternateElem: altBG,
					cellHeight: 120,
					cellWidth: 120,
					mouseLight: true,
					mouseLightRadius: 800,
					mouseLightIncrement: 16,
					resizeAdjustment: true,
					variance: 1.2,
					pattern: "x*y",
					baseColor1: {
						baseHue: 222,
						baseSaturation: 53,
						baseLightness: 21
					},
					baseColor2: {
						baseHue: 222,
						baseSaturation: 53,
						baseLightness: 21
					},
					colorDelta: {
						hue: 0.0001,
						lightness: 10,
						saturation: 0
					}
				});
				document.querySelector('.adminlogin_input').blur();
			}

		</script>
		<div class="loginpage_center">
			<img class="login_logo_img" alt="{translations name='engine.name'}" src="{$theme->getImageUrl("trickster-logo.svg")}">
			{include file=$theme->template($loginForm->getTemplate()) element=$loginForm}
		</div>
	</div>
{else}
	<header class="header">
		<div class="header_floating">
			{*      Info block (site, linktree)*}
			<div class="info_block">
				{include file=$theme->template('block.currentlocation.tpl')}
				<div class="pipe"></div>
				<div class="page_link"><a class="page_link_item" href="{$controller->baseURL}redirect/type:element/id:{$currentElement->id}/code:{$currentLanguage->iso6393}/" target="_blank">{translations name='header.open_in_public'}</a><span class="icon icon_page_link"></span></div>
			</div>
			<div class="header_user_info">
				{if !empty($userElement)}
					<span class="user_details">
								{if $userElement}
									<a href="{$userElement->URL}id:{$userElement->id}/action:showForm">{$user->getName()}
										<span class="icon icon_user"></span>
									</a>
								{else}
									{$user->getName()}
								{/if}
							</span>
				{/if}
				<span class="logout">
					{if isset($loginForm) && $loginForm}
						<a class="logout_button" href="{$loginForm->URL}id:{$loginForm->id}/action:logout">
							{translations name='label.logout'}
							<span class="icon icon_logout"></span>
						</a>
					{/if}
				</span>
			</div>
		</div>
	</header>
	<div class="left_panel">
		<div class="lef_panel_content">
			<button class="button primary_button left_menu_hide_button"></button>
			<div class="left_panel_top">
				<div class="logo_container">
					<a class="mainpage_logo" href="{$controller->rootURL}">
						<img class="mainpage_logo_img" alt="{translations name='engine.name'}" src="{$theme->getImageUrl("trickster-logo.svg")}">
					</a>
				</div>
				<div class="language">
					{if $languagesList}
						{include file=$theme->template('languageSelector.tpl')}
					{/if}
				</div>
			</div>
		</div>
		<div class="left_panel_ajaxsearch_block ajaxsearch_block">
			<div class="placeholder_icon">
				<span class="icon icon_search"></span>
			</div>
			<input type="text" class="ajaxsearch_input" placeholder="{translations name="header.search"}" value=""{if $allowedSearchTypes} data-types="{$allowedSearchTypes}"{/if} />
		</div>
		<div class="treemenu_component">
			{include file=$theme->template('block.tree.tpl') menuLevel=$leftMenu level=0 requests_level = 0}
		</div>
		<div class="standalone_menu"></div>
	</div>
	<div class="mobile_control">
		<button class="left_menu_toggle"><span class="icon icon_mobile_left_menu"></span></button>
		<button class="tabs_items_toggle"></button>
	</div>
	<div class="content">
		{if $currentElement}
			{include file=$theme->template($currentElement->getTemplate()) element=$currentElement}
		{else}
			{include file=$theme->template('block.error.tpl') message={translations name="engine.element_not_found_error"}|cat:':'}
		{/if}
	</div>
{/if}
{include file=$theme->template("javascript.data.tpl")}
{if !empty($JSFileName)}{foreach $JSFileName as $script}<script defer type="text/javascript" src="{$script}"></script>{/foreach}{/if}
</body>
</html>