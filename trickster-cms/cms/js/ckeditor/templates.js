CKEDITOR.addTemplates( 'default',
{
	// The name of the subfolder that contains the preview images of the templates.
	// imagesPath : 'images/',
	imagesPath :'/project/js/ckeditor/images/',

	// Template definitions.
	templates :
		[
			{
				title: 'Styled table',
				image: 'template1.gif',
				description: 'Table',
				html:
					'<table class="table_component">' +
						'<thead>' +
							'<tr>' +
								'<th>header 1</th><th>header 2</th><th>header 3</th>' +
							'</tr>' +
						'</thead>' +
						'<tbody>' +
							'<tr>' +
								'<td>row 1</td><td>row 1</td><td>row 1</td>' +
							'</tr>' +
							'<tr>' +
								'<td>row 2</td><td>row 2</td><td>row 2</td>' +
							'</tr>' +
							'<tr>' +
								'<td>row 3</td><td>row 3</td><td>row 3</td>' +
							'</tr>' +
						'</tbody>' +
						'<tfoot>' +
							'<tr>' +
								'<td>footer 1</td><td>footer 1</td><td>footer 1</td>' +
							'</tr>' +
						'</tfoot>' +
					'</table>'
			},
			{
				title: 'Side picture (left)',
				image: 'template1.gif',
				description: '',
				html:
					'<div class="template_sideimage template_sideimage_left">' +
						'<div class="template_sideimage_cell template_sideimage_cell_graphic">' +
							'<img src="/vendor/artweb-ou/trickster-cms/cms/images/admin/layout_default.png" />' +
						'</div>' +
						'<div class="template_sideimage_cell">' +
							'<h2>TITLE</h2>' +
							'<p>- Line 1</p>' +
							'<p>- Line 2</p>' +
							'<p>- Line 3</p>' +
						'</div>' +
					'</div>' +
					'<p></p>'
			},
			{
				title: 'Side picture (right)',
				image: 'template1.gif',
				description: '',
				html:
					'<div class="template_sideimage template_sideimage_right">' +
						'<div class="template_sideimage_cell">' +
							'<h2>TITLE</h2>' +
							'<p>- Line 1</p>' +
							'<p>- Line 2</p>' +
							'<p>- Line 3</p>' +
						'</div>' +
						'<div class="template_sideimage_cell template_sideimage_cell_graphic">' +
							'<img src="/vendor/artweb-ou/trickster-cms/cms/images/admin/layout_default.png" />' +
						'</div>' +
					'</div>' +
					'<p></p>'
			},
			{
				title: 'Gallery Picture Block',
				image: 'gallery.gif',
				description: 'Gallery Picture Description',
				html:
					'<div class="gallery_block">' +
						'<div class="gallery_promo_title">' +
							'Gallerii Picture promo-tekst' +
						'</div>' +
						'<h1 class="main_gallery_title">' +
							'Gallerii Picture Pealkiri' +
						'</h1>' +
						'<div class="gallery_content">' +
							'Gallerii Picture tekst' +
						'</div>' +
					'</div>'
			},
			{
				title: 'Gallery Orange Block',
				image: 'yellow_gallery_description.svg',
				description: 'Gallery Orange Block',
				html:
					'<div class="gallery_orange_content_image"> Orange Block Image </div>' +
					'<h2 class="gallery_orange_content_title">' +
					'Orange Block Title' +
					'</h2>' +
					'<div class="gallery_orange_content">' +
					'Orange Block Content' +
					'</div>'
			},
		]
});