{$supportElement = $structureManager->getElementByMarker('support', $currentLanguage->id)}
{$userElement = $element->getUserElement()}
{if $userElement && $supportElement}
    {if $userBadges = $userElement->getBadgetTypes()}
        <div class="user-badges">
            {foreach $userBadges as $badge}
                <a class="user-badge {$badge}" href="{$supportElement->getUrl()}">
                    <span class="user-badge-image"></span>
                    <span class="user-badge-title">{translations name="author.badge_$badge"}</span>
                </a>
            {/foreach}
        </div>
    {/if}
{/if}