'use strict';

var datatables_options = require('./modules/datatables.js');

var dt_tables = [
    require('./modules/datatables-mandates.js'),
    require('./modules/datatables-users.js'),
];

$.extend($.fn.dataTable.defaults, datatables_options);

dt_tables.forEach(
    function(element, index, array) {
        element.init();
    }
);
