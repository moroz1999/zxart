{if !empty($contentOnly)}{if !empty($moduleContent)}
	<div class="contentmodule_content{if !empty($moduleContentClass)} {$moduleContentClass}{/if}" {if isset($moduleAttributes)}{$moduleAttributes}{/if}>{$moduleContent}</div>{/if}{else}
	<div class="columnmodule_component{if !empty($moduleClass)} {$moduleClass}{/if}" {if isset($moduleAttributes)}{$moduleAttributes}{/if}>
	{if !empty($moduleTitle)}
		<div class="columnmodule_title{if isset($moduleTitleClass)} {$moduleTitleClass}{/if}">{$moduleTitle}</div>{/if}
	{if !empty($moduleContent)}
		<div class="columnmodule_content{if isset($moduleContentClass)} {$moduleContentClass}{/if}">{$moduleContent}</div>{/if}
	</div>
{/if}