{if !isset($userType)}{$userType = ''}{/if}
{if !isset($userUrl)}{$userUrl = ''}{/if}
<span class="user-name {if $userType}{$userType}{/if}">{if $userUrl}<a href="{$userUrl}">{/if}{$userName}{if $userUrl}</a>{/if}{if $userType}<span class="user-icon"></span>{/if}</span>