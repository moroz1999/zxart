<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns:xsi="//www.w3.org/2001/XMLSchema-instance"
		xsi:schemaLocation="//www.sitemaps.org/schemas/sitemap/0.9
//www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
	{foreach from=$mapItems item=mapItem}
		<url>
			<loc>{$mapItem.loc}</loc>
			<lastmod>{$mapItem.lastmod}</lastmod>
			<changefreq>monthly</changefreq>
			<priority>{$mapItem.priority}</priority>
		</url>
	{/foreach}
</urlset>