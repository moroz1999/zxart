{if isset($dashboard)}
	<div class="dashboard_container">
	{if $chartData = $dashboard->getTotalOrdersByDay()}
			<div class="panel_component">
				<div class="panel_heading">{translations name='dashboard.orders_statistics'}</div>
				<div class="panel_content">
					<canvas class="chart_component" data-chartid="1"></canvas>
					<script>
						chartsData = {$chartData};
					</script>
				</div>
			{if $orders = $dashboard->getOrdersElement()}
				<div class="panel_controls controls_block">
					<a href="{$orders->URL}" class="button primary_button dashboard_button">{translations name='dashboard.view_others'}</a>
				</div>
			{/if}
			</div>{/if}
	{if $nonSentOrders=$dashboard->getNonSentOrders()}
	<div class="panel_component">
		<div class="panel_heading">{translations name='dashboard.last_non_sent_orders'}</div>
		<div class="panel_content dashboard_table_container">
			<table class="dashboard_table table_component">
				<thead>
				<tr>
					<th>{translations name='dashboard.date'}</th>
					<th>{translations name='dashboard.payerName'}</th>
					<th>{translations name='dashboard.status'}</th>
					<th>{translations name='dashboard.edit'}</th>
				</tr>
				</thead>
				<tbody>
				{foreach from=$nonSentOrders item=entry}
					<tr>
						<td>{$entry->dateCreated}</td>
						<td>{$entry->getPayerName()}</td>
						<td>{$entry->getOrderStatus()}</td>
						<td><a href="{$entry->URL}">{$entry->getTitle()}</a></td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>

		{if $orders = $dashboard->getOrdersElement()}
			<div class="panel_controls controls_block">
				<a href="{$orders->URL}" class="button primary_button dashboard_button">{translations name='dashboard.view_others'}</a>
			</div>
		{/if}
	</div>{/if}
		<div class="panel_component">
		<div class="panel_heading">{translations name='dashboard.last_registered'}</div>
		<div class="panel_content dashboard_table_container">
			<table class="dashboard_table table_component">
				<thead>
				<tr>
					<th>{translations name='dashboard.user'}</th>
					<th>{translations name='dashboard.email'}</th>
					<th>{translations name='dashboard.edit'}</th>
				</tr>
				</thead>
				<tbody>
				{foreach from=$dashboard->getLatestUsers() item=entry}
					{if $entry}
						<tr>
							<td>{$entry->firstName} {$entry->lastName}</td>
							<td>{$entry->email}</td>
							<td><a href="{$entry->URL}">{$entry->getTitle()}</a></td>
						</tr>
					{/if}
				{/foreach}
				</tbody>
			</table>
		</div>

		{if $users = $dashboard->getUsersElement()}
			<div class="panel_controls controls_block">
				<a href="{$users->URL}" class="button primary_button dashboard_button">{translations name='dashboard.view_others'}</a>
			</div>
		{/if}
		</div>
		<div class="panel_component">
			<div class="panel_heading">{translations name='dashboard.last_404_errors'}</div>
			<div class="panel_content dashboard_table_container">
				<table class="dashboard_table table_component">
					<thead>
					<tr>
						<th>{translations name='dashboard.date'}</th>
						<th>{translations name='dashboard.error'}</th>
						<th>{translations name='dashboard.count'}</th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$dashboard->getLatest404() item=entry}
						<tr>
							<td>{$entry.dateFormatted}</td>
							<td>{$entry.errorUrl}</td>
							<td>{$entry.count}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>

			{if $notFoundLog = $dashboard->getNotFoundLogElement()}
				<div class="panel_controls controls_block">
					<a href="{$notFoundLog->URL}" class="button primary_button dashboard_button">{translations name='dashboard.view_others'}</a>
				</div>
			{/if}
		</div>
		<div class="panel_component">
			<div class="panel_heading">{translations name='dashboard.last_errors'}</div>
			<div class="panel_content dashboard_table_container">
				<table class="dashboard_table table_component last_errors">
					<thead>
					<tr>
						<th>{translations name='dashboard.error_text'}</th>
						<th>{translations name='dashboard.error_url'}</th>
						<th>{translations name='dashboard.error_count'}</th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$dashboard->getLatestTopErrors() item=entry}
						<tr>
							<td>{$entry.error}</td>
							<td>{$entry.uri}</td>
							<td>{$entry.count}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>

			{if $logViewer = $dashboard->getLogViewer()}
				<div class="panel_controls controls_block">
					<a class="button primary_button dashboard_button" href="{$logViewer->getUrl()}">{translations name='dashboard.view_others'}</a>
				</div>
			{/if}
		</div>
		<div class="panel_component">
			<div class="panel_heading">{translations name='dashboard.last_actions_logs'}</div>
			<div class="panel_content dashboard_table_container">
				<table class="dashboard_table table_component">
					<thead>
					<tr>
						<th>{translations name='dashboard.date'}</th>
						<th>{translations name='dashboard.type'}</th>
						<th>{translations name='dashboard.action'}</th>
						<th>{translations name='dashboard.user'}</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$dashboard->getLatestActionsLogs() item=entry}
						<tr>
							<td>{$entry.date}</td>
							<td>{$entry.elementType}</td>
							<td>{$entry.action}</td>
							<td>{$entry.userName}</td>
							<td>
								{if $entry.URL}
									<a href="{$entry.URL}">{$entry.elementName}</a>
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>

			{if $actionsLog = $dashboard->getActionsLogElement()}
				<div class="panel_controls controls_block">
					<a href="{$actionsLog->URL}" class="button primary_button dashboard_button">{translations name='dashboard.view_others'}</a>
				</div>
			{/if}
		</div>
		<div class="panel_component">
			<div class="panel_heading">{translations name='dashboard.last_elements'}</div>
			<div class="panel_content dashboard_table_container">
				<table class="dashboard_table table_component">
					<thead>
					<tr>
						<th>{translations name='dashboard.date'}</th>
						<th>{translations name='dashboard.type'}</th>
						<th>{translations name='dashboard.edit'}</th>
					</tr>
					</thead>
					<tbody>
					{foreach from=$dashboard->getLatestAddedElements() item=entry}
						<tr>
							<td>{$entry->dateCreated}</td>
							<td>{$entry->structureType}</td>
							<td><a href="{$entry->URL}">{$entry->getTitle()}</a></td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>

		</div>
	</div>
{/if}