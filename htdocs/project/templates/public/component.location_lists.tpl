{$mode = $element->getLocationMode()}
{if $mode == 'author'}
    <h2>{translations name="location.authors"}</h2>
    {include file=$theme->template("component.authorstable.tpl") authorsList=$element->getAuthorsList()}
{elseif $mode == 'group'}
    <h2>{translations name="location.groups"}</h2>
    {include file=$theme->template("component.groupstable.tpl") groupsList=$element->getGroupsList()}
{elseif $mode == 'party'}
    <h2>{translations name="location.parties"}</h2>
    {include file=$theme->template("component.partiestable.tpl") partiesList=$element->getPartiesList()}
{/if}