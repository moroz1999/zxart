{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{include file=$theme->template("component.authorinfo.tpl")}<br>
{include file=$theme->template('author.pictures.tpl')}<br>
{include file=$theme->template('author.music.tpl')}<br>
{include file=$theme->template('author.zxProds.tpl')}<br>
{include file=$theme->template('author.zxReleases.tpl')}<br>

{include $theme->template('component.comments.tpl')}