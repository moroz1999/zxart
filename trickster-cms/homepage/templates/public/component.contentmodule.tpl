{if !empty($contentOnly)}{if isset($moduleContent)}
	<section id="{$element->structureName}" class="contentmodule_content{if isset($moduleContentClass)} {$moduleContentClass}{/if}" {if isset($moduleAttributes)}{$moduleAttributes}{/if}>{$moduleContent}</section>
{/if}{else}
	<section id="{$element->structureName}" class="contentmodule_component{if isset($moduleClass)} {$moduleClass}{/if}" {if isset($moduleAttributes)}{$moduleAttributes}{/if}>
		{if isset($moduleLeftSide)}
			<div class="inner_container{if isset($moduleInnerContainerClass)} {$moduleInnerContainerClass}{/if}">
				<div class="contentmodule_left_side{if isset($moduleLeftSideClass)} {$moduleLeftSideClass}{/if}"{if isset($moduleLeftSideClassAttributes)} {$moduleLeftSideClassAttributes}{/if}>{$moduleLeftSide}</div>
		{/if}
		{if isset($moduleTitle)}
			<h1 class="contentmodule_title{if isset($moduleTitleClass)} {$moduleTitleClass}{/if}"{if isset($moduleTitleAttributes)} {$moduleTitleAttributes}{/if}>{$moduleTitle}</h1>
		{/if}
		{if isset($moduleContent)}
			<div class="contentmodule_content{if isset($moduleContentClass)} {$moduleContentClass}{/if}">{$moduleContent}</div>
		{/if}
		{if isset($moduleLeftSide)}
			</div>
		{/if}
	</section>
{/if}