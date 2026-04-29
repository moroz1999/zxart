{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
{$selectedLogFile = $element->getSelectedLogFileName()}

<form action="{$element->getFormActionURL()}" class="form_component log_viewer_form" method="get" enctype="multipart/form-data">
	<div class="form_fields">
		<div class="form_items">
			{if $element->debugError}
				<div class="form_label">{$element->debugError}</div>
				<div class="form_field"></div>
			{elseif $element->apiError}
				<div class="form_label"><strong>
						{translations name='log_viewer.api_error'}:
					</strong>
					<p>{$element->apiError}</p></div>
				<div class="form_field"></div>
			{/if}
		</div>
		<div class="form_items">
			<div class="form_label">{translations name='log_viewer.log'}</div>
			<div class="form_field">
				<select class="dropdown_placeholder requests_debugger_api_select" name="log">
					{foreach $element->getLogsList()|@array_reverse as $logFile}
						{$fileName = $logFile->getBasename()}
						<option value='{$fileName}'{if $fileName == $selectedLogFile} selected="selected"{/if}>
							{$fileName} ({number_format($logFile->getSize() / 1024, 2)} KB)
						</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="form_items">
			<div class="form_label">

			</div>
			<div class="form_field">
				<input class="button success_button" type="submit" value='{translations name='log_viewer.view'}' />
			</div>
		</div>
	</div>
</form>

{if $selectedLogFile}
	<div class="log_viewer_summary">
		<div class="panel_component">
			<div class="panel_heading">{$selectedLogFile}</div>
			<div class="panel_content">
				<table class="table_component">
					<thead>
					<tr>
						<th class="log_viewer_summary_error_cell">{translations name='logViewer.error_text'}</th>
						<th class="log_viewer_summary_url_cell">{translations name='logViewer.error_url'}</th>
						<th class="log_viewer_summary_count_cell">{translations name='logViewer.error_count'}</th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$element->getSelectedLogErrors() item=entry}
						<tr>
							<td>{$entry.error}</td>
							<td>{$entry.uri}</td>
							<td>{$entry.count}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="log_viewer_contents_panel">
		<div class="log_viewer_contents_panel_title">{translations name='logViewer.raw_text'}</div>
		<div class="log_viewer_contents_panel_data_wrap">
			<pre class="log_viewer_contents_panel_data">{$element->getSelectedLogContents()|escape|replace:"\n":'<br/>'}</pre>
		</div>
	</div>
{/if}
