<header class='header_block'>
	<div class='header_inner'>
		{include file=$theme->template("component.logo.tpl")}
		<div class='menu_block'>
			<a href="/about/contact-us/"
			   rel="nofollow noopener noreferrer"
			   aria-hidden="true"
			   tabindex="-1"
			   style="position:absolute; left:-99999px; top:auto; width:1px; height:1px; overflow:hidden;"
			>—</a>
			{if $subMenuList = $currentLanguage->getElementFromHeader('subMenuList')}
				{include file=$theme->template("subMenuList.header.tpl") element=$subMenuList}
			{/if}
		</div>
		<div class="header_column">
		{include file=$theme->template("component.languages.tpl")}
		<div class='settings_block'>
			{if $currentMode.hidden === '0'}
				<div>
					{translations name='label.gigascreenmode'}:
					{if $currentMode.mode == 'mix' || $currentMode.mode == 'public'}
						<b>Mix</b>
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:flicker' value="Flicker" />
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:interlace1' value="int1" />
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:interlace2' value="int2" />
					{elseif $currentMode.mode == 'flicker'}
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:mix' value="Mix" />
						<b>Flicker</b>
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:interlace1' value="int1" />
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:interlace2' value="int2" />
					{elseif $currentMode.mode == 'interlace1'}
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:mix' value="Mix" />
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:flicker' value="Flicker" />
						<b>int1</b>
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:interlace2' value="int2" />
					{elseif $currentMode.mode == 'interlace2'}
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:mix' value="Mix" />
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:flicker' value="Flicker" />
						<input type="button" class="button button_xs button_outlined"  data-operation='border:{$currentMode.border}/mode:interlace1' value="int1" />
						<b>int2</b>
					{/if}
				</div>
				<div>
					{translations name='label.border'}:
					{if $currentMode.border === '1'}
						<b>ON</b>
						<input type="button" class="button button_xs button_outlined"  data-operation='border:0/mode:{$currentMode.mode}' value="OFF" />
					{else}
						<input type="button" class="button button_xs button_outlined"  data-operation='border:1/mode:{$currentMode.mode}' value="ON" />
						<b>OFF</b>
					{/if}
				</div>
			{/if}
			<div>
				{translations name='label.hidden'}:
				{if $currentMode.hidden === '1'}
					<b>ON</b>
					<input type="button" class="button button_xs button_outlined"  data-operation='hidden:0' value="OFF" />
				{else}
					<input type="button" class="button button_xs button_outlined"  data-operation='hidden:1' value="ON" />
					<b>OFF</b>
				{/if}
			</div>
		</div>
	</div>
	{if $currentLanguage->getElementFromHeader('login')}
		{include file=$theme->template("login.header.tpl") element=$currentLanguage->getElementFromHeader('login')}
	{/if}
	</div>
</header>