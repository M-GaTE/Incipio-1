module.exports = {
    config: {
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: -1,
            },
        ],
        order: [[0, 'desc'], [1, 'asc']],
    },
    init: function() {
        $('#users-index-table').DataTable(this.config);
    },
};

