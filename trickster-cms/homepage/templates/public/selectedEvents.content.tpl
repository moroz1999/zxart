{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{include file=$theme->template("component.eventslist.tpl")}
	{if ($element->getConnectedEventsLists() || $element->getConnectedEvents()) && $element->getFixedElementURL()}
		<div class="button_link_container calendar_link_container text_center">
			<a href="{$element->getFixedElementURL()}" class="button_link bordered"><span class="button_link_text">{$element->gotoButtonTitle()}</span></a>
		</div>
	{/if}
{/capture}

{assign colorLayoutStyle ''}
{if $element->getCurrentLayout('colorLayout')}
	{$colorLayoutStyle = "bg_color_{$element->getCurrentLayout('colorLayout')}"}
	{assign moduleAttributes "data-color='colorlayout_bg_color'"}
{/if}

{assign moduleClass "selectedevents selectedevents_$currentLayout $colorLayoutStyle"}
{assign moduleTitleClass "selectedevents_title selectedevents_title_$currentLayout"}
{assign moduleContentClass "selectedevents_content"}

{include file=$theme->template("component.contentmodule.tpl")}