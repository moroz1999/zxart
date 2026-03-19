<header class='header_block'>
	<div class='header_inner'>
		<zx-mobile-nav></zx-mobile-nav>
		{include file=$theme->template("component.logo.tpl")}
		<div class='menu_block'>
			<a href="/about/contact-us/"
			   rel="nofollow noopener noreferrer"
			   aria-hidden="true"
			   tabindex="-1"
			   style="position:absolute; left:-99999px; top:auto; width:1px; height:1px; overflow:hidden;"
			>—</a>
			<zx-menu-block></zx-menu-block>
		</div>
		<div class="header_column">
            <zx-language-trigger></zx-language-trigger>
            <zx-theme-trigger></zx-theme-trigger>
            <zx-picture-settings-trigger></zx-picture-settings-trigger>
            <zx-login-trigger></zx-login-trigger>
        </div>
	</div>
</header>
