{if $element->getReplies()}
    <div class="form_items">
        <span class="form_label">
            {translations name = "{$structureType}.replies"}
        </span>
        <div class="form_field">
            {foreach $element->getReplies() as $reply}
                <div class="reply">
                    <span class="reply_author">{$reply->author}</span>
                    <a class="icon icon_edit" href="{$reply->URL}"></a>
                    <a href="{$reply->URL}id:{$reply->id}/action:delete" class='icon icon_delete'></a>
                    <br />
                    <div class="reply_datetime">{$reply->dateTime}</div>
                    <div class="html_content reply_content">{$reply->content|html_entity_decode}</div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}