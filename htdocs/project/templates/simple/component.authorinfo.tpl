{if $groupsList = $element->getGroupsList()}
    <b>
        {translations name='field.group'}:
    </b>
    {foreach $groupsList as $groupElement}<a href="{$groupElement->getUrl()}">{$groupElement->title}</a>{if !$groupElement@last}, {/if}{/foreach}
    <br>
{/if}
{if $element->realName}
    <b>
        {translations name='field.realname'}:
    </b>
    {$element->realName}
    <br>
{/if}
{if $element->getCityTitle() || $element->getCountryTitle()}
    <b>
        {translations name='field.livinglocation'}:
    </b>
    {if $element->getCityTitle()}{$element->getCityTitle()}, {/if}
    {$element->getCountryTitle()}
    <br>
{/if}
{if $aliasElements = $element->getAliasElements()}
    <b>
        {translations name='field.othernicknames'}:
    </b>
    {foreach $aliasElements as $aliasElement}
        <a href="{$aliasElement->getUrl()}">{$aliasElement->title}</a>{if !$aliasElement@last}, {/if}
    {/foreach}
    <br>
{/if}
{include file=$theme->template('component.links.tpl')}
{if $element->displayInMusic}
    <b>
        {translations name='author.chiptype'}:
    </b>
    {$element->chipType}
    <br>
    <b>
        {translations name='author.channelstype'}:
    </b>
    {$element->channelsType}
    <br>
    <b>
        {translations name='author.frequency'}:
    </b>
    {$element->frequency}
    <br>
    <b>
        {translations name='author.intfrequency'}:
    </b>
    {$element->intFrequency}
    <br>
{/if}
{if $element->displayInGraphics}
    <b>
        {translations name='author.palette'}:
    </b>
    {$element->getPalette()}
    <br>
{/if}
{if $element->displayInMusic}
    <b>
        {translations name='author.rating_music'}:
    </b>
    {$element->musicRating}
    <br>
{/if}
{if $element->displayInGraphics}
    <b>
        {translations name='author.rating_graphics'}:
    </b>
    {$element->graphicsRating}
    <br>
{/if}
{$userElement = $element->getUserElement()}
{if $userElement}
    <b>
        {translations name='author.connecteduser'}:
    </b>
    {$userElement->userName}
    <br>
{/if}