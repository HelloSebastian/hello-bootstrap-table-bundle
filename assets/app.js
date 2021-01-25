require('bootstrap-table/dist/bootstrap-table.min.css');
require('bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.css');
require("bootstrap-table/dist/extensions/page-jump-to/bootstrap-table-page-jump-to.min.css");
require('./css/styles.css');

require('bootstrap-table/dist/bootstrap-table.min');
require('bootstrap-table/dist/extensions/export/bootstrap-table-export.min');
require('bootstrap-table/dist/extensions/cookie/bootstrap-table-cookie.min');
require('bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min');
require('bootstrap-table/dist/extensions/toolbar/bootstrap-table-toolbar.min');
require("bootstrap-table/dist/extensions/page-jump-to/bootstrap-table-page-jump-to.min");
require("bootstrap-table/dist/bootstrap-table-locale-all.min");

require("tableexport.jquery.plugin/libs/FileSaver/FileSaver.min");
require("tableexport.jquery.plugin/libs/js-xlsx/xlsx.core.min");
require("tableexport.jquery.plugin/tableExport.min");

require("bootstrap-table/dist/extensions/export/bootstrap-table-export.min");


$(function () {

    window.defaultFormatter = function (value) {
        return value;
    };

    window.defaultActionFormatter = function (value) {
        const $wrapper = $("<div />");

        for (let i = 0; i < value.length; i++) {
            const $button = $('<a />');
            $button.attr('href', value[i].route);
            $button.attr('class', value[i].classNames);
            $button.html(value[i].displayName);
            $wrapper.append($button);
        }

        return $wrapper.html();
    };

    const $table = $(".hello-bootstrap-table");
    const $bulkForm = $("#bulk_form");

    $table.bootstrapTable('destroy').bootstrapTable({
        stickyHeaderOffsetLeft: ($(".hello-bootstrap-table").offset().left - 7) + 'px',
        stickyHeaderOffsetRight: ($(".hello-bootstrap-table").offset().left - 7) + 'px',
        queryParams: function (params) {
            params.isCallback = true;
            return params;
        }
    });

    $bulkForm.submit(function (e) {
        const selectedRows = $table.bootstrapTable("getSelections");
        const hidden = $("#bulk_form input[type=hidden]");
        hidden.val(JSON.stringify(selectedRows));
    });

});