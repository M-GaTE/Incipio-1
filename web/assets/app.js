(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

},{"./modules/datatables-mandates.js":2,"./modules/datatables-users.js":3,"./modules/datatables.js":4}],2:[function(require,module,exports){
'use strict';

module.exports = {
    config: {
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: -1
            },
            {
                type: 'string',
                targets: 2
            },
            {
                type: 'string',
                targets: 3
            }
        ],
        order: [[3, 'desc'], [3, 'desc']]
    },
    init: function() {
        $('#mandates-index-table').DataTable(this.config);
    }
};


},{}],3:[function(require,module,exports){
'use strict';

module.exports = {
    config: {
        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: -1
            }
        ],
        order: [[0, 'desc'], [1, 'asc']]
    },
    init: function() {
        $('#users-index-table').DataTable(this.config);
    }
};


},{}],4:[function(require,module,exports){
'use strict';

module.exports = {
    language: {
        sProcessing: 'Traitement en cours...',
        sSearch: 'Rechercher&nbsp;:',
        sLengthMenu: 'Afficher _MENU_ &eacute;l&eacute;ments',
        sInfo: 'Affichage de l\'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments',
        sInfoEmpty: 'Affichage de l\'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ments',
        sInfoFiltered: '(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)',
        sInfoPostFix: '',
        sLoadingRecords: 'Chargement en cours...',
        sZeroRecords: 'Aucun &eacute;l&eacute;ment &agrave; afficher',
        sEmptyTable: 'Aucune donn&eacute;e disponible dans le tableau',
        oPaginate: {
            sFirst: 'Premier',
            sPrevious: 'Pr&eacute;c&eacute;dent',
            sNext: 'Suivant',
            sLast: 'Dernier'
        },
        oAria: {
            sSortAscending: ': activer pour trier la colonne par ordre croissant',
            sSortDescending: ': activer pour trier la colonne par ordre d&eacute;croissant'
        }
    }
};


},{}]},{},[1]);
