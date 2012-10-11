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
        var aData = (oSettings.sAjaxDataProp !== "") ? that.oApi
                ._fnGetObjectDataFn(oSettings.sAjaxDataProp)(json) : json;

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

            options.fnInitComplete = function() {
                //$('.timeago').timeago();
                //$table.find('[data-form-url]').dialogForm();
            };

            $table.dataTable(options);
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
