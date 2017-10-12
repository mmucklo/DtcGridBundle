log = function(value) {
    if (console && console.log) {
        console.log(value);
    }
};

$.fn.dataTableExt.oApi.fnReloadAjax = function(oSettings, sNewSource,
        fnCallback, bStandingRedraw) {
    if (typeof sNewSource != 'undefined' && sNewSource != null) {
        oSettings.sAjaxSource = sNewSource;
    }

    this.oApi._fnProcessingDisplay(oSettings, true);
    var that = this;
    var iStart = oSettings._iDisplayStart;
    var aData = [];

    // Override ajax update draw function

    this.oApi._fnServerParams(oSettings, aData);
    var ajaxCallBack = function(json) {
        /* Clear the old information from the table */
        that.oApi._fnAjaxUpdateDraw( oSettings, json );

        /* Callback user function - for event handlers etc */
        if (typeof fnCallback == 'function' && fnCallback != null) {
            fnCallback(oSettings);
        }

        fnCallback = oSettings.oInit.fnInitComplete;
        if (typeof fnCallback == 'function' && fnCallback != null) {
            fnCallback(oSettings);
        }
    }

    // Fetch data from server
    oSettings.fnServerData(oSettings.sAjaxSource, aData, ajaxCallBack, oSettings);
};

/**
 * Require purl.js for filter
 */
(function($) {
    var _fnServerData = function(sUrl, aoData, fnCallback, oSettings) {
        var data = {
            limit: oSettings._iDisplayLength,
            offset: oSettings._iDisplayStart,
            test: 'one',
            filters: {
                "*": aoData.sSearch
            }
        };

        var url = oSettings.sAjaxSource;

        if (!url) {
            return;
        }

        var baseUrl = $.url(url).attr('path');
        var params = $.url(url).param();
        if (!params.filter) {
           params.filter = {};
        }

        if (oSettings.oPreviousSearch.sSearch) {
           params.filter['*'] = oSettings.oPreviousSearch.sSearch;
        }

        params.limit = oSettings._iDisplayLength;
        params.offset = oSettings._iDisplayStart;

        params.order = {};
        // Set up sorting
        if ( oSettings.oFeatures.bSort !== false )
        {
            var currentSorting = ( oSettings.aaSortingFixed !== null ) ?
                oSettings.aaSortingFixed.concat( oSettings.aaSorting ) :
                oSettings.aaSorting.slice();

            for (var index in currentSorting) {
                var sortedDirection = currentSorting[index][1];
                var sortedColIndex = currentSorting[index][0];
                var sortedCol = oSettings.aoColumns[sortedColIndex];
                var fieldName = $(sortedCol.nTh).data('column-field');

                params.order[fieldName] = sortedDirection;
            }
        }
        // Abort any current xhr

        if (oSettings.jqXHR) {
            oSettings.jqXHR.abort();
        }

        // Set filters if there are any
        oSettings.jqXHR = $.ajax({
            url: baseUrl,
            data: $.param(params),
            dataType: "json",
            cache: false,
            type: 'GET',
            success: function(json) {
                if ( json.sError ) {
                    oSettings.oApi._fnLog( oSettings, 0, json.sError );
                }

                $(oSettings.oInstance).trigger('xhr', [oSettings, json]);
                fnCallback( json );
            },
            error: function (xhr, error, thrown) {
                //bootbox.alert("Error parsing the results");

                if ( error == "parsererror" ) {
                    oSettings.oApi._fnLog( oSettings, 0, "DataTables warning: JSON data from "+
                        "server could not be parsed. This is caused by a JSON formatting error." );
                }
            }
        });
    };

    var methods = {};

    /**
     * jQuery wrapper
     *
     * @param options
     * @returns
     */
    methods.init = function(options) {
        return this.each(function() {
            var $table = $(this);
            var tableOptions = $table.data('jqtable');

            if (!options) {
                options = tableOptions;
            }
            else {
                options = $.extend(tableOptions, options);
                options = tableOptions;
            }

            if (!options) {
                return;
            }

            options.sServerMethod = 'POST';
            options.bServerSide = true;

            // Override Server Data, we want to use the format Grids support!
            options.fnServerData = _fnServerData;

            var dataTable = $table.DataTable(options);
            $table.data('datatable', dataTable);
        });
    };

    methods.reload = function(keepState) {
        return this.each(function() {
            var $table = $(this);
            var jqTable = $table.DataTable();
            jqTable.fnReloadAjax();
        });
    };

    /**
     * Filter takes jqTable's settings, then modifies
     *  the 'sAjaxSource' with new filter params, if resetPage is
     *  set, then modifies '_iDisplayStart' to 0 to kick the
     *  page back to page 1.
     */
    methods.filter = function(filters, resetPage) {
        return this.each(function() {
            var $table = $(this);
            var jqTable = $table.DataTable();

            var settings = jqTable.fnSettings();
            var url = settings.sAjaxSource;

            if (!url) {
                return;
            }

            var newUrl = $.url(url).attr('path');
            var params = $.url(url).param();
            params.filter = filters;

            if (resetPage) {
                settings._iDisplayStart = 0;
            }

            newUrl += '?' + $.param(params);
            settings.sAjaxSource = newUrl;
            jqTable.fnReloadAjax();
        });
    };

    $.fn.jqtable = function(method) {
        // Method calling logic
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else {
            if (typeof method === 'object' || !method) {
                return methods.init.apply(this, arguments);
            }
            else {
                $.error('Method ' + method + ' does not exist on jQuery.jqtable');
            }
        }
    };
})(jQuery);
