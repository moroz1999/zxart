<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title></title>
		<meta name="robots" content="noindex, nofollow"/>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link rel="stylesheet" type="text/css" href="{$controller->baseURL}css/set:{$theme->getCode()}/file:index.css"/>
	</head>
	<body>
		<div class="menu">
			<a href="{$controller->baseURL}deploy/">Deployments</a>
			 |
			<a href="{$controller->baseURL}deploy/new">New</a>
		</div>
		{if !empty($resultMessage)}
			<div class="result">
				{$resultMessage}
			</div>
		{/if}
		{if $action != "new"}
		<table class="deployments_table">
			<tr>
				<th>
					Name
				</th>
				<th>
					Description
				</th>
				<th>
					Requirements
				</th>
				<th>
					Type
				</th>
				<th>
					Status
				</th>
			</tr>
			{foreach $deployments as $deployment}
				<tr class="deployments_table_item">
					<td>
						<strong>{$deployment->getName()} ({$deployment->getVersion()})</strong>
					</td>
					<td>
						{$deployment->getDescription()}
					</td>
					<td>
						{$deploymentManager->compileVersionsNamesList($deployment->getRequiredVersions())}
					</td>
					<td>
						{$deployment->getType()}
					</td>
					<td>
						{if $deploymentManager->isDeploymentInstallable($deployment)}
							<a class="button" href="{$controller->baseURL}deploy/install/type:{$deployment->getType()}/version:{$deployment->getVersion()}/">Install</a>
						{elseif $deploymentManager->isDeploymentInstalled($deployment)}
							installed
						{elseif $versions = $deploymentManager->getMissingVersions($deployment)}
							Missing versions: {$deploymentManager->compileVersionsNamesList($versions)};
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>
		{else}
			<form class="form_component" method="post" enctype="multipart/form-data" action="{$controller->baseURL}deploy/new/">
				<table>
					<tr>
						<td>
							Name:
						</td>
						<td>
							<input type="text" name="name"/>
						</td>
					</tr>
					<tr>
						<td>
							Description:
						</td>
						<td>
							<input type="text" name="description"/>
						</td>
					</tr>
					<tr>
						<td>
							Version:
						</td>
						<td>
							<input type="text" name="version"/>
						</td>
					</tr>
					<tr>
						<td>
							Required version:
						</td>
						<td>
							<input type="text" name="requires"/>
						</td>
					</tr>
					<tr>
						<td>
							Elements markers (child elements are exported):
						</td>
						<td>
							<input type="text" name="markers"/>
						</td>
					</tr>
					<tr>
						<td>
							Usergroups (enter groups markers):
						</td>
						<td>
							<input type="text" name="usergroups"/>
						</td>
					</tr>
					<tr>
						<td>
							Translations (enter translation groups):
						</td>
						<td>
							<input type="text" name="translations"/>
						</td>
					</tr>
					<tr>
						<td>
							Admin Translations (enter translation groups):
						</td>
						<td>
							<input type="text" name="adminTranslations"/>
						</td>
					</tr>
					<tr>
						<td>
							Privileges (enter module types):
						</td>
						<td>
							<input type="text" name="privileges"/>
						</td>
					</tr>
				</table>
				<input type="submit" value="Export"/>
			</form>
		{/if}
	</body>
</html>