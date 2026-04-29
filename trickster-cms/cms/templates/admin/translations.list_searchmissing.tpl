<div class="content_list_block">
	<div class="controls_block">
		<a href="{$element->URL}id:{$element->id}/action:createMissing/" class="button button success_button">{translations name='button.create'}</a>
		<div class="clearfix"></div>
	</div>
	<table class='content_list'>
		<thead>
			<tr>
				<th class="">
					{translations name='label.name'}
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$element->searchMissingTranslations() item=translationCode}
				<tr class="content_list_item">
					<td>
						{$translationCode}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

</div>
