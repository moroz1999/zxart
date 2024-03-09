<div class="author_details_photo">
    {if $element->originalName != ""}
        <img loading="lazy" class="author_details_photo_image" src='{$controller->baseURL}image/type:authorPhoto/id:{$element->image}/filename:{$element->originalName}' alt="{$element->title}" />
    {else}
        <img loading="lazy" class="author_details_photo_image" src='{$theme->getImageUrl('author.png')}' alt="" />
    {/if}
</div>