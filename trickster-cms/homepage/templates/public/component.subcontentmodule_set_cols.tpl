{stripdomspaces}
<{if isset($moduleTag)}{$moduleTag}{else}section{/if}
        class="subcontentmodule_component subcontentmodule_cols{if isset($colsOnRow)} cols_{$colsOnRow}{/if}
        {if isset($moduleClass)} {$moduleClass}{/if}"
        {if isset($moduleAttributes)}{$moduleAttributes}{/if}>
    {if isset($moduleTitle) && $moduleTitle !=""}
        <h2 class="subcontentmodule_title{if isset($moduleTitleClass)} {$moduleTitleClass}{/if}"
                {if isset($moduleTitleAttributes)} {$moduleTitleAttributes}{/if}>
            {$moduleTitle}
        </h2>
    {/if}
    <div class="subcontentmodule_content{if isset($moduleContentClass)} {$moduleContentClass}{/if}">
        {if isset($moduleContent)} {$moduleContent}{/if}
    </div>
    <div class="subcontentmodule_image{if isset($moduleImageBlockClass)} {$moduleImageBlockClass}{/if}">
        {if isset($moduleImageBlock)} {$moduleImageBlock}{/if}
    </div>

</{if isset($moduleTag)}{$moduleTag}{else}section{/if}>
{/stripdomspaces}