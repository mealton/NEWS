<?php
/**
 * Элмент публикации в файле sitemap.xml
 * @var $url string
 */
?>
<url>
    <loc><?= $url ?></loc>
    <lastmod><?= date('c', time()) ?></lastmod>
    <priority>1.0</priority>
</url>
