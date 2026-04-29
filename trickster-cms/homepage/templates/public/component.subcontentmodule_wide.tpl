<{if !empty($moduleTag)}{$moduleTag}{else}section{/if} class="subcontentmodule_component subcontentmodule_wide{if !empty($moduleClass)} {$moduleClass}{/if}" {if !empty($moduleAttributes)}{$moduleAttributes}{/if}>
	{if !empty($moduleSideContent)}<span class="subcontentmodule_side{if !empty($moduleSideContentClass)} {$moduleSideContentClass}{/if}">{$moduleSideContent}</span>{/if}
	<span class="subcontentmodule_center">
		<span class="subcontentmodule_center_top">
			{if !empty($moduleTitle)}<h2 class="subcontentmodule_title{if !empty($moduleTitleClass)} {$moduleTitleClass}{/if}"{if !empty($moduleTitleAttributes)} {$moduleTitleAttributes}{/if}>{$moduleTitle}</h2>{/if}
			{if !empty($moduleContent)}<span class="subcontentmodule_content{if !empty($moduleContentClass)} {$moduleContentClass}{/if}">{$moduleContent}</span>{/if}
		</span>
		{if !empty($moduleControls)}
			<span class="subcontentmodule_wide_controls subcontentmodule_controls{if !empty($moduleControlsClass)} {$moduleControlsClass}{/if}">
				{if !empty($moduleSideContent)}
					<span class="subcontentmodule_wide_controls_side"></span>
				{/if}
				<span class="subcontentmodule_wide_controls_content">{$moduleControls}</span>
			</span>
		{/if}
	</span>
</{if !empty($moduleTag)}{$moduleTag}{else}section{/if}>