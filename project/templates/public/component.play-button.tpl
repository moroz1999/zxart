{if $element->getEmulatorType() === 'usp'}
    <button
            class="button"
            onclick="emulatorComponent.start('{$element->getFileUrl('play')|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
{elseif $element->getEmulatorType() === 'zx81'}
    <button
            class="button"
            onclick="zx81EmulatorComponent.start('{$element->getFileUrl('play')|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
{/if}