{*{assign var="translationCode" value="$structureType.formhelper_$fieldName"}*}
{*{if isset($privileges.adminTranslations) && isset($privileges.adminTranslations.receive) && $privileges.adminTranslations.receive}*}
    {*{capture assign="translationText"}{translations name=$translationCode required=true loggable=false}{/capture}*}
{*{else}*}
    {*{capture assign="translationText"}{translations name=$translationCode required=false loggable=false}{/capture}*}
{*{/if}*}
{*{if $translationText}*}
    {*<div class="form_helper">*}
        {*<div class="form_helper_mark"></div>*}
        {*<div class="form_helper_tip">*}
            {*{$translationText}*}
        {*</div>*}
    {*</div>*}
{*{/if}*}