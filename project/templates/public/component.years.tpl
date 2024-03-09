{if isset($yearsInfo)}
    <div class='years_selector'>
        {foreach from=$yearsInfo item=year}<a class='years_selector_item' href="{$year.url}">{$year.title}</a>{/foreach}
    </div>
{/if}