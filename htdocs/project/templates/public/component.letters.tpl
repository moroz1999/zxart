{if isset($lettersInfo)}
    <div class='letters_selector'>
        {foreach from=$lettersInfo item=letter}<a class='letters_selector_item' href="{$letter.url}">{$letter.title}</a>{/foreach}
    </div>
{/if}