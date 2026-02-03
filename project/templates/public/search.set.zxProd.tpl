<h2 class='search_results_group_title'>{translations name=$translationCode}</h2>
<div class="search_results_group_zxprods">
    <script>
        window.elementsData = window.elementsData ? window.elementsData : {};
        window.elementsData[{$element->id}] = {$set->getJsonData()};
    </script>
    <zx-prods-list element-id="{$element->id}" property="prods"></zx-prods-list>
</div>
