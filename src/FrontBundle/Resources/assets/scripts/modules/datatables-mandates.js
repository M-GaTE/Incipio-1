module.exports = {
    config: {
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: -1,
            },
            {
                type: 'string',
                targets: 2,
            },
            {
                type: 'string',
                targets: 3,
            },
        ],
        order: [[3, 'desc'], [3, 'desc']],
    },
    init: function() {
        $('#mandates-index-table').DataTable(this.config);
    },
};

