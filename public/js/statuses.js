window.openStatusManager = function() {
    renderStatusesList();
    $('#statusesModal').modal('show');
};

window.renderStatusesList = function() {
    $.get('/api/statuses.php?action=get_statuses', function(statuses) {
        let html = '';
        statuses.forEach(s => {
            html += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${s.name}
                <button class="btn btn-sm btn-outline-danger" onclick="deleteStatus(${s.id})">&times;</button>
            </li>`;
        });
        $('#statuses-list-group').html(html);
    });
}

window.createStatus = function() {
    let name = $('#new-status-name').val();
    if(!name) return;
    $.post('/api/statuses.php?action=add_status', {name: name}, function() {
        $('#new-status-name').val('');
        renderStatusesList();
        if(window.loadStatuses) window.loadStatuses();
    });
};

window.deleteStatus = function(id) {
    if(!confirm('Вы уверены?')) return;
    $.post('/api/statuses.php?action=delete_status', {id: id})
        .done(function() {
            renderStatusesList();
            if(window.loadStatuses) window.loadStatuses();
        })
        .fail(function(xhr) {
            alert(JSON.parse(xhr.responseText).error);
        });
};

window.loadStatuses = function() {
    $.get('/api/statuses.php?action=get_statuses', function(statuses) {
        let options = statuses.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        $('#modal-status, #filter-status').html(options);
        $('#filter-status').prepend('<option value="">Все статусы</option>').val('');
    });
};