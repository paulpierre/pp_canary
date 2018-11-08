/** --------------
 *  THIS SITE NAME
 *  -------------- */
var thisSite = 'tracking_unfulfillment';

/** +----------+
 *  | NAV MENU |
 *  +----------+
 *
 *  DESCRIPTION:
 *  Create site's Navigation Menu
 *
 */
setHeaderMenu(thisSite);

/** ----------
 *  CONSTNACES
 *  ---------- */
var ERROR_SHEET_PARSING_NO_ERROR = 'SUCCESS'; //0
var ERROR_SHEET_PARSING_ORDER_RECEIPT_ID_NOT_FOUND = 'NO ORDER RECEIPT ID'; //1
var ERROR_SHEET_PARSING_SKU_NOT_FOUND = 'NO ORDER RECEIPT ID'; //2
var ERROR_SHEET_PARSING_TRACKING_NUMBER_NOT_FOUND = 'NO TRACKING NUMBER'; //3
var ERROR_SHEET_PARSING_ORDER_FULFILLMENT_NUMBER_NOT_FOUND = 'NO FULFILLMENT ID'; //4
var ERROR_SHEET_FILE_ERROR = ''; //5

var SHEET_STATUS_UNKNOWN = 'UNKNOWN'; //0
var SHEET_STATUS_SUCCESS = 'SUCCESS'; //1
var SHEET_STATUS_WARNING = 'WARNING'; //2
var SHEET_STATUS_FAILURE = 'FAILURE'; //3

var ERROR_ROW_PARSING_NO_ERROR = 'NO ERROR'; //0
var ERROR_ROW_PARSING_ORDER_RECEIPT_ID_NOT_FOUND = 'NO RECEIPT ID'; //1
var ERROR_ROW_PARSING_SKU_NOT_FOUND = 'NO SKU'; //2
var ERROR_ROW_PARSING_TRACKING_NUMBER_NOT_FOUND = 'NO TRACKING NUMBER'; //3
var ERROR_ROW_PARSING_FULFILLMENT_ID_NOT_FOUND = 'NO FULFILLMENT ID'; //4
var ERROR_ROW_PARSING_ORDER_ID_NOT_FOUND = 'NO ORDER ID'; //5
var ERROR_ROW_PARSING_ITEM_NOT_FOUND = 'NO ITEM' //6
var ERROR_ROW_PARSING_NO_RECEIPT_ID_NO_TRACKING_NUMBER = 'NO RECEIPT ID - NO TRACKING NUMBER' //7

var ROW_STATUS_UNKNOWN = 'UNKNOWN'; //0
var ROW_STATUS_SUCCESS = 'SUCCESS'; //1
var ROW_STATUS_WARNING = 'WARNING'; //2
var ROW_STATUS_FAILURE = 'FAILURE'; //3

var ROW_ACTION_NONE = 'NO ACTION'; //0
var ROW_ACTION_NONE_DATA_UP_TO_DATE = 'NO ACTION - DATA UP TO DATE'; //1
var ROW_ACTION_CREATE_TRACKING = 'CREATE TRACKING'; //2
var ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING = 'UPDATE FULFILLMENT AND CREATE TRACKING'; //3
var ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING = 'CREATE FULFILLMENT AND CREATE TRACKING'; //4
var ROW_ACTION_UNFULFILLMENT = 'UNFULFILL'; //5


/** --------------------
 *  SET VENDOR VARIABLES
 *  -------------------- */
var vendor;
var vendor_id = 0;
var safe_mode = 1;
var url = '';
/** --------------------
 *  NOTIFY VARIABLES
 *  -------------------- */
var notify_content = {};
notify_content.message = 'Mesage';
notify_content.title = 'Title';
notify_content.icon = 'icon ' + 'la la-cloud-download';
notify_content.url = 'www.omgtrue.com';
notify_content.target = '_blank';

var notify_type = 'secondary';

/** --------------------
 *  DEFINE SHEETS STATUSES
 *  -------------------- */

var sheets_statuses = {
                        0: 'UNKNOWN',
                        1: 'SUCCESS',
                        2: 'WARNING',
                        3: 'ERROR'
                    }
var sheet_errors_statuses = {
                        1: 'order not found',
                        2: 'tracking number not found',
                        3: 'fulfillment not found',
                        4: 'file error',
                    }
/** -------------
 *  EXCEL RESULTS
 *  ------------- */                
var summary;
var sheets;
var data;

var result_vendor_id;
var result_file_name;
/** -----
 *  TABLE
 *  ----- */
var grid;
var dataView;

/** -------------
 *  SUMMARY TABLE
 *  ------------- */
var data_table = [];

/** ------
 *  TABLES
 *  ------ */
var shhets_tables;


/** +-------------------+
 *  | SEND CONFIRMATION |
 *  +-------------------+
 *
 *  DESCRIPTION:
 *  Send confirmation of sheet parsing
 */
function sendConfirmation(){
    console.log('data=' + JSON.stringify(data) + '&vendor_id=' + result_vendor_id);
    var ajax_data = 'data=' + JSON.stringify(data) + '&vendor_id=' + result_vendor_id;    
    var ajax_url = API_URL + 'vendor/untracking_confirm/' + result_vendor_id;
    console.log(ajax_data);
    console.log(data);
    console.log(ajax_url);
    
    return $.ajax({
        type: "POST",
        data: ajax_data,
        url: ajax_url,        
        async: true
    });
}

/** +-----------------+
 *  | DISPLAY RESULTS |
 *  +-----------------+
 *
 *  DESCRIPTION:
 *  Display results of parsing sheets
 */
function displayResults(response){  
    
    var obj     = {};
    try {
        obj     = JSON.parse(response);
    } catch(e) {
        console.log(response);
        var n = response.indexOf("{");
        var l = response.length;
        
        $("#modal_lg_title").empty();
        $("#modal_lg_message").empty();
        $("#modal_lg_footer").empty();
        $("#modal_lg_title").append('Info for Admin');
        $("#modal_lg_message").append("<strong>Copy text below and send it to Rafal.</strong><br>We are grabing information about app still<br>" + response.slice(0, n-1));
        $("#modal_lg").modal('show');
        
        obj     = JSON.parse(response.slice(n, l));
        return;
    }
    
    var code    = obj.code;
    var msg     = obj.msg;
    var result  = obj.result;    
    
    if(code == 1){
        
        summary     = result.summary;
        sheets      = result.sheets;
        data        = result.data;        
        
        result_vendor_id   = summary.vendor_id;
        result_file_name   = summary.name;
        var timestamp   = summary.timestamp;
        
        var i = 0;
        for(x in data){
            for(y in data[x]){
                data_table[i] = data[x][y];
                data_table[i]['id'] = i;
                i++;
            }
        }
        shets_tables = sheets;
        
        
        $('#parsing_summary_row').slideDown("medium", function() {
            $('#summary_table_row').slideDown("medium", function() {                        

                $('#sheets_total').text(summary.sheets.total);
                $('#sheets_success').text(summary.sheets.success);
                $('#sheets_warning').text(summary.sheets.warning);
                $('#sheets_failure').text(summary.sheets.failure);
                $('#sheets_unknown').text(summary.sheets.unknown);

                $('#rows_total').text(summary.rows.total);
                $('#rows_success').text(summary.rows.success);
                $('#rows_warning').text(summary.rows.warning);
                $('#rows_failure').text(summary.rows.failure);
                $('#rows_unknown').text(summary.rows.unknown);

                $('#rows_total_chart').text(summary.rows.total);
                chart_series[0]['value'] = summary.rows.success;
                chart_series[1]['value'] = summary.rows.warning;
                chart_series[2]['value'] = summary.rows.failure;
                chart_series[3]['value'] = summary.rows.unknown;

                rowsParseWidget();                
                setDataTable();
                
                $('html, body').animate({
                    scrollTop: ($('#parsing_summary_row').offset().top - $('#m_header_nav').height() - 20) + 'px'
                }, 'medium');
            });
        });
    } else {
        notify_content.message = msg;
        notify_content.title = 'ERROR';
        notify_content.icon = 'icon ' + 'la la-warning';
        notify_content.url = '';
        notify_content.target = '_blank';
        notify_type = 'danger';
        runNotify();
    }
    
    return;
}
/** +-------------------+
 *  | DECLARE DATATABLE |
 *  +-------------------+
 *
 *  DESCRIPTION:
 *  Declare columns for table
 */
var checkboxSelector = new Slick.CheckboxSelectColumn({
  cssClass: "slick-cell-checkboxsel"
});
var data_table_columns = [
                            checkboxSelector.getColumnDefinition(),
                            {id:"order_status",             name:"Order Status",                field:"order_status",               cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.FulfillmentOrderStatus},
                            {id:"order_shopify_id",         name:"Shopify Order Id",            field:"order_shopify_id",           cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"fulfillment_shopify_id",   name:"Shopify Fulfillment Id",      field:"fulfillment_shopify_id",     cssClass:"",                    sortable:true,  width:240,  resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"tracking_number",          name:"Tracking Number",             field:"tracking_number",            cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"status",                   name:"Parse Status",                field:"status",                     cssClass:"cell-align-center",   sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.FulfillmentRowStatus},
                            {id:"action",                   name:"Action",                      field:"action",                     cssClass:"cell-align-center",   sortable:true,  width:360,  resizable:true,	 formatter:Slick.Formatters.FulfillmentRowAction},
                            {id:"error",                    name:"Error",                       field:"error",                      cssClass:"cell-align-center",   sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.FulfillmentRowError},
                            {id:"sheet",                    name:"Sheet Name",                  field:"sheet",                      cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.ValueBoldest},
                            {id:"row",                      name:"Sheet Row",                   field:"row",                        cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.ValueBoldest},
                            {id:"row_id",                   name:"Row Id",                      field:"row_id",                     cssClass:"",                    sortable:true,  width:80,   resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"order_receipt_id",         name:"Order Receipt Id",            field:"order_receipt_id",           cssClass:"",                    sortable:true,  width:180,  resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"order_id",                 name:"Order Id",                    field:"order_id",                   cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"fulfillment_id",           name:"Fulfillment Id",              field:"fulfillment_id",             cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"fulfillment_id",           name:"Tracking Id",                 field:"fulfillment_id",             cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
                            {id:"sku",                      name:"SKU",                         field:"sku",                        cssClass:"",                    sortable:true,  width:120,  resizable:true,	 formatter:Slick.Formatters.Value},
];

/** +---------------+
 *  | SET DATATABLE |
 *  +---------------+
 *
 *  DESCRIPTION:
 *  Create table with statuses of orders
 */

function setDataTable(){
    
    var sortcol = "order_status";
    var sortdir = 1;
    var options = {
        enableCellNavigation: true,
        editable: true,
        autoHeight:false,
        forceFitColumns: false,
        multiColumnSort: true
    };
    
    
    
    function groupBy() {
        dataView.setGrouping([
            {
                getter: "order_status",
                aggregators: [
                    
                ],
                formatter: (function (g) {
                                var value = g.value;
                                var color, text;
                                if (value == 0) {
                                    color = "secondary";
                                    text = ROW_STATUS_UNKNOWN;
                                } else if (value == 1) {
                                    color = "success";
                                    text = ROW_STATUS_SUCCESS;
                                } else if (value == 2) {
                                    color = "warning";
                                    text = ROW_STATUS_WARNING;
                                } else if (value == 3) {
                                    color = "danger";
                                    text = ROW_STATUS_FAILURE;
                                } else {
                                    color = "secondary";
                                    text = ROW_STATUS_UNKNOWN;
                                }
                                return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
                            }),
                collapsed: false,
                aggregateCollapsed: false,
                lazyTotalsCalculation: false
            },
            {
                getter: "order_shopify_id",
                aggregators: [
                    
                ],
                formatter: (function (g) {
                                return 'Shopify Order Id  <span style="font-weight:600">' + g.value + '</span>';
                            }),
                collapsed: false,
                aggregateCollapsed: false,
                lazyTotalsCalculation: false
            },
            {
                getter: "fulfillment_shopify_id",
                aggregators: [
                    
                ],
                formatter:(function (g) {
                                return 'Shopify Fulfillment Id  <span style="font-weight:600">' + g.value + '</span>';
                            }),
                collapsed: false,
                aggregateCollapsed: false,
                lazyTotalsCalculation: false
            }
        ]);
    }
    
    /*
     * Start initialize
     */

    var initialize = 0;
    if(typeof dataView == 'undefined'){
        initialize = 1;
    }
    
    /*
     * INITIALIZE GROUPING
     * -------------------
     */
    var groupItemMetadataProvider = new Slick.Data.GroupItemMetadataProvider({ checkboxSelect: true, checkboxSelectPlugin: checkboxSelector });

    /*
     * INITIALIZE DATAVIEW
     * -------------------
     */
    dataView = new Slick.Data.DataView({
            groupItemMetadataProvider: groupItemMetadataProvider,
            inlineFilters: true
    });
    
    /*
     * INITIALIZE GRID
     * ---------------
     */   
    grid = new Slick.Grid("#summary_table", dataView, data_table_columns, options);

    /*
     * REGISTER PLUGINS
     */
    grid.registerPlugin(groupItemMetadataProvider);
    grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));    
    grid.registerPlugin(checkboxSelector);
    //var pager = new Slick.Controls.Pager(dataView, grid, $("#pager"));
    var columnpicker = new Slick.Controls.ColumnPicker(data_table_columns, grid, options);

    /*
     * GRID AND DATAVIEW SYNCHRONISE
     * -----------------------------
     */
    
    grid.invalidate();
    grid.render();
    dataView.syncGridSelection(grid, true, true);

    
    /*
     * LISTENERS
     */
    grid.onSort.subscribe(function (e, args) {
        var cols = args.sortCols;
        dataView.sort(function (dataRow1, dataRow2) {
            for (var i = 0, l = cols.length; i < l; i++) {
                var field = cols[i].sortCol.field;
                var sign = cols[i].sortAsc ? 1 : -1;
                var value1 = dataRow1[field], value2 = dataRow2[field];
                var result = (value1 == value2 ? 0 : (value1 > value2 ? 1 : -1)) * sign;
                if (result != 0) {
                    return result;
                }
            }
          return 0;
        });
        grid.invalidate();
        grid.render();
    });

    dataView.onRowCountChanged.subscribe(function (e, args) {
            grid.updateRowCount();
    grid.render();
    });

    dataView.onRowsChanged.subscribe(function (e, args) {
            grid.invalidateRows(args.rows);
    grid.render();
    });
    
    function unsetFilter(){
        dataView.setFilter(function(item){
            return true;
        });
    };

    /*
     * UPDATE RESULTS
     * --------------
     */
    function updateResults(){
        dataView.beginUpdate();
        grid.setSelectedRows([]);
        dataView.setItems([]);
        dataView.setItems(data_table);
        unsetFilter();
        groupBy();
        dataView.endUpdate();
    }
    updateResults();    
    
    if(initialize == 1){
        initialize = 0;
        
    
        /*
         * LISTENERS
         */
        
        $("#group_groups").click(function(){
            if($(this).is(':checked')){
                $("#collapse_groups").prop("disabled", false);
                $("#collapse_groups").prop("checked", false);
                groupBy();
            } else {
                if(!$("#collapse_groups").prop("checked")){
                    $("#collapse_groups").prop("checked", true);
                }
                $("#collapse_groups").prop("checked", false);
                $("#collapse_groups").prop("disabled", true);
                dataView.setGrouping([]);
            }
        });
        $("#collapse_groups").click(function(){
            if($(this).is(':checked')){
                dataView.collapseAllGroups();
            } else {
                dataView.expandAllGroups();
            }
        });
    };
}


/** +--------------+
 *  | SUMMARY ROWS |
 *  +--------------+
 *
 *  DESCRIPTION:
 *  Shows parsed rows summary
 *  Based on Chartist plugin - https://gionkunz.github.io/chartist-js/index.html
 */
var chart_series = [{
                value: 0,
                className: 'custom',
                meta: {
                    color: mUtil.getColor('success')
                }
            },
            {
                value: 0,
                className: 'custom',
                meta: {
                    color: mUtil.getColor('warning')
                }
            },
            {
                value: 0,
                className: 'custom',
                meta: {
                    color: mUtil.getColor('danger')
                }
            },
            {
                value: 0,
                className: 'custom',
                meta: {
                    color: mUtil.getColor('metal')
                }
            }
        ];

var chart_summary;

var rowsParseWidget = function() {
    if ($('#m_chart_rows_parse').length == 0) {
        return;
    }
    
    chart_summary = new Chartist.Pie('#m_chart_rows_parse', {
        series: chart_series,
        labels: [1, 2, 3]
    }, {
        donut: true,
        donutWidth: 17,
        showLabel: false
    });

    chart_summary.on('draw', function(data) {
        if (data.type === 'slice') {
            // Get the total path length in order to use for dash array animation
            var pathLength = data.element._node.getTotalLength();
            // Set a dasharray that matches the path length as prerequisite to animate dashoffset
            data.element.attr({
                'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
            });

            // Create animation definition while also assigning an ID to the animation for later sync usage
            var animationDefinition = {
                'stroke-dashoffset': {
                    id: 'anim' + data.index,
                    dur: 1000,
                    from: -pathLength + 'px',
                    to: '0px',
                    easing: Chartist.Svg.Easing.easeOutQuint,
                    // We need to use `fill: 'freeze'` otherwise our animation will fall back to initial (not visible)
                    fill: 'freeze',
                    'stroke': data.meta.color
                }
            };

            // If this was not the first slice, we need to time the animation so that it uses the end sync event of the previous animation
            if (data.index !== 0) {
                animationDefinition['stroke-dashoffset'].begin = 'anim' + (data.index - 1) + '.end';
            }

            // We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
            data.element.attr({
                'stroke-dashoffset': -pathLength + 'px',
                'stroke': data.meta.color
            });

            // We can't use guided mode as the animations need to rely on setting begin manually
            // See http://gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
            data.element.animate(animationDefinition, false);
        }
    });

    // For the sake of the example we update the chart every time it's created with a delay of 8 seconds
    chart_summary.on('created', function() {
        if (window.__anim21278907124) {
            clearTimeout(window.__anim21278907124);
            window.__anim21278907124 = null;
        }
        window.__anim21278907124 = setTimeout(chart_summary.update.bind(chart_summary), 15000);
    });
}

$("#execute_fulfillment").click(function(event){
    event.preventDefault();
    sendConfirmation();
    destroy();
});

$("#cancel_fulfillment").click(function(event){
    event.preventDefault();
    destroy();
});

$("#safe_mode").click(function(event){
    if($(this).is(':checked')){
        safe_mode = 1;
    } else {
        safe_mode = 0;
        destroy();
    }
    Dropzone.options.mDropzoneXls.params.safe_mode = safe_mode;
});

$("[data-vendor]").click(function(event){
    event.preventDefault();
        
    if(vendor_id == 0){
        $('#dropzone-contaner').slideDown("medium", function() {});
    }
    
    vendor      = $(this).data('vendor');
    vendor_id   = $(this).data('vendor-id');
    $("#dropzone-vendor").removeClass("m--font-danger");
    $("#dropzone-vendor").addClass("m--font-info");
    $("#dropzone-vendor").text(vendor);
    $("#select_vendor_href").text(vendor);
    $("#dropzone-msg").text('Drop files here or click to upload.;');
    Dropzone.options.mDropzoneXls.params.vendor_id = vendor_id;
    Dropzone.options.mDropzoneXls.params.url = API_URL + 'vendor/untracking_parse/' + vendor_id;
    
    
    notify_content.message = 'Vendor selected';
    notify_content.title = vendor;
    notify_content.icon = 'icon ' + 'la la-truck';
    notify_content.url = '';
    notify_content.target = '_blank';
    notify_type = 'secondary';
    runNotify();
});


/** +----------+
 *  | DROP ZONE |
 *  +----------+
 *
 *  DESCRIPTION:
 *  Upload Files
 *
 */
Dropzone.options.mDropzoneXls = {
    method: "POST",
    paramName: "file", 
    maxFiles: 1,       // fiiles
    maxFilesize: 10,    // MB
    params: {
        "vendor_id":vendor_id,
        "url":url,
        "safe_mode":safe_mode
    },
    init : function() {
        mDropzoneXls = this;
        //Restore initial message when queue has been completed
        this.on("drop", function(event) {
            if(vendor_id < 1){                
                notify_content.message = 'Select vendor and drop file again.';
                notify_content.title = 'Vendor not seleted!';
                notify_content.icon = 'icon ' + 'la la-thumbs-up';
                notify_content.url = '';
                notify_content.target = '_blank';
                notify_type = 'danger';
                runNotify();
                done("Naha, you dn't");
            }
        });
        

    },
    //acceptedFiles: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel",
    success: function(file, response){
        this.removeAllFiles(true);
        if(safe_mode){
            displayResults(response);
        } else {
            destroy();
        }
    }
};

/** +-----------+
 *  | DESTROYER |
 *  +-----------+
 *
 *  DESCRIPTION:
 *  Clear all variables and destroy curent table and dropzone instance
 *      
 */
function destroy(){
    
    $('#summary_table_row').slideUp("medium", function() {
        $('#parsing_summary_row').slideUp("medium", function() {
            
            chart_summary = {};
            
            if(typeof(grid) != 'undefined'){
                grid.invalidateAllRows();
                dataView.beginUpdate();
                dataView.getItems().length = 0;
                dataView.endUpdate();
                grid.render();  
            }

            $("#collapse_groups").prop("disabled", false);
            $("#collapse_groups").prop("checked", true);    

            $("#group_groups").prop("disabled", false);
            $("#group_groups").prop("checked", true);

            $('#sheets_total').text('0');
            $('#sheets_success').text('0');
            $('#sheets_warning').text('0');
            $('#sheets_failure').text('0');
            $('#sheets_unknown').text('0');

            $('#rows_total').text('0');
            $('#rows_success').text('0');
            $('#rows_warning').text('0');
            $('#rows_failure').text('0');
            $('#rows_unknown').text('0');

            $('#rows_total_chart').text(0);
            chart_series[0]['value'] = 0;
            chart_series[1]['value'] = 0;
            chart_series[2]['value'] = 0;
            chart_series[3]['value'] = 0;
            
            return;
        });
    });
}

function runNotify(){
    
    var notify = $.notify(notify_content, { 
        type: notify_type,
        allow_dismiss: true,
        newest_on_top: true,
        mouse_over:  null,
        showProgressbar:  false,
        spacing: 30,                    
        timer: 3000,
        placement: {
            from: 'bottom', 
            align: 'right'
        },
        offset: {
            x: 30, 
            y: 30
        },
        delay: 1000,
        z_index: 10000,
        animate: {
            enter: 'animated ' + 'bounceInUp',
            exit: 'animated ' + 'bounceOutRight'
        },
        init: function() {}
    });
}
$("#collapse_groups").prop("disabled", false);
$("#collapse_groups").prop("checked", false);
$("#safe_mode").prop("checked", true);