{*    <div class="editing_controls">*}
{*    {if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}*}
{*        <a class="button" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='zxProdCategory.upload'}</a>*}
{*    {/if}*}
{*    </div>*}
<script>
    window.elementsData = window.elementsData? window.elementsData : { };
    window.elementsData[{$element->id}] = {$element->getJsonInfo('details')};
</script>
<app-zx-prods-list element-id="{$element->id}"></app-zx-prods-list>