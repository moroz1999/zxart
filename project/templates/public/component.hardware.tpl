<div class="hardware_items">
{foreach $element->getHardwareMap() as $type => $hardwareItems}
    {foreach $hardwareItems as $hardwareItem}
        <div class="hardware_item">
            <a href="{$element->getCatalogueUrl(['hw' => $hardwareItem])}">{$element->getIconByHwType($type)} {translations name="hardware.item_{$hardwareItem}"}</a></div>
    {/foreach}
{/foreach}
</div>