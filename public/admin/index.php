<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель Администратора</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"/>
    <style>
        body { background-color: #f4f7f6; }
        .auth-container { max-width: 400px; margin: 100px auto; }
        .page-section { display: none; }
    </style>
</head>
<body>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toast-text">
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрыть"></button>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div id="auth-section" class="page-section auth-container">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Вход в Админ панель</h3>
                <form id="admin-auth-form">
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="auth-btn">Войти</button>
                </form>
            </div>
        </div>
    </div>

    <div id="admin-section" class="page-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Все обращения клиентов</h2>
            <div>
                <button class="btn btn-success btn-sm ms-3" onclick="openStatusManager()">Статусы</button>
                <button class="btn btn-success btn-sm ms-3" onclick="openTagManager()">Теги</button>
                <button class="btn btn-outline-danger btn-sm ms-3" id="logout-btn">Выйти</button>
            </div>
        </div>

        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <select class="form-select filter-trigger" id="filter-status">
                    <option value="">Статусы</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select filter-trigger" id="sort-date">
                    <option value="desc">Сначала новые</option>
                    <option value="asc">Сначала старые</option>
                </select>
            </div>
        </div>

        <div class="card shadow-sm">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Клиент</th>
                    <th>Заголовок</th>
                    <th>Статус</th>
                    <th>Теги</th>
                    <th>Ответ администратора</th>
                    <th>Действие</th>
                </tr>
                </thead>
                <tbody id="admin-tickets-table"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="admin-reply-form" class="modal-content">
            <input type="hidden" name="ticket_id" id="modal-ticket-id">
            <div class="modal-header">
                <h5 class="modal-title">Обращение #<span id="display-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Клиент:</strong> <span id="modal-username"></span></p>
                <p><strong>Описание:</strong> <br><span id="modal-desc" class="text-muted"></span></p>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Изменить статус</label>
                    <select name="status_id" id="modal-status" class="form-select">
                        <option value="">Статусы</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Теги</label>
                    <select name="tags[]" id="modal-tags" class="form-select" multiple="multiple">
                    </select>
                    <div class="form-text">Выберите из списка или введите новый тег и нажмите Enter</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ваш ответ клиенту</label>
                    <textarea name="reply" id="modal-reply" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="tagsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Управление тегами</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="new-tag-name" class="form-control" placeholder="Название нового тега">
                    <button class="btn btn-success" onclick="createTag()">Добавить</button>
                </div>
                <ul class="list-group" id="tags-list-group">
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Управление статусами</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="new-status-name" class="form-control" placeholder="Название статуса">
                    <button class="btn btn-success" onclick="createStatus()">Добавить</button>
                </div>
                <ul class="list-group" id="statuses-list-group">
                </ul>
                <div class="alert alert-warning mt-3 small">
                    <i class="bi bi-exclamation-triangle"></i> Удалить можно только статус, не привязанный ни к одному тикету.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="js/auth.js"></script>
<script src="js/tickets.js"></script>
<script src="js/statuses.js"></script>
<script src="js/tags.js"></script>


<script>
    $(document).ready(function() {
        const toast = new bootstrap.Toast($('#liveToast')[0]);
        window.showNotify = function(text, type='success') {
            $('#toast-text').text(text);
            $('#liveToast').removeClass('bg-success bg-danger').addClass('bg-'+type);
            toast.show();
        };

        window.checkAuth('admin');
        window.loadStatuses();
        window.fetchAvailableTags();

    });
</script>
</body>
</html>