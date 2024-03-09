<div class="location_controls editing_controls">
    {if $element->getAmountInLocation('author')}
        <a class="button" href="{$element->URL}show:author/">{translations name="location.authors"}</a>
    {/if}
    {if $element->getAmountInLocation('group')}
        <a class="button" href="{$element->URL}show:group/">{translations name="location.groups"}</a>
    {/if}
    {if $element->getAmountInLocation('party')}
        <a class="button" href="{$element->URL}show:party/">{translations name="location.parties"}</a>
    {/if}
</div>