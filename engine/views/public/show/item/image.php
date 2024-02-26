<?php /**
 * Элемент контента Изображение
 * @var $is_hidden boolean
 * @var $like_content boolean
 * @var $content_is_liked boolean
 * @var $description string
 * @var $content string
 * @var $source string
 * @var $id integer
 * @var $content_likes integer
 *
 */
?>
<?php if (!$is_hidden): ?>
    <figure class="mb-4 image-item-container" itemscope itemtype="http://schema.org/ImageObject">
        <?php if ($description): ?>
            <blockquote class="blockquote">
                <p><em itemprop="description"><?= $description ?></em></p>
            </blockquote>
        <?php endif ?>
        <figure style="display: inline-block; position: relative">
            <img itemprop="contentUrl" class="img-fluid rounded publication-image-item"
                 data-id="<?= $id ?>"
                 onclick="publication.showModal(this)" src="<?= $content ?>"
                 alt="<?= htmlspecialchars($description) ?>">

            <?php if ($like_content): ?>
                <?php include __DIR__ . '/like_content.php'; ?>
            <?php endif; ?>

            <figcaption>
                <p style="text-align: right">
                    <?php if ($source): ?>

                        <small>
                            Источник:
                            <cite title="Source Title">
                                <a href="<?= $source ?>" target="_blank"><?= get_from_url($source) ?></a>
                            </cite>
                        </small>

                    <?php endif ?>

                </p>
            </figcaption>
        </figure>
    </figure>
<?php endif ?>

















