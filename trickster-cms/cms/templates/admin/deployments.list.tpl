{if $element->getError()}
	{include file=$theme->template('block.error.tpl') message=$element->getError()}
{/if}
{if $list}
	<div class="deployments_list">
		<div class="panel_component">
			<div class="panel_content">
				<table class='table_component deployments_table content_list_deployments'>
					<thead>
					<tr>
						<th class="type_column">
							{translations name='deployments.list_column_type'}
						</th>
						<th class="version_column">
							{translations name='deployments.list_column_version'}
						</th>
						<th class="description_column">
							{translations name='deployments.list_column_description'}
						</th>
					</tr>
					</thead>
					<tbody>
					{foreach $list as $deployment}
						<tr class="deployments_table_item">
							<td class="type_column">
								{$deployment.type}
							</td>
							<td class="version_column">
								{$deployment.version}
							</td>
							<td class="description_column">
								{if !empty($deployment.description)}
									{$deployment.description}
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/if}
