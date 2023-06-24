<div class="container rounded bg-white mt-5 mb-5">
    <form action="/publication/add" id="new-public-form" method="post" class="publication-form" data-method="add">
        <?= render('public/edit', 'publication', ['start_text' => 1, 'categories' => $categories])?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" name="publish" type="checkbox" value="1" checked>
                        Опубликовать
                    </label>
                </div>
            </div>
        </div>
        <hr>
        <button class="btn btn-primary" type="submit">Добавить</button>
        <button type="button" class="btn btn-secondary" onclick="publication.cancel()">Отмена</button>
    </form>
</div>
