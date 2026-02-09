{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{assign var='formData' value=$element->getFormData()}
	{assign var='formErrors' value=$element->getFormErrors()}
	{assign var='formNames' value=$element->getFormNames()}

	<a class="detailedsearch_apilink button" href="{$element->getApiUrl()}">{translations name='detailedsearch.apilink'}</a>
	<a class="detailedsearch_save button" href="{$element->getSaveUrl()}">{translations name='detailedsearch.save'}</a>
	<form action="{$element->URL}" class='detailedsearch_form' method="post" enctype="multipart/form-data">
		<div class="detailedsearch_center">
			<div class="detailedsearch_left">
				<table class='form_table'>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.title'}:
						</td>
						
						<td class='form_field'>
							<input class='detailedsearch_title input_component detailedsearch_input' name='{$formNames.titleWord}' type='text' value="{$formData.titleWord}"/>
						</td>
					</tr>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.startyear'}:
						</td>
						
						<td class='form_field'>
							<input class='detailedsearch_startyear input_component detailedsearch_input' name='{$formNames.startYear}' type='text' value="{$formData.startYear}"/>
						</td>
					</tr>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.endyear'}:
						</td>
						
						<td class='form_field'>
							<input class='detailedsearch_endyear input_component detailedsearch_input' name='{$formNames.endYear}' type='text' value="{$formData.endYear}"/>
						</td>
					</tr>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.rating'}:
						</td>
						
						<td class='form_field'>
								<input class='detailedsearch_rating input_component detailedsearch_input' name='{$formNames.rating}' type='text' value="{$formData.rating}"/>
						</td>
					</tr>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.partyplace'}:
						</td>
						
						<td class='form_field'>
								<input class='detailedsearch_partyplace input_component detailedsearch_input' name='{$formNames.partyPlace}' type='text' value="{$formData.partyPlace}"/>
						</td>
					</tr>
					{if $element->items == 'graphics'}
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.type'}:
						</td>
						
						<td class='form_field'>
							<select class='detailedsearch_picturetype dropdown_placeholder' name='{$formNames.pictureType}'>
								<option value="" {if $formData.pictureType === ''}selected="selected"{/if}>{translations name='detailedsearch.alltypes'}</option>
								{foreach $element->getZxPictureTypes() as $type=>$translation}
									<option value="{$type}" {if $formData.pictureType === $type}selected="selected"{/if}>
										{translations name=$element->getZxPictureTypeTranslation($type)}
									</option>
								{/foreach}
							</select>
						</td>
					</tr>
					{elseif $element->items == 'music'}
						<tr>
							<td class='form_label'>
								{translations name='detailedsearch.format'}:
							</td>
							
							<td class='form_field'>
								<select class="dropdown_placeholder detailedsearch_format" name="{$formNames.format}" >
									<option value=''> </option>
									{foreach $element->getMusicFormats() as $format}
										<option value='{$format}' {if $formData.format==$format}selected='selected'{/if}>{$format}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class='form_label'>
								{translations name='detailedsearch.formatgroup'}:
							</td>
							
							<td class='form_field'>
								<select class="dropdown_placeholder detailedsearch_formatgroup" name="{$formNames.formatGroup}" >
									<option value=''> </option>
									<option value='ay' {if $formData.formatGroup=='ay'}selected='selected'{/if}>AY/YM</option>
									<option value='beeper' {if $formData.formatGroup=='beeper'}selected='selected'{/if}>Beeper</option>
									<option value='digitalbeeper' {if $formData.formatGroup=='digitalbeeper'}selected='selected'{/if}>Digital Beeper</option>
									<option value='beeperdigitalbeeper' {if $formData.formatGroup=='beeperdigitalbeeper'}selected='selected'{/if}>Beeper + Digital Beeper</option>
									<option value='digitalay' {if $formData.formatGroup=='digitalay'}selected='selected'{/if}>Digital AY, Covox, SD</option>
									<option value='ts' {if $formData.formatGroup=='ts'}selected='selected'{/if}>Turbo Sound</option>
									<option value='fm' {if $formData.formatGroup=='fm'}selected='selected'{/if}>FM</option>
									<option value='tsfm' {if $formData.formatGroup=='tsfm'}selected='selected'{/if}>Turbo Sound FM</option>
									<option value='aybeeper' {if $formData.formatGroup=='aybeeper'}selected='selected'{/if}>AY/YM + Beeper</option>
									<option value='aydigitalay' {if $formData.formatGroup=='aydigitalay'}selected='selected'{/if}>AY/YM + Digital AY</option>
									<option value='aycovox' {if $formData.formatGroup=='aycovox'}selected='selected'{/if}>AY/YM + Covox</option>
									<option value='saa' {if $formData.formatGroup=='saa'}selected='selected'{/if}>SAA</option>
								</select>
							</td>
						</tr>
					{/if}
					{stripdomspaces}
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.sortby'}:
						</td>
						
						<td class='form_field'>
							<select class='dropdown_placeholder detailedsearch_sortparameter' name='{$formNames.sortParameter}'>
								<option value="year" {if $formData.sortParameter == 'year'}selected="selected"{/if}>{translations name='detailedsearch.sort_year'}</option>
								<option value="title" {if $formData.sortParameter == 'title'}selected="selected"{/if}>{translations name='detailedsearch.sort_title'}</option>
								<option value="place" {if $formData.sortParameter == 'place'}selected="selected"{/if}>{translations name='detailedsearch.sort_place'}</option>
								<option value="date" {if $formData.sortParameter == 'date'}selected="selected"{/if}>{translations name='detailedsearch.sort_dateadded'}</option>
								<option value="votes" {if $formData.sortParameter == 'votes'}selected="selected"{/if}>{translations name='detailedsearch.sort_rating'}</option>
								<option value="commentsAmount" {if $formData.sortParameter == 'commentsAmount'}selected="selected"{/if}>{translations name='detailedsearch.sort_commentsamount'}</option>
								{if $element->items == 'graphics'}
									<option value="views" {if $formData.sortParameter == 'views'}selected="selected"{/if}>{translations name='detailedsearch.sort_views'}</option>
								{elseif $element->items == 'music'}
									<option value="plays" {if $formData.sortParameter == 'plays'}selected="selected"{/if}>{translations name='detailedsearch.sort_plays'}</option>
								{/if}
							</select> <select class='dropdown_placeholder detailedsearch_sortorder' name='{$formNames.sortOrder}'>
								<option value="asc" {if $formData.sortOrder == 'asc'}selected="selected"{/if}>{translations name='detailedsearch.sortasc'}</option>
								<option value="desc" {if $formData.sortOrder == 'desc'}selected="selected"{/if}>{translations name='detailedsearch.sortdesc'}</option>
								<option value="rand" {if $formData.sortOrder == 'rand'}selected="selected"{/if}>{translations name='detailedsearch.sortrand'}</option>
							</select>
						</td>
					</tr>
					{/stripdomspaces}
					{if $element->items == 'graphics'}
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.realtime'}:
						</td>
						
						<td class='form_field'>
							<input class="detailedsearch_realtime checkbox_placeholder" type='checkbox' {if $formData.realtime == '1'}checked="checked"{/if} name="{$formNames.realtime}"/>
						</td>
					</tr>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.inspiration'}:
						</td>
						
						<td class='form_field'>
							<input class="detailedsearch_inspiration checkbox_placeholder" type='checkbox' {if $formData.inspiration == '1'}checked="checked"{/if} name="{$formNames.inspiration}"/>
						</td>
					</tr>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.stages'}:
						</td>
						
						<td class='form_field'>
							<input class="detailedsearch_stages checkbox_placeholder" type='checkbox' {if $formData.stages == '1'}checked="checked"{/if} name="{$formNames.stages}"/>
						</td>
					</tr>
					{/if}
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.tagsinclude'}:
						</td>
						
						<td class='form_field'>
								<input class='detailedsearch_tagsinclude input_component detailedsearch_input' name='{$formNames.tagsInclude}' type='text' value="{$formData.tagsInclude}"/>
						</td>
					</tr>
					<tr>
						<td class='form_label'>
							{translations name='detailedsearch.tagsexclude'}:
						</td>
						
						<td class='form_field'>
								<input class='detailedsearch_tagsexclude input_component detailedsearch_input' name='{$formNames.tagsExclude}' type='text' value="{$formData.tagsExclude}"/>
						</td>
					</tr>
				</table>
			</div>
			<div class="detailedsearch_right">
			<table class="form_table">
				<tr>
					<td class='form_label'>
						{translations name='detailedsearch.author_country'}:
					</td>
					
					<td class='form_field'>
						<select class="detailedsearch_author_country" name="{$formNames.authorCountry}[]" multiple="multiple" autocomplete='off'>
							{assign var="countryElement" value=$element->getCountryElement()}
							{if $countryElement}
								<option value='{$countryElement->id}' selected="selected">
									{$countryElement->title}
								</option>
							{/if}
						</select>
					</td>
				</tr>
				<tr>
					<td class='form_label'>
						{translations name='detailedsearch.author_city'}:
					</td>
					
					<td class='form_field'>
						<select class="detailedsearch_author_city" name="{$formNames.authorCity}[]" multiple="multiple" autocomplete='off'>
							{assign var="cityElement" value=$element->getCityElement()}
							{if $cityElement}
								<option value='{$cityElement->id}' selected="selected">
									{$cityElement->title}
								</option>
							{/if}
						</select>
					</td>
				</tr>
				<tr>
					<td class='form_label'>
						{translations name='detailedsearch.results_type'}:
					</td>
					
					<td class='form_field'>
						<select class="detailedsearch_resultstype dropdown_placeholder" name="{$formNames.resultsType}" autocomplete='off'>
							<option value="zxitem" {if $formData.resultsType == 'zxitem'}selected="selected"{/if}>{translations name='detailedsearch.resultstype_zxitem'}</option>
							<option value="author" {if $formData.resultsType == 'author'}selected="selected"{/if}>{translations name='detailedsearch.resultstype_author'}</option>
						</select>
					</td>
				</tr>
			</table>
		</div>
		</div>
		<div class='form_controls detailedsearch_controls'>
			<button type="reset" class='detailedsearch_reset button'>{translations name='detailedsearch.reset'}</button>
			<button type="submit" class='detailedsearch_button button'>{translations name='detailedsearch.perform'}</button>
			<input type="hidden" value="{$element->id}" name="id"/>
			<input type="hidden" value="perform" name="action"/>
		</div>
	</form>
	<h2>{translations name='detailedsearch.results'} {$element->getStartElementNumber()}-{$element->getEndElementNumber()} ({$element->getTotalAmount()})</h2>
	{if $element->getResultsType() == 'zxPicture'}
		<div id="gallery_{$element->id}">
			{include file=$theme->template('component.pictureslist.tpl') pictures=$element->getResultsList() pager=$element->getPager() }
		</div>
{*		{include file=$theme->template("component.picturestable.tpl") picturesList=$element->getResultsList() element=$element pager=$element->getPager() number=$element->getStartElementNumber()}*}
	{elseif $element->getResultsType() == 'zxMusic'}
		{include file=$theme->template("component.musictable.tpl") musicList=$element->getResultsList() element=$element pager=$element->getPager() number=$element->getStartElementNumber() musicListId="detailedsearch_music_{$element->id}"}
	{elseif $element->getResultsType() == 'author'}
		{include file=$theme->template("component.authorstable.tpl") authorsList=$element->getResultsList() element=$element pager=$element->getPager() number=$element->getStartElementNumber()}
	{/if}
{/capture}
{assign moduleClass "detailedsearch_block"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}
