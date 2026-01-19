$(document).on('click', '#toggle-auth', function(e) {
    e.preventDefault();
    window.isRegistration = !window.isRegistration;
    $('#auth-title').text(window.isRegistration ? 'Регистрация' : 'Вход в систему');
    $('#auth-btn').text(window.isRegistration ? 'Зарегистрироваться' : 'Войти');
    $(this).text(window.isRegistration ? 'Уже есть аккаунт? Войти' : 'У меня нет аккаунта (Регистрация)');
});

$(document).on('click', '#logout-btn', function() {
    $.post('/api/auth.php?action=logout', function() {
        if(window.showNotify) showNotify('Вы вышли из системы');
        checkAuth();
    });
});

$('#admin-auth-form').submit(function(e) {
    e.preventDefault();
    $.post('/api/auth.php?action=login', $(this).serialize(), () => checkAuth('admin'))
        .fail(() => showNotify('Ошибка входа', 'danger'));
});

window.checkAuth = function(roleRequired = 'client') {
    $.get('/api/auth.php?action=me', function(res) {
        $('.page-section').hide();
        if (res.authorized) {
            if (roleRequired === 'admin' && res.role !== 'admin') {
                $('#auth-section').show();
                return;
            }

            $(`#${roleRequired}-section`).show();

            if (roleRequired === 'admin' && window.loadAllTickets) loadAllTickets();
            if (roleRequired === 'client' && window.loadTickets) loadTickets();
        } else {
            $('#auth-section').show();
        }
    });
};

$('#auth-form').submit(function(e) {
    e.preventDefault();
    const action = window.isRegistration ? 'register' : 'login';

    $.ajax({
        url: `/api/auth.php?action=${action}`,
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if(res.success) {
                window.showNotify(window.isRegistration ? 'Успешная регистрация!' : 'Вы вошли в систему');
                checkAuth();
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON ? xhr.responseJSON.error : 'Ошибка доступа';
            window.showNotify(errorMsg, 'danger');
        }
    });
});