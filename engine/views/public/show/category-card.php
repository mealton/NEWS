<div class="card subcategory-card" style="width: 18rem; margin: 0 10px 10px">
    <a href="/publication/category/<?= $id ?>/<?= $name ?>/" class="card-link">
        <div class="card-body">
            <h5 class="card-title">
                <?= $name ?>
            </h5>
            <p class="card-text"><?= $description ?></p>
            <?php if ($is_hidden): ?>
                <img src="/assets/uploads/icons/18+.png" alt="" class="img-18">
            <?php endif; ?>
        </div>
    </a>
</div>
