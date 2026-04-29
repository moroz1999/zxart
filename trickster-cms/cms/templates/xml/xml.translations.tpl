<?xml version="1.0" encoding="UTF-8"?>
<deployment>
    <version></version>
    <type></type>
    <requiredVersions>
        <requiredVersion>
            <version></version>
            <type></type>
        </requiredVersion>
    </requiredVersions>
    <description></description>
    <procedures>
        {foreach from=$exportData item=translation}
            <AddTranslation>
                <type>{$translation->structureType}</type>
                <code>{$translation->parentTitle}.{$translation->structureName}</code>
                <valueType>{$translation->valueType}</valueType>
                <values>
                    {foreach $translation->getAllDataChunks() as $languageId => $chunk}
                        <value languageCode="{$languagesList.$languageId}">{if $translation->valueType == 'text'}{$chunk.valueText->getDisplayValue()}{elseif $translation->valueType == 'textarea'}{$chunk.valueTextarea->getDisplayValue()}{elseif $translation->valueType == 'html'}{$chunk.valueHtml->getDisplayValue()}{/if}</value>
                    {/foreach}
                </values>
            </AddTranslation>
        {/foreach}
        <GenerateTranslations>
            <type>{$translationsType}</type>
        </GenerateTranslations>
    </procedures>
</deployment>
