<div class="subarticle spoiler_component spoiler_hidden">
    <div class="subarticle_title spoiler_component_title">{$element->title}</div>
    <div class="subarticle_content_wrapper spoiler_component_content_wrapper">
        <div class='subarticle_content html_content spoiler_component_content'>
            {if $element->originalName}
                {include file=$theme->template('component.elementimage.tpl') type='subArticleShortImage' class='subarticle_simple_image' lazy=false}
            {/if}
            <div>
                {$element->content}
            </div>
        </div>
    </div>
</div>