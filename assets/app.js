require('bootstrap-table/dist/bootstrap-table.min.css');
require('bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.css');
require("bootstrap-table/dist/extensions/page-jump-to/bootstrap-table-page-jump-to.min.css");
require('bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control.min.css');
require('./css/styles.css');

require('bootstrap-table/dist/bootstrap-table.min');
require('bootstrap-table/dist/extensions/export/bootstrap-table-export.min');
require('bootstrap-table/dist/extensions/cookie/bootstrap-table-cookie.min');
require('bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min');
//require('bootstrap-table/dist/extensions/toolbar/bootstrap-table-toolbar.min');
require('./bootstrap-table-toolbar');
require("bootstrap-table/dist/extensions/page-jump-to/bootstrap-table-page-jump-to.min");
require("bootstrap-table/dist/bootstrap-table-locale-all.min");

require("tableexport.jquery.plugin/libs/FileSaver/FileSaver.min");
require("tableexport.jquery.plugin/libs/js-xlsx/xlsx.core.min");
require("tableexport.jquery.plugin/tableExport.min");

require("bootstrap-table/dist/extensions/export/bootstrap-table-export.min");
require('bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control');


$(function () {

    /**
     * Default formatter for action columns.
     */
    window.defaultActionFormatter = function (value) {
        const buttons = [];

        for (let i = 0; i < value.length; i++) {
            buttons.push(
                `<a href="${value[i].route}" class="${value[i].classNames}">${value[i].displayName}</a>`
            );
        }

       return buttons.join("");
    };

    window.defaultActionCellStyle = function () {
        return {
            css: {
                display: 'inline-block'
            }
        };
    };

    window.defaultAdvSearchTextField = function (field, filterOptions, value) {
        let val = value || "";
        return `<input type="text" value="${val}" class="form-control" name="${field}" placeholder="${filterOptions.placeholder}" id="${field}">`;
    };

    window.defaultAdvSearchChoiceField = function (field, filterOptions, value) {
        const choices = [];
        for (var prop in filterOptions.choices) {
            choices.push(`<option ${value !== undefined && value == prop ? "selected" : ""} value="${prop}">${filterOptions.choices[prop]}</option>`);
        }

        return `<select class="form-control" name="${field}" id="${field}">
            ${choices.join("")}
        </select>`;
    };

    const $tables = $(".hello-bootstrap-table");

    if ($tables.length) {
        $tables.each(index => {
            const $table = $($tables[index]);
            const tableName = $table.data('id-table');
            const bulkIdentifier = $table.data('bulk-identifier');

            $table.bootstrapTable('destroy').bootstrapTable({
                queryParams: function (params) {
                    params.isCallback = true;
                    params.tableName = tableName;
                    return params;
                },
                icons: {
                    advancedSearchIcon: 'fa-filter',
                    paginationSwitchDown: 'fa-caret-square-o-down',
                    paginationSwitchUp: 'fa-caret-square-o-up',
                    columns: 'fa-columns',
                    refresh: 'fa-sync',
                    export: 'fa-download',
                    detailOpen: 'fa-plus',
                    detailClose: 'fa-minus',
                    toggleOff: 'fa-toggle-off',
                    toggleOn: 'fa-toggle-on',
                    fullscreen: 'fa-arrows-alt',
                    search: 'fa-search',
                    clearSearch: 'fa-trash'
                }
            });

            const $bulkForm = $("#bulk_form_" + tableName);
            $bulkForm.submit(function (e) {
                const selectedRows = $table.bootstrapTable("getSelections");
                const identifiers = selectedRows.map(row => row[bulkIdentifier]);

                const hidden = $("#bulk_form_" + tableName + " input[type=hidden]");
                hidden.val(JSON.stringify(identifiers));
            });
        });
    }
});