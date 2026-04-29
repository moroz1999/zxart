<section class="subcontentmodule_component{if isset($moduleClass)} {$moduleClass}{/if}" {if isset($moduleAttributes)}{$moduleAttributes}{/if}>
	{if isset($moduleSideContent) && $moduleSideContent != ''}<div class="subcontentmodule_side{if isset($moduleSideContentClass)} {$moduleSideContentClass}{/if}">{$moduleSideContent}</div>{/if}
	<div class="subcontentmodule_center">
		{if isset($moduleTitle) && $moduleTitle != ''}<h2 class="subcontentmodule_title{if isset($moduleTitleClass)} {$moduleTitleClass}{/if}" {if isset($moduleTitleAttributes)} {$moduleTitleAttributes}{/if}>{$moduleTitle}</h2>{/if}
		<div class="subcontentmodule_content{if isset($moduleContentClass)} {$moduleContentClass}{/if}">{if isset($moduleContent)} {$moduleContent}{/if}</div>
	</div>
</section>
