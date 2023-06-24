<?php
/**
 * Пагинация
 */
?>
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $PreviousDisabled ?>">
            <a class="page-link" href="<?= $href ?>page=<?= $PreviousPage ?>" tabindex="-1">Предыдущая</a>
        </li>
        <?= $pages ?>
        <li class="page-item <?= $NextDisabled ?>">
            <a class="page-link" href="<?= $href ?>page=<?= $NextPage ?>">Следующая</a>
        </li>
    </ul>
</nav>
