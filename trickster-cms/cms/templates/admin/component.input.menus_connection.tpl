{foreach $formRelativesInfo as $relative}
	<div class="form_items">
		<div class="form_label">
            {translations name='shared.formrelativesinput'} ({$languagesIndex[$relative@key]->title}):
		</div>
		<div class="form_field">
			<select class="{$item.class}" name="{$formNames.formRelativesInput}[{$relative@key}]" autocomplete="off">
				{if $relative}
					<option value='{$relative->id}' selected="selected">{$relative->title}</option>
				{/if}
			</select>
		</div>
	</div>
{/foreach}