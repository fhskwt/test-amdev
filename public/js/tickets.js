window.viewTicket = function(id) {
    $.get('/api/tickets.php', {id: id, is_admin: 1}, function(t) {
        $('#modal-ticket-id').val(t.id);
        $('#display-id').text(t.id);
        $('#modal-username').text(t.client_name);
        $('#modal-desc').text(t.description);
        $('#modal-status').val(t.status_id);
        let selectedTags = t.tags ? t.tags.split(',') : [];
        $('#modal-tags').val(selectedTags).trigger('change');
        $('#modal-reply').val(t.admin_answer);
        $('#viewModal').modal('show');
    });
};

window.loadAllTickets = function() {
    const filters = { status: $('#filter-status').val(), sort: $('#sort-date').val(), is_admin: 1 };
    $.get('/api/tickets.php', filters, function(data) {
        let h = data.map(t => `
            <tr>
                <td>${t.id}</td>
                <td><b>${t.client_name || 'User'}</b></td>
                <td>${t.title}</td>
                <td><span class="badge bg-secondary">${t.status_name}</span></td>
                <td><small>${t.tags || ''}</small></td>
                <td>${t.admin_answer || ''}</td>
                <td><button class="btn btn-sm btn-primary" onclick="viewTicket(${t.id})">Открыть</button></td>
            </tr>`).join('');
        $('#admin-tickets-table').html(h);
    });
};



window.loadTickets = function() {
    const filters = {
        status: $('#filter-status').val(),
        sort: $('#sort-date').val()
    };
    $.get('/api/tickets.php', filters, function(data) {
        let html = '';
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(ticket => {
                html += `
                <tr>
                    <td>#${ticket.id}</td>
                    <td><small>${ticket.created_at}</small></td>
                    <td><small>${ticket.updated_at}</small></td>
                    <td class="text-truncate" style="max-width: 300px;">
                        <strong>${ticket.title}</strong><br>
                        <span class="text-muted">${ticket.description}</span>
                    </td>
                    <td><span class="text-muted">${ticket.admin_answer || ''}</span></td>
                    <td><span class="badge bg-secondary">${ticket.status_name}</span></td>
                </tr>`;
            });
        }
        $('#tickets-table-body').html(html || '<tr><td colspan="6" class="text-center">Обращений пока нет</td></tr>');
    });
};

$('#create-ticket-form').submit(function(e) {
    e.preventDefault();
    $.post('/api/tickets.php?action=create', $(this).serialize(), function(res) {
        if(res.success) {
            $('#createTicketModal').modal('hide');
            $('#create-ticket-form')[0].reset();
            window.showNotify('Тикет успешно создан!');
            loadTickets();
        }
    }).fail(() => window.showNotify('Ошибка при создании', 'danger'));
});

$('#admin-reply-form').submit(function(e) {
    e.preventDefault();
    $.post('/api/tickets.php?action=update_admin', $(this).serialize(), function() {
        $('#viewModal').modal('hide');
        window.showNotify('Тикет обновлен');
        loadAllTickets();
    });
});

$('.filter-trigger').change(loadAllTickets);

$('.filter-trigger').change(loadTickets);