

var thisSite = 'vendor_delivery_summary';
setHeaderMenu(thisSite);
/*
 * request results
 */
var RawJsonResultAll;
var RawJsonResult;

/*
 * current status of
 * chart and table
 */
var TableStatus = 0;
var ChartStatus = 0;

/*
 * Chart and Datatable
 */
var datatable;
var chart;

/*
 * variables for Datatable
 */
var DatatableColumns;

/*
 * variables for setting filter
 * for chart
 */
var ChartJson;
var ChartJsonAll
var ChartLabels = [];
var ChartValues = [];
var ChartFilter = {vendor_id:"",delivery_status:""};

/*
 * Selected filter indexes
 */
var HtmlVendorId = [];
var HtmlDeliveryStatus = [];


var DELIVERY_STATUS_LEGEND_UNKNOWN          = 'UNKNOWN';
var DELIVERY_STATUS_LEGEND_CONFIRMED        = 'CONFIRMED';
var DELIVERY_STATUS_LEGEND_INTRANSIT        = 'IN TRANSIT';
var DELIVERY_STATUS_LEGEND_OUTFORDELIVERY   = 'OUT FOR DELIVERY';
var DELIVERY_STATUS_LEGEND_DELIVERED        = 'DELIVERED';
var DELIVERY_STATUS_LEGEND_FAILURE          = 'FAILURE';
var DELIVERY_STATUS_LEGEND_NOTFOUND         = 'NOT FOUND';
var DELIVERY_STATUS_LEGEND_PICKUP           = 'PICKUP';
var DELIVERY_STATUS_LEGEND_ALERT            = 'ALERT';
var DELIVERY_STATUS_LEGEND_EXPIRED          = 'EXPIRED';


/*
 * Getting variables, construct requests
 * and sending requests.
 */
function createGET()
{
    var DateStart = document.getElementsByName('daterangepicker_start')[0].value;
    var DateEnd = document.getElementsByName('daterangepicker_end')[0].value;
    
    var ObjDateStart = new Date(DateStart);
    var ObjDateEnd = new Date(DateEnd);
    
    if(ObjDateStart > ObjDateEnd)
    {
        $("#small_modal_title").append('oho!!! no, no...')
        $("#small_modal_message").append('<h6>Back To The Future Error....</h6><p>Your start date is younger than end date.</p>')
        $("#small_modal").modal('show');
        return;
    }
    
    $('#' + thisSite + '_chart_container').empty(); // this is my <canvas> element
    $('#' + thisSite + '_chart_container').append('<canvas id="' + thisSite + '_chart"><canvas>');            
            
    
    var RequestUrl = API_URL + 'report/vendor/' + thisSite + '?date_start=' + DateStart + '&date_end=' + DateEnd;
    
    var MyResult = $.ajax({
        type: "GET",
        url: RequestUrl,        
        async: false,
        processData:'text',
        complete: function(response)
        {
            var obj     = {};
            var json    = response.responseText;
            obj         = JSON.parse(json);
            
            ChartJson = obj.data.report;
            
            var tmp_chart_json = new Array();
            var delivery_status;
            var DeliveryStatusLegend;
                
            ChartJson.forEach(function(Item){
                delivery_status = Item.delivery_status;
                DeliveryStatusLegend = '';
                
                if(delivery_status == 0){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_UNKNOWN;};
                if(delivery_status == 1){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_CONFIRMED;};
                if(delivery_status == 2){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_INTRANSIT;};
                if(delivery_status == 3){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_OUTFORDELIVERY;};
                if(delivery_status == 4){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_DELIVERED;};
                if(delivery_status == 5){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_FAILURE;};
                if(delivery_status == 6){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_NOTFOUND;};
                if(delivery_status == 7){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_PICKUP;};
                if(delivery_status == 8){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_ALERT;};
                if(delivery_status == 9){DeliveryStatusLegend = DELIVERY_STATUS_LEGEND_EXPIRED;};
                
                var sub_item = new Array();
                sub_item.push(Item);
                sub_item[0]['DeliveryStatusLegend'] = DeliveryStatusLegend;
                tmp_chart_json.push(Item);
            });
            ChartJson = tmp_chart_json;
            RawJsonResultAll = JSON.stringify(ChartJson); 
            RawJsonResult = RawJsonResultAll;
                        
            ChartFilter['vendor_id'] = "";
            ChartFilter['delivery_status'] = "";
            
            doTable();
            doChart(); 
        }
    });
    
    return MyResult;
}

function createFilters(array)
{
    
    HtmlVendorId = [];
    HtmlDeliveryStatus = [];
    
    $('#m_form_vendor').empty();
    $('#m_form_status').empty();
    
    array.forEach(function(Item){
                
        HtmlVendorId_index = -1;
        HtmlVendorId.forEach(function(subitem){
            if(subitem.index === Item.vendor_id){HtmlVendorId_index = 0;};
        });    

        HtmlDeliveryStatusIndex = -1
        HtmlDeliveryStatus.forEach(function(subitem){
            if(subitem.index === Item.delivery_status){HtmlDeliveryStatusIndex = 0;};
        }); 

        if(HtmlVendorId_index == -1)
        {
            HtmlVendorId.push({index:Item.vendor_id, value:Item.vendor_name});
        };
        if(HtmlDeliveryStatusIndex == -1)
        {
            HtmlDeliveryStatus.push({index:Item.delivery_status, value:Item.DeliveryStatusLegend});
        };
    });
    
    HtmlVendorId = sortAssocArrayByStrValue(HtmlVendorId);
    HtmlDeliveryStatus = sortAssocArrayByNumIndex(HtmlDeliveryStatus);

    
    $('#m_form_vendor').append('<option value="">All</option>');
    $('#m_form_status').append('<option value="">All</option>');
    
    var HtmlVendorId_selected;
    var HtmlDeliveryStatus_selected;
    
    HtmlVendorId.forEach(function(Item){
        
        if(parseInt(Item.index) === parseInt(ChartFilter['vendor_id']))
        {
            HtmlVendorId_selected = ' selected';
        }
        else
        {
            HtmlVendorId_selected = '';
        }
        $('#m_form_vendor').append('<option value="' + Item.index + '"' + HtmlVendorId_selected + '>' + Item.value + '</option>');
    });
    HtmlDeliveryStatus.forEach(function(Item){        
        if(parseInt(Item.index) === parseInt(ChartFilter['delivery_status']))
        {
            HtmlDeliveryStatus_selected = ' selected';
        }
        else
        {
            HtmlDeliveryStatus_selected = '';
        }
        $('#m_form_status').append('<option value="' + Item.index + '"' + HtmlDeliveryStatus_selected + '>' + Item.value + '</option>');
    });
    
    $('#m_form_vendor').selectpicker('refresh');
    $('#m_form_status').selectpicker('refresh');
    
    return;
}

/*
 * D A T A T A B L E
 */
DatatableColumns = [{
                                    field: "vendor_id",
                                    title: "Vendor ID",
                                    sortable: 'asc',
                                    selector: false,
                                    type: 'number',
                                    textAlign: 'center'
                            },{
                                    field: "vendor_name",
                                    title: "Vendor Name",
                                    sortable: 'asc',
                                    selector: false,
                                    type: 'number',
                                    textAlign: 'center'
                            },{
                                    field: "delivery_status",
                                    title: "Delivery Status",
                                    sortable: 'asc',
                                    selector: false,
                                    type: 'number',
                                    textAlign: 'center'
                            },{
                                    field: "DeliveryStatusLegend",
                                    title: "Delivery Status Description",
                                    selector: false,
                                    textAlign: 'center'
                            }, {
                                    field: "count",
                                    title: "Count",
                                    type: 'number',
                                    textAlign: 'center'
                            }];
function doTable()
{    
    var DatatableDataLocalDemo = function () {
                        
            var reportTable = function () {
                    var DataJsonArray = JSON.parse(RawJsonResult);

                    datatable = $('.m_datatable').mDatatable({
                            
                            data: {
                                    type: 'local',
                                    source: DataJsonArray,
                                    pageSize: 10
                            },

                            layout: {
                                    theme: 'default',
                                    class: '',
                                    scroll: false,
                                    footer: false 
                            },
                            
                            serverSorting: false,
                            sortable: true,
                            dom: 'Bfrtip',
                            columns: DatatableColumns
                    });
                    
                    var query = datatable.getDataSourceQuery();
                    
                    $('#m_form_vendor').on('change', function() {
                        var query = datatable.getDataSourceQuery();
                        query.vendor_id = $(this).val().toLowerCase();

                        ChartFilter['vendor_id'] = query.vendor_id;
                        $('#' + thisSite + '_chart_container').empty();
                        $('#' + thisSite + '_chart_container').append('<canvas id="' + thisSite + '_chart"><canvas>');  

                        datatable.setDataSourceQuery(query);
                        datatable.load();

                        doChart();
                    }).val(typeof query.Status !== 'undefined' ? query.Status : '');

                    $('#m_form_status').on('change', function() {
                        var query = datatable.getDataSourceQuery();
                        query.delivery_status = $(this).val().toLowerCase();             
                        
                        ChartFilter['delivery_status'] = query.delivery_status;
                        $('#' + thisSite + '_chart_container').empty();
                        $('#' + thisSite + '_chart_container').append('<canvas id="' + thisSite + '_chart"><canvas>');  
                      
                        datatable.setDataSourceQuery(query);
                        datatable.load();

                        doChart();
                    }).val(typeof query.Type !== 'undefined' ? query.Type : '');
                    
                    $('#m_form_vendor, #m_form_status').selectpicker();

            };

            return {
                    init: function () {
                            reportTable();
                    }
            };
    }();
    
    if(TableStatus == 1)
    {
        datatable.destroy();
    }
    else
    {
        TableStatus = 1;
    }
    
    setTimeout(function(){
        DatatableDataLocalDemo.init();
    }, 100);
};


/*
 * C H A R T
 */

var doChart = function() {
    
    ChartLabels = new Array();
    ChartValues = new Array();
    ChartLabels = [];
    for (i = 0; i < 10; i++) {
        ChartValues[i] = [];
    }
    
    /*
     * Build Arrays for chart and filters
     * 
     */
    var ItemIndex;
    var FilterValues = [];
    if(ChartFilter['vendor_id'] === "")
    {
        if(ChartFilter['delivery_status'] === "")
        {
            ChartJson.forEach(function(Item) {
                var VendorName = Item.vendor_name;
                var DeliveryStatus = Item.delivery_status;
                
                ItemIndex = ChartLabels.indexOf(VendorName);

                if(ItemIndex === -1)
                {
                    FilterValues.push(Item);
                    ChartLabels.push(VendorName);
                    ItemIndex = ChartLabels.indexOf(VendorName);
                    for (i = 0; i < 10; i++) {
                        var ValueToPush = new Array();                
                        ValueToPush = ChartValues[i];
                        ValueToPush.push(0);
                        ChartValues[i] = ValueToPush;
                    }
                    ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);            
                }
                else
                {
                    FilterValues.push(Item);
                    ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);
                }
            });
        }
        else
        {
            ChartJson.forEach(function(Item) {
                var VendorName = Item.vendor_name;
                var DeliveryStatus = Item.delivery_status;
                
                if(parseInt(DeliveryStatus) == parseInt(ChartFilter['delivery_status']))
                {
                    ItemIndex = ChartLabels.indexOf(VendorName);

                    if(ItemIndex === -1)
                    {
                        FilterValues.push(Item);
                        ChartLabels.push(VendorName);
                        ItemIndex = ChartLabels.indexOf(VendorName);
                        for (i = 0; i < 10; i++) {
                            var ValueToPush = new Array();                
                            ValueToPush = ChartValues[i];
                            ValueToPush.push(0);
                            ChartValues[i] = ValueToPush;
                        }
                        ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);            
                    }
                    else
                    {
                        FilterValues.push(Item);
                        ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);
                    }
                }
            });
        }
    }
    else
    {
        if(ChartFilter['delivery_status'] === "")
        {
            ChartJson.forEach(function(Item) {
                var VendorName = Item.vendor_name;
                var vendor_id = Item.vendor_id;
                var DeliveryStatus = Item.delivery_status;
                
                if(parseInt(vendor_id) == parseInt(ChartFilter['vendor_id']))
                {
                    ItemIndex = ChartLabels.indexOf(VendorName);
                    if(ItemIndex === -1)
                    {
                        FilterValues.push(Item);
                        ChartLabels.push(VendorName);
                        ItemIndex = ChartLabels.indexOf(VendorName);
                        for (i = 0; i < 10; i++) {
                            var ValueToPush = new Array();                
                            ValueToPush = ChartValues[i];
                            ValueToPush.push(0);
                            ChartValues[i] = ValueToPush;
                        }
                        ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);            
                    }
                    else
                    {
                        FilterValues.push(Item);
                        ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);
                    }
                }
            });
        }
        else
        {
            ChartJson.forEach(function(Item) {
                var VendorName = Item.vendor_name;
                var vendor_id = Item.vendor_id;
                var DeliveryStatus = Item.delivery_status;

                if(parseInt(vendor_id) === parseInt(ChartFilter['vendor_id']))
                {
                    if(parseInt(DeliveryStatus) == parseInt(ChartFilter['delivery_status']))
                    {
                        ItemIndex = ChartLabels.indexOf(VendorName);
                        
                        if(ItemIndex === -1)
                        {
                            FilterValues.push(Item);
                            ChartLabels.push(VendorName);
                            ItemIndex = ChartLabels.indexOf(VendorName);
                            for (i = 0; i < 10; i++) {
                                var ValueToPush = new Array();                
                                ValueToPush = ChartValues[i];
                                ValueToPush.push(0);
                                ChartValues[i] = ValueToPush;
                            }
                            ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);            
                        }
                        else
                        {
                            FilterValues.push(Item);
                            ChartValues[DeliveryStatus][ItemIndex] = parseInt(Item.count);
                        }
                    }
                }
            });
        }
    }
    
    createFilters(FilterValues);
    
    var ChartDataAll = new Array;                
    ChartDataAll.push({
                            label: 'Unknown',
                            backgroundColor: C_D19,
                            data: ChartValues[0]
                      });               
    ChartDataAll.push({
                            label: 'Confirmed',
                            backgroundColor: C_D17,
                            data: ChartValues[1]
                      });               
    ChartDataAll.push({
                            label: 'In Transit',
                            backgroundColor: C_D15,
                            data: ChartValues[2]
                      });               
    ChartDataAll.push({
                            label: 'Out Of Delivery',
                            backgroundColor: C_D13,
                            data: ChartValues[3]
                      });               
    ChartDataAll.push({
                            label: 'Delivered',
                            backgroundColor: C_D11,
                            data: ChartValues[4]
                      });               
    ChartDataAll.push({
                            label: 'Failure',
                            backgroundColor: C_D09,
                            data: ChartValues[5]
                      });               
    ChartDataAll.push({
                            label: 'Not Found',
                            backgroundColor: C_D07,
                            data: ChartValues[6]
                      });               
    ChartDataAll.push({
                            label: 'Pickup',
                            backgroundColor: C_D05,
                            data: ChartValues[7]
                      });               
    ChartDataAll.push({
                            label: 'Alert',
                            backgroundColor: C_D03,
                            data: ChartValues[8]
                      });               
    ChartDataAll.push({
                            label: 'Expired',
                            backgroundColor: C_D01,
                            data: ChartValues[9]
                      });

    var ChartDataFiltered = new Array();
    
    if(ChartFilter['delivery_status'] === "")
    {
        ChartDataFiltered = ChartDataAll;
    }
    else
    {
        ChartDataFiltered.push(ChartDataAll[ChartFilter['delivery_status']]);
    }
    
    var chartData = {
        labels: ChartLabels,
        datasets:   ChartDataFiltered
    };
    
    var chartContainer = $('#' + thisSite + '_chart');

    if (chartContainer.length == 0) {
        return;
    }

    chart = new Chart(chartContainer, {
        type: 'bar',
        data: chartData,
        options: {
            title: {
                display: false,
            },
            tooltips: {
                intersect: false,
                mode: 'nearest',
                xPadding: 10,
                yPadding: 10,
                caretPadding: 10
            },
            legend: {
                display: true,
                position: 'left',
                fullHeight: true,
                reverse: true
            },
            responsive: true,
            maintainAspectRatio: false,
            barRadius: 2,
            scales: {
                xAxes: [{
                    display: true,
                    stacked: true,
                    gridLines: {
                                display: false,
                                drawBorder: true,
                                drawOnChartArea: false,
                            }
                }],
                yAxes: [{
                    display: true,
                    stacked: true,
                    gridLines: {
                                display: false,
                                drawBorder: true,
                                drawOnChartArea: false,
                            }
                }]
            },
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 20,
                    bottom: 0
                }
            }
        }
    });
    
    if(ChartStatus == 0)
    {
        ChartStatus = 1;
    }    
           
}

/*
 * 
 * D A T E R A N G E P I C K E R
 * 
 */


var picker = $('#m_dashboard_daterangepicker');
var start = moment().subtract(30,'day');
var end = moment();

function cb(start, end, label) {
    var Title = '';
    var Range = '';

    if ((end - start) < 100) {
        Title = 'Today:';
        Range = start.format('MMM D');
    } else if (label == 'Yesterday') {
        Title = 'Yesterday:';
        Range = start.format('MMM D');
    } else {
        Range = start.format('MMM D') + ' - ' + end.format('MMM D');
    }

    picker.find('.m-subheader__daterange-date').html(Range);
    picker.find('.m-subheader__daterange-title').html(Title);
}

picker.daterangepicker({
    showWeekNumbers: true,
    opens: 'left',
    ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'Last 60 Days': [moment().subtract(59, 'days'), moment()],
        'Last 90 Days': [moment().subtract(89, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    locale: {
                format: 'YYYY-MM-DD',
        },
    function (start) {
        startdate = start.format('YYYY-MM-DD')
    },
    startDate: REPORT_LANDING_PERIOD,
    endDate: DATE_TODAY
}, cb);

/*
 * L I S T E N E R S
 */

/*
 * Export To Excel
 */
$("#export_excel_button").click(function(){
    $("table").table2excel_all({
            exclude: ".noExl",
            name: "Excel Document Name",
            filename: thisSite + '_' + DATE_TIME_NOW_FILE
    }); 
});

/*
 * DateRangePicker
 */
$( ".applyBtn" ).click(function() {
    createGET();
    $("#export_buttons_container").show();
});

$( "[data-range-key]" ).click(function() {
    createGET();
    $("#export_buttons_container").show();
});

$( "#m_dashboard_daterangepicker" ).click(function() {
    $( ".calendar" ).show();
});

//Initialize DateRangePicker
$( document ).ready(function() {
    cb(start, end, '');
});


/*
 * Methods for sorting object arrays
 */

function sortAssocArrayByStrValue(array)
{
    array.sort(compare);
    function compare(a,b) {
      if (a.value < b.value)
        return -1;
      if (a.value > b.value)
        return 1;
      return 0;
    }
    return array;
}

function sortAssocArrayByNumIndex(array)
{
    array.sort(function(a, b){return a.index - b.index});
    return array;
}



//Initialize landing report
$( document ).ready(function() {
  $('input[name=daterangepicker_start]').val(DATE_MINUS_ONE_MONTH);
  $('input[name=daterangepicker_end]').val(DATE_TODAY);
  createGET();
  $("#export_buttons_container").show();
});