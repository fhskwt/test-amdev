<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет клиента | Support Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .auth-container { max-width: 400px; margin: 100px auto; }
        .page-section { display: none; }
        .toast-container { z-index: 1060; }
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
                <h3 class="card-title text-center mb-4" id="auth-title">Вход в систему</h3>
                <form id="auth-form">
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="auth-btn">Войти</button>
                    <div class="mt-3 text-center">
                        <a href="#" id="toggle-auth">У меня нет аккаунта (Регистрация)</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="client-section" class="page-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Мои обращения</h2>
            <div>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                    + Новое обращение
                </button>
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
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Дата создания</th>
                        <th>Обновлено</th>
                        <th>Описание</th>
                        <th>Ответ администратора</th>
                        <th>Статус</th>
                    </tr>
                    </thead>
                    <tbody id="tickets-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="create-ticket-form" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новое обращение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Заголовок</label>
                    <input type="text" name="title" class="form-control" required placeholder="Краткая суть...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Описание проблемы</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary">Отправить</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/auth.js"></script>
<script src="js/tickets.js"></script>
<script src="js/statuses.js"></script>

<script>
    $(document).ready(function() {
        const toast = new bootstrap.Toast($('#liveToast')[0]);
        window.showNotify = function(text, type='success') {
            $('#toast-text').text(text);
            $('#liveToast').removeClass('bg-success bg-danger').addClass('bg-'+type);
            toast.show();
        };
        window.checkAuth();
        window.loadStatuses();
    });
</script>

</body>
</html>