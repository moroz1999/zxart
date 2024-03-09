<h2 class='search_results_group_title'>{translations name=$translationCode}</h2>
<div id="gallery_{$element->id}">
    {include file=$theme->template('component.pictureslist.tpl') pictures=$set->elements pager=false}
</div>