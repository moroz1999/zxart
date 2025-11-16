<div class="emulator">
    <canvas class="emulator_canvas" id="canvas" tabindex="0"></canvas>
    <div class="emulator_status"></div>
    {$emulatorType = $element->getEmulatorType()}
    {if $emulatorType === 'samcoupe'}
        <p class="emulator_notes">{translations name="emulator.samcoupe"}</p>
    {/if}
    <div class="emulator_controls">
        <button class="button emulator_fullscreen">{translations name="emulator.fullscreen"}</button>
        {if !empty($currentElementPrivileges.uploadScreenshot)}
            <select class="emulator_type dropdown_block">
                <option value="48">48</option>
                <option value="128">128</option>
                <option value="giga">giga</option>
            </select>
        {/if}
    </div>
</div>