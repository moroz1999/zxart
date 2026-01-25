<div class="comment">
	<div class="comment_head">
		<div class="comment_datetime">
			{$element->dateTime}
		</div>
		<div class="comment_author">
			{$element->author}
		</div>
		<div class="comment_email">
			{$element->email}
		</div>
	</div>
	<div class="comment_content">
		{$element->content}
	</div>
	{if $replies = $element->getReplies()}
		<div class="comment_replies">
			{foreach $replies as $reply}
				<div class="comment_reply">
					<div class="comment_reply_head">
						<div class="comment_reply_datetime">
							{$reply->dateTime}
						</div>
						<div class="comment_reply_author">
							{$reply->author}
						</div>
					</div>
					<div class="comment_reply_content">
						{$reply->content|html_entity_decode}
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
</div>