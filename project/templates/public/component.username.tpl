{if !isset($userType)}{$userType = ''}{/if}
{if !isset($userClass)}{$userClass = ''}{/if}
{if !isset($userUrl)}{$userUrl = ''}{/if}
<span class="user-name {$userType} {$userClass}">{if $userType}<span class="user-icon"></span>{/if}{if $userUrl}<a href="{$userUrl}">{/if}{$userName}{if $userUrl}</a>{/if}</span>