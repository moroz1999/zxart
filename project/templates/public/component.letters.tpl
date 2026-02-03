{if isset($lettersInfo)}
    <div class='letters_selector'>
        {foreach from=$lettersInfo item=letter}<a class='button button_square{if $letter.selected} button_primary{else} button_transparent{/if}' href="{$letter.url}">{$letter.title}</a>{/foreach}
    </div>
{/if}
