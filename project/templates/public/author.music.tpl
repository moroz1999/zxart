{if ($element->getYearsWorks('authorMusic'))}
	<div class="author_details_section">
		<h1>{translations name="author.music"}</h1>
		<a class="author_details_save button" href="{$element->getSaveUrl('zxMusic')}">{translations name='author.save_music'}</a>
		<zx-author-tunes element-id="{$element->id}"></zx-author-tunes>

	</div>
{/if}
