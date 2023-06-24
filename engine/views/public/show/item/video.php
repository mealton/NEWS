<figure class="mb-4" itemscope itemtype="http://schema.org/ImageObject">
    <?php if ($description): ?>
        <blockquote class="blockquote">
            <p><em itemprop="description"><?= $description ?></em></p>
        </blockquote>
    <?php endif ?>
    <div style="display: inline-block;">
        <?php if (!strpos($content, "youtube")): ?>
            <video itemprop="contentUrl" src="<?= $content ?>" controls="controls" class="img-fluid"></video>
        <?php else: ?>
            <iframe itemprop="contentUrl" style="max-width: 100%" width="640" height="360" src="<?= $content ?>" title="" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen></iframe>;
        <?php endif; ?>
        <?php if ($source): ?>
            <figcaption>
                <p align="right">
                    <small>
                        Источник:
                        <cite title="Source Title">
                            <a href="<?= $source ?>" target="_blank"><?= get_from_url($source) ?></a>
                        </cite>
                    </small>
                </p>
            </figcaption>
        <?php endif ?>
    </div>
</figure>
