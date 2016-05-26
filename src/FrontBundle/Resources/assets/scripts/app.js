let datatablesOptions = require('./modules/datatables.js');

let dtTables = [
    require('./modules/datatables-mandates.js'),
    require('./modules/datatables-users.js'),
];

$.extend($.fn.dataTable.defaults, datatablesOptions);

dtTables.forEach(
    function(element) {
        element.init();
    }
);
