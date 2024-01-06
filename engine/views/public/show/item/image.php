<?php /**
 * @var $is_hidden boolean
 * @var $description string
 * @var $content string
 * @var $source string
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
        <figure style="display: inline-block;">
            <img itemprop="contentUrl" class="img-fluid rounded publication-image-item"
                 onclick="publication.showModal(this)" src="<?= $content ?>" alt="<?= htmlspecialchars($description) ?>">
            <?php if ($source): ?>
                <figcaption>
                    <p style="text-align: right">
                        <small>
                            Источник:
                            <cite title="Source Title">
                                <a href="<?= $source ?>" target="_blank"><?= get_from_url($source) ?></a>
                            </cite>
                        </small>
                    </p>
                </figcaption>
            <?php endif ?>
        </figure>
    </figure>
<?php endif ?>