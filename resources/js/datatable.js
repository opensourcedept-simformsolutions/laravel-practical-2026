function initializeDataTables() {
    if (!window.$ || !window.$.fn || !window.$.fn.DataTable) {
        return;
    }

    window.$('[data-datatable]').each(function () {
        const $table = window.$(this);

        if (window.$.fn.DataTable.isDataTable(this)) {
            return;
        }

        $table.DataTable({
    searching: $table.data('searching') === true || $table.data('searching') === 'true',
    paging: $table.data('paging') === true || $table.data('paging') === 'true',
    ordering: $table.data('ordering') === true || $table.data('ordering') === 'true',
    pageLength: Number($table.data('page-length')) || 10,

    info: false,
});
    });
}

window.$(function () {
    initializeDataTables();
});
