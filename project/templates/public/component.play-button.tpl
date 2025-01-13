{if $element->getEmulatorType() === 'usp'}
    <button
            class="button"
            onclick="emulatorComponent.start('{$element->getPlayUrl()|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
{elseif $element->getEmulatorType() === 'zx81'}
    <button
            class="button"
            onclick="zx81EmulatorComponent.start('{$element->getPlayUrl()|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
{elseif $element->getEmulatorType() === 'tsconf'}
    <button
            class="button"
            onclick="tsconfEmulatorComponent.start('{$element->getPlayUrl(false)|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
    {if $element->getEmulatorType() === 'tsconf'}
        <script type="text/javascript" src="/libs/mame/es6-promise.js"></script>
        <script type="text/javascript" src="/libs/mame/browserfs.min.js"></script>
    {/if}
{/if}