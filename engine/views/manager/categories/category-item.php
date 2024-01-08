<?php
/**
 * Элемент "Аккордеона" с категориями для редактирования
 * @var $id integer
 * @var $name string
 * @var $breadcrumb string
 * @var $parent_id integer
 * @var $categories_list string
 * @var $is_active boolean
 * @var $is_hidden boolean
 * @var $keywords string
 * @var $description string
 */

?>

<div class="accordion-item category-item" data-id="<?= $id ?>">
    <h2 class="accordion-header position-relative">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse_category_<?= $id ?>" aria-expanded="true" aria-controls="collapse_category_<?= $id ?>">
            <?= $name ?>
        </button>
    </h2>
    <div id="collapse_category_<?= $id ?>" class="accordion-collapse collapse" aria-labelledby="headingOne"
         data-bs-parent="#accordionExample">
        <div class="accordion-body">
            <p align="right">
                <i class='fa fa-times pointer' title="Удалить категорию <?= $name ?>" onclick="manager.removeCategory(this)" aria-hidden='true'></i>
            </p>
            <p class="lead"><?= $breadcrumb ?></p>
            <form onsubmit="return false;">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="name" placeholder="Название категории"
                               onchange="manager.updateCategory(this)"
                               value="<?= $name ?>">
                    </div>
                    <div class="col-md-5">
                        <select name="parent_id" class="form-control" onchange="manager.updateCategory(this)">
                            <?php if (!$parent_id): ?>
                                <option value="" disabled>Родительская категория</option>
                                <option value="0" selected>Без родителя</option>
                            <?php else: ?>
                                <option value="" selected disabled>Родительская категория</option>
                                <option value="0">Без родителя</option>
                            <?php endif ?>
                            <?= $categories_list ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <label>
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       onchange="manager.updateCategory(this)"
                                    <?= $is_active ? 'checked' : '' ?> />
                                Активная
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check" style="margin: 10px 0;">
                            <label>
                                <input class="form-check-input" type="checkbox" name="is_hidden"
                                       onchange="manager.updateCategory(this)"
                                    <?= $is_hidden ? 'checked' : '' ?> />
                                категория 18+
                            </label>
                        </div>
                        <input type="text" class="form-control" name="keywords" placeholder="Ключевые слова"
                               onchange="manager.updateCategory(this)"
                               value="<?= $keywords ?>">
                        <textarea onchange="manager.updateCategory(this)" name="description" class="form-control"
                                  style="min-height: 50px; max-height: 150px; margin-top: 15px"><?= $description ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>