{if $facebookSocialPlugin = $socialDataManager->getSocialPluginByName('facebook')}
    {if $facebookAppId = $facebookSocialPlugin->getSpecialDataByKey('appId')}
        <meta property="fb:app_id" content="{$facebookAppId}"/>
    {/if}
{/if}