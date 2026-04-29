{if !empty($item.layouts)}
    <div class="form_table_layout form_table_layout_{$fieldName}">
        <div class="layout_header">
            <h1>
                {if isset($headingTitle)}
                    {$headingTitle}
                {else}
                    {translations name="layout.{$fieldName}"}
                {/if}
            </h1>
        </div>
        <div class="layout_content">
            {foreach $item.layouts as $layout}
                <div class="form_table_layout_item">
                    <label class="form_table_layout_item_label">
                        {if $fieldName == 'colorLayout'}
                            <span class="form_table_layout_item_bg" style="background-color:{if $layout != 'none'}#{$layout}" {else}transparent" data-bg="none_bg"{/if}></span>
                        {else}
                        {$imageUrl = $theme->getImageUrl("{$structureType}_layout_{$layout}.png", false, false)}
                        {if !$imageUrl}{$imageUrl = $theme->getImageUrl("layout_default.png", false, false)}{/if}
                        {if $imageUrl}<img class="form_table_layout_item_image" src="{$imageUrl}"/>{/if}
                        {/if}
                        <div class="form_table_layout_item_text">{if $fieldName == 'colorLayout' && $layout != 'none'}<small>#{$layout|lower}</small>{else}{translations name="{$translationGroup}.layout_{$layout}"}{/if}</div>
                        <input type="radio" class="radio_holder" name="{$formNames.$fieldName}"
                               value="{$layout}"{if $formData.$fieldName == $layout || !$formData.$fieldName && $layout==$item.defaultLayout} checked="checked"{/if}
                               id="form_table_layout_item_input_short">
                    </label>
                </div>
            {/foreach}
        </div>
    </div>
{/if}