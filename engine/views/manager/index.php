<?php
/**
 * Выбор вкладки на странице администрирования
 */
?>
<ul class="nav nav-tabs" id="myTab" role="tablist">

    <? foreach ($GLOBALS['config']['manager']['tabs'] as $tab => $title): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $_GET['tab'] == $tab ? 'active' : '' ?>" id="<?= $tab ?>-tab"
                    data-bs-toggle="tab" data-bs-target="#<?= $tab ?>" type="button"
                    data-publication_id="<?= $_GET['publication_id'] ?>"
                    data-publication_page="<?= $_GET['page'] ?>"
                    role="tab" aria-controls="<?= $tab ?>" aria-selected="false">
                <?= $title ?>
            </button>
        </li>
    <?php endforeach; ?>

</ul>
<div class="tab-content" id="myTabContent">
    <? foreach ($GLOBALS['config']['manager']['tabs'] as $tab => $title): ?>
        <div class="tab-pane <?= $_GET['tab'] == $tab ? 'show active' : '' ?>" id="<?= $tab ?>"
             role="tabpanel"
             aria-labelledby="<?= $tab ?>-tab">
            <div class="container rounded bg-white mt-5 mb-5">
                <?php include __DIR__ . '/' . $tab . '.php' ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>