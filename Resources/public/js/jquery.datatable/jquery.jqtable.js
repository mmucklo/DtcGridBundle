$.fn.dataTableExt.oApi.fnReloadAjax = function(oSettings, sNewSource,
        fnCallback, bStandingRedraw) {

    if (typeof sNewSource != 'undefined' && sNewSource != null) {
        oSettings.sAjaxSource = sNewSource;
    }

    this.oApi._fnProcessingDisplay(oSettings, true);
    var that = this;
    var iStart = oSettings._iDisplayStart;
    var aData = [];

    this.oApi._fnServerParams(oSettings, aData);

    // Custom data function
    oSettings.fnServerData(oSettings.sAjaxSource, aData, function(json) {
        /* Clear the old information from the table */
        that.oApi._fnClearTable(oSettings);

        /* Got the data - add it to the table */
        log('before fn server data...');
        var aData = (oSettings.sAjaxDataProp !== "") ? that.oApi
                ._fnGetObjectDataFn(oSettings.sAjaxDataProp)(json) : json;
            log('after fn server data...');

        for ( var i = 0; i < aData.length; i++) {
            that.oApi._fnAddData(oSettings, aData[i]);
        }

        oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
        that.fnDraw();

        if (typeof bStandingRedraw != 'undefined' && bStandingRedraw === true) {
            oSettings._iDisplayStart = iStart;
            that.fnDraw(false);
        }

        that.oApi._fnProcessingDisplay(oSettings, false);

        /* Callback user function - for event handlers etc */
        if (typeof fnCallback == 'function' && fnCallback != null) {
            fnCallback(oSettings);
        }

        fnCallback = oSettings.oInit.fnInitComplete;
        if (typeof fnCallback == 'function' && fnCallback != null) {
            fnCallback(oSettings);
        }

    }, oSettings);
};

(function($) {
    $.fn.dataTableExt.oApi.fnDtcGrid = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
        log('grid override...');
        log(oSettings);
    }

    if ( typeof $.fn.dataTable === 'function')
    {
        $.fn.dataTableExt.aoFeatures.push( {
            'fnInit': function( oDTSettings ) {
                log('on init...');
                log(oDTSettings);
            }
        });
    }
})(jQuery);

/**
 * Require purl.js for filter
 */
(function($) {
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
            var options = $table.attr('data-jqtable');

            if (!options) {
                return;
            }
            else {
                options = $.parseJSON(options);
            }

            options.sServerMethod = 'POST';
            options.bServerSide = true;
            options.fnInitComplete = function() {
                //$('.timeago').timeago();
                //$table.find('[data-form-url]').dialogForm();
            };

            // Override Server Data, we want to use the format Grids support!
            options.fnServerData = function(sUrl, aoData, fnCallback, oSettings) {
                var data = {
                    limit: oSettings._iDisplayLength,
                    offset: oSettings._iDisplayStart
                };

                // Set filters if there are any
                $.ajax({
                    url: sUrl,
                    data: data,
                    dataType: "json",
                    cache: false,
                    type: 'POST',
                    success: function(json) {
                        if ( json.sError ) {
                            oSettings.oApi._fnLog( oSettings, 0, json.sError );
                        }

                        $(oSettings.oInstance).trigger('xhr', [oSettings, json]);
                        fnCallback( json );
                    },
                    error: function (xhr, error, thrown) {
                        if ( error == "parsererror" ) {
                            oSettings.oApi._fnLog( oSettings, 0, "DataTables warning: JSON data from "+
                                "server could not be parsed. This is caused by a JSON formatting error." );
                        }
                    }
                });
            }

            $table.dataTable(options);
        });
    };

    methods.filter = function(filters, keepState) {
        return this.each(function() {
            var $table = $(this);
            var jqTable = $table.dataTable();

            var settings = jqTable.fnSettings();
            var url = settings.sAjaxSource;

            if (!url) {
                return;
            }

            var newUrl = $.url(url).attr('path');
            var params = $.url(url).param();
            params.filter = filters;

            newUrl += '?' + $.param(params);
            jqTable.fnReloadAjax(newUrl);
        });
    }

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
