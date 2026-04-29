<div class="footer_folders">
	{foreach $folders as $folder}
		<a href="{$folder->URL}" class="footer_folders_item">
			{$folder->title}
		</a>
		{if !$folder@last}
			<div class="footer_folders_seperator"></div>
		{/if}
	{/foreach}
</div>