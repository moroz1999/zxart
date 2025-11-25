{$emulatorType = $element->getEmulatorType()}
{if $emulatorType === 'usp'}
    <button
            class="button"
            onclick="emulatorComponent.start('{$element->getPlayUrl()|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
{elseif $emulatorType === 'zx80' || $emulatorType === 'zx81'}
    <button
            class="button"
            onclick="zx81EmulatorComponent.start('{$element->getPlayUrl()|escape:'quotes'}', '{$emulatorType}')"
    >{translations name="zxrelease.play"}</button>
{elseif $emulatorType === 'tsconf'}
    <button
            class="button"
            onclick="tsconfEmulatorComponent.start('{$element->getPlayUrl(false)|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
    <script type="text/javascript" src="/libs/mame/es6-promise.js"></script>
    <script type="text/javascript" src="/libs/mame/browserfs.min.js"></script>
{elseif $emulatorType === 'samcoupe'}
    <button
            class="button"
            onclick="samcoupeEmulatorComponent.start('{$element->getPlayUrl(false)|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
    <script type="text/javascript" src="/libs/mamenextsam/es6-promise.js"></script>
    <script type="text/javascript" src="/libs/mamenextsam/browserfs.min.js"></script>
{elseif $emulatorType === 'zxnext'}
    <button
            class="button"
            onclick="zxnextEmulatorComponent.start('{$element->getPlayUrl(false)|escape:'quotes'}')"
    >{translations name="zxrelease.play"}</button>
    <script type="text/javascript" src="/libs/mamenextsam/es6-promise.js"></script>
    <script type="text/javascript" src="/libs/mamenextsam/browserfs.min.js"></script>
{/if}