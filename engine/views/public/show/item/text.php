<?php
/**
 * Элемент контента Текст
 * @var $style string
 * @var $content string
 */
?>

<?php if (preg_match("/^<ul>(.*)<\/ul>$/", trim($content))): echo $content; else: ?>
    <p class="fs-5 mb-4" style="text-align: justify; <?= str_replace("font-size: px;", "", $style) ?>">
        <?= str_replace(["\n", "<br><br>", "<p></p>"], ["<br>", "<br>", ""], $content) ?>
    </p>
<?php endif; ?>