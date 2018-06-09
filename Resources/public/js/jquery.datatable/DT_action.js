function dtc_grid_tablize(value) {
    var table = "<table class=\"table table-bordered\"><thead><th>Column</th><th>Value</th></thead></thead><tbody>";
    var stringifyValue = function (value) {
        if (value === null) {
            return '<div><em>null</em></div>';
        }
        if (typeof(value) === 'object') {
            return dtc_grid_tablize(value);
        }
        return $('<div />').text(value).html();
    };

    for (var prop in value) {
        if (value.hasOwnProperty(prop)) {
            table += "<tr><td>" + stringifyValue(prop) + "</td><td>" + stringifyValue(value[prop]) + "</td></tr>";
        }
    }
    table += "</tbody></table>";
    return table;
}

function dtc_grid_delete(context) {
    var $table = $(context).parents('table');
    var id = $table.attr('id');
    var route = $(context).attr('data-route');
    $(context).find('i').removeClass('dtc-grid-hidden');
    $table.find('button').attr('disabled','disabled');
    $.ajax({
        url: route
    }).then(function () {
        $table.data('datatable').ajax.reload();
    })
}

function dtc_grid_refresh(context) {
    var id = $(context).attr('data-id');
    var $table = $('#' + id);
    if (!$table.get(0)) {
        return;
    }
    $(context).find('i').removeClass('dtc-grid-hidden');
    $(context).attr('disabled','disabled');
    $table.find('button').attr('disabled','disabled');
    $table.data('datatable').ajax.reload(function () {
      $(context).find('i').addClass('dtc-grid-hidden');
      $(context).attr('disabled',false);
    });
}

function dtc_grid_show(context) {
    var $table = $(context).parents('table');
    var id = $table.attr('id');
    var route = $(context).attr('data-route');
    var $modal = $('#' + 'modal-' + id);
    var identifier = $(context).attr('data-id');
    $modal.find('.modal-title').text('Show Id #' + identifier);
    $modal.modal('show');
    $modalBody = $modal.find('.modal-body');
    $modalBody.addClass('dtc-grid-spinner');
    $modalBody.html('<div style="height: 50px; width: 50px;">&nbsp;</div>');

    $.ajax({
        url: route
    }).then(function (result) {
        console.log(result);
        $modalBody.removeClass('dtc-grid-spinner');
        $modalBody.html(dtc_grid_tablize(result));
    });
}