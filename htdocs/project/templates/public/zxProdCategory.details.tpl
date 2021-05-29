<div class="editing_controls">
    {if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
        <a class="button"
           href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='zxProdCategory.upload'}</a>
    {/if}
</div>
<script>
    window.elementsData = window.elementsData ? window.elementsData : { };
    window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
</script>
<app-zx-prods-category element-id="{$element->id}"></app-zx-prods-category>