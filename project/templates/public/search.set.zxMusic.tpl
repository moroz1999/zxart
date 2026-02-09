<h2 class='search_results_group_title'>{translations name=$translationCode}</h2>
{include file=$theme->template("component.musictable.tpl") musicList=$set->elements pager=false musicListId="searchset_music_{$translationCode|replace:'.':'_'}"}
