<div class="card subcategory-card" style="width: 18rem;">
    <a href="/publication/authors/<?= $id ?>::<?= $username ?>/" class="card-link">
        <div class="card-body">
            <img src="<?= $profile_image ?>" width="60" class="user-circle-img" alt="Пользователь <?= $username ?>" title="Пользователь <?= $username ?>">
            <h5 class="card-title"><?= $username ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?= $p_count ?> <?= current_ending($p_count, ['публикация','публикации','публикаций']) ?></h6>
            <p class="card-text"></p>
        </div>
    </a>
</div>
