{if ($element->getYearsWorks('authorPicture'))}
	<div class="author_details_section">
		<h1>{translations name="author.graphics"}</h1>
		<a class="author_details_save button" href="{$element->getSaveUrl('zxPicture')}">{translations name='author.save'}</a>
		<zx-author-pictures element-id="{$element->id}"></zx-author-pictures>
	</div>
{/if}
