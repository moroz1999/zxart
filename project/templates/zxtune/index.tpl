<?xml version="1.0" encoding="UTF-8"?>
<response>
	<responseStatus>{$responseStatus}</responseStatus>
	<responseData>
		{if isset($responseData['totalAmount'])}<totalAmount>{$responseData['totalAmount']}</totalAmount>{/if}
		{if isset($responseData['author'])}
			<authors>
				{foreach from=$responseData['author'] item=author}
					<author>
					<id>{$author->id}</id>
						<title>{$author->title}</title>
						<tunesQuantity>{$author->tunesQuantity}</tunesQuantity>
						{if $author->realName}<realName>{$author->realName}</realName>{/if}
						{if $author->city}<city>{$author->city}</city>{/if}
						{if $author->country}<country>{$author->country}</country>{/if}
					</author>
				{/foreach}
			</authors>
		{/if}
		{if isset($responseData['zxMusic'])}
			<tunes>
				{foreach from=$responseData['zxMusic'] item=zxmusic}
					<tune>
						<id>{$zxmusic->id}</id>
						<title>{$zxmusic->title}</title>
						<originalFileName><![CDATA[{$zxmusic->getFileName('original', false, false)}]]></originalFileName>
						{if $zxmusic->year}<year>{$zxmusic->year}</year>{/if}
						{if $zxmusic->votes}<votes>{$zxmusic->votes}</votes>{/if}
						{if $zxmusic->time}<time>{$zxmusic->time}</time>{/if}
						{if $zxmusic->compo}<compo>{$zxmusic->compo}</compo>{/if}
						{if $zxmusic->partyplace}<partyplace>{$zxmusic->partyplace}</partyplace>{/if}
						{if $zxmusic->internalAuthor}<internalAuthor><![CDATA[{$zxmusic->internalAuthor}]]></internalAuthor>{/if}
						{if $zxmusic->internalTitle}<internalTitle><![CDATA[{$zxmusic->internalTitle}]]></internalTitle>{/if}
						<authors>
						{foreach $zxmusic->getAuthorIds() as $id}<id>{$id}</id>{/foreach}
						</authors>
					</tune>
				{/foreach}
			</tunes>
		{/if}
		{if isset($responseData['party'])}
			<parties>
				{foreach from=$responseData['party'] item=party}
					<party>
						<id>{$party->id}</id>
						<title>{$party->title}</title>
						{if $party->getYear()}<year>{$party->getYear()}</year>{/if}
						{if $party->city}<city>{$party->city}</city>{/if}
						{if $party->country}<country>{$party->country}</country>{/if}
					</party>
				{/foreach}
			</parties>
		{/if}
	</responseData>
</response>