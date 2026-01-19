window.openTagManager = function() {
    renderTagsList();
    $('#tagsModal').modal('show');
};

window.renderTagsList = function() {
    $.get('/api/tags.php?action=get_tags', function(tags) {
        let html = '';
        tags.forEach(t => {
            html += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${t.name}
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTag(${t.id})">&times;</button>
            </li>`;
        });
        $('#tags-list-group').html(html);
    });
}

window.createTag = function() {
    let name = $('#new-tag-name').val();
    if(!name) return;
    $.post('/api/tags.php?action=add_tag', {name: name}, function() {
        $('#new-tag-name').val('');
        renderTagsList();
        if(window.fetchAvailableTags) window.fetchAvailableTags();
    });
};

window.deleteTag = function(id) {
    if(!confirm('Удалить этот тег?')) return;
    $.post('/api/tags.php?action=delete_tag', {id: id}, function() {
        renderTagsList();
        if(window.fetchAvailableTags) window.fetchAvailableTags();
    });
};

window.fetchAvailableTags = function() {
    $.get('/api/tags.php?action=get_tags', function(tags) {
        let $select = $('#modal-tags');
        if(!$select.length) return;
        $select.empty();
        tags.forEach(t => $select.append(new Option(t.name, t.name)));
        $select.select2({
            theme: 'bootstrap-5',
            placeholder: 'Выберите теги',
            tags: true,
            tokenSeparators: [',', ' '],
            width: '100%',
            dropdownParent: $select.closest('.modal')
        });
    });
};