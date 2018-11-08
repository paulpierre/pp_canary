

var thisSite = 'vendor_avg_delivery_time_by_country';
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
var ChartFilter = {vendor_id:"",country_id:""};
var Countries = new Array;

/*
 * Selected filter indexes
 */
var HtmlVendorId = [];
var HtmlCountry = [];

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
            var str ='';
            var i = 0;
            ChartJson.forEach(function(Item){
                var key;
                for(key in Item){
                    if(key == 'country')
                    {
                        if(Item[key] === '')
                        {
                            Item[key] = 'UNDEFINED';
                        }
                        if(Countries.length)
                        {
                            if(Countries.indexOf(Item[key]) === -1)
                            {
                                Countries.push(Item[key]);
                                Item['country_id'] = i;
                                ++i;
                            }
                            else
                            {
                                Item['country_id'] = Countries.indexOf(Item[key].toString());
                            }
                        }
                        else
                        {
                            Countries.push(Item[key]);
                            Item['country_id'] = i;
                            ++i;
                        }
                    }
                };
            });
            RawJsonResultAll = JSON.stringify(ChartJson); 
            RawJsonResult = RawJsonResultAll;
                        
            ChartFilter['vendor_id'] = "";
            ChartFilter['country_id'] = "";
            
            doTable();
            doChart(); 
        }
    });
    
    return MyResult;
}

function createFilters(array)
{
    
    HtmlVendorId = [];
    HtmlCountryId = [];
    

    $('#m_form_vendor').empty();
    $('#m_form_country').empty();
    
    
    array.forEach(function(Item){
                
        HtmlVendorId_index = -1;
        HtmlVendorId.forEach(function(subitem){
            if(subitem.index === Item.vendor_id){HtmlVendorId_index = 0;};
        });    
        
        HtmlCountryId_index = -1
        HtmlCountryId.forEach(function(subitem){
            if(subitem.index === Item.country_id){HtmlCountryId_index = 0;};
        }); 

        if(HtmlVendorId_index == -1)
        {
            HtmlVendorId.push({index:Item.vendor_id, value:Item.vendor_name});
        };
        if(HtmlCountryId_index == -1)
        {
            HtmlCountryId.push({index:Item.country_id, value:Item.country});
        };
    });
    
    HtmlVendorId = sortAssocArrayByStrValue(HtmlVendorId);
    HtmlCountryId = sortAssocArrayByStrValue(HtmlCountryId);

    
    $('#m_form_vendor').append('<option value="">All</option>');
    $('#m_form_country').append('<option value="">All</option>');
    
    var HtmlVendorId_selected;
    var HtmlCountry_selected;
    
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
    HtmlCountryId.forEach(function(Item){
        if(Item.index === parseInt(ChartFilter['country_id']))
        {
            HtmlCountry_selected = ' selected';
        }
        else
        {
            HtmlCountry_selected = '';
        }
        $('#m_form_country').append('<option value="' + Item.index + '"' + HtmlCountry_selected + '>' + Item.value + '</option>');
    });
    
    $('#m_form_vendor').selectpicker('refresh');
    $('#m_form_country').selectpicker('refresh');
    
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
                                    field: "country",
                                    title: "Country",
                                    sortable: 'asc',
                                    selector: false,
                                    type: 'number',
                                    textAlign: 'center'
                            }, {
                                    field: "count",
                                    title: "Count",
                                    selector: false,
                                    type: 'number',
                                    textAlign: 'center'
                            },{
                                    field: "days",
                                    title: "Days",
                                    selector: false,
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

                    $('#m_form_country').on('change', function() {
                        var query = datatable.getDataSourceQuery();
                        query.country_id = $(this).val().toLowerCase();           
                        
                        ChartFilter['country_id'] = query.country_id;
                        $('#' + thisSite + '_chart_container').empty();
                        $('#' + thisSite + '_chart_container').append('<canvas id="' + thisSite + '_chart"><canvas>');  
                      
                        datatable.setDataSourceQuery(query);
                        datatable.load();

                        doChart();
                    }).val(typeof query.Type !== 'undefined' ? query.Type : '');
                    
                    $('#m_form_vendor, #m_form_country').selectpicker();

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
    ChartValues = {};
    ChartLabels = [];
    for (i = 0; i < Countries.length; i++) {
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
        if(ChartFilter['country_id'] === "")
        {            
            var TmpCountries = new Array();
            var TmpChartJson = new Array();
            
            ChartJson.forEach(function(Item) {                
                var VendorName  = Item.vendor_name;
                
                ItemIndex = TmpCountries.indexOf(VendorName);
                
                if(ItemIndex === -1)
                {
                    TmpCountries.push(VendorName);
                    
                    TmpChartJson.push({
                                "vendor_id": Item['vendor_id'],
                                "vendor_name":Item['vendor_name'],
                                "country": Item['country'],
                                "count": Item['count'],
                                "days": parseInt(Item['days']) * parseInt(Item['count']),
                                "country_id": '0',
                                "total_avg": '0'
                    });
                }
                else
                {
                    TmpChartJson.forEach(function(SubItem) {
                        if(SubItem['vendor_name'] == VendorName)
                        {
                            var c_count     = parseInt(parseInt(SubItem['count']) + parseInt(Item['count']));
                            var c_days      = parseInt(parseInt(SubItem['days']) + (parseInt(Item['count']) * parseInt(Item['days'])));
                            var c_avg       = parseInt(c_days / c_count);
                            
                            SubItem['count']       = c_count;
                            SubItem['days']        = c_days;
                            SubItem['total_avg']   = c_avg;
                            
                        }
                    });
                }
            });
            
            
            TmpChartJson.forEach(function(Item) {
                var VendorName  = Item.vendor_name;
                var CountryId   = Item.country_id;
                
                ItemIndex = ChartLabels.indexOf(VendorName);

                if(ItemIndex === -1)
                {
                    FilterValues.push(Item);
                    ChartLabels.push(VendorName);
                    ItemIndex = ChartLabels.indexOf(VendorName);
                    for (i = 0; i < Countries.length; i++) {
                        var ValueToPush = new Array();                
                        ValueToPush = ChartValues[i];
                        ValueToPush.push(0);
                        ChartValues[i] = ValueToPush;
                    }
                    ChartValues[CountryId][ItemIndex] = parseInt(Item.total_avg);
                }
                else
                {
                    FilterValues.push(Item);
                    ChartValues[CountryId][ItemIndex] = parseInt(Item.total_avg);
                }
            });
            createFilters(ChartJson);
        }
        else
        {
            ChartJson.forEach(function(Item) {
                var VendorName  = Item.vendor_name;
                var CountryId   = Item.country_id;
                var i =0;
                if(parseInt(CountryId) == parseInt(ChartFilter['country_id']))
                {
                    ItemIndex = ChartLabels.indexOf(VendorName);

                    if(ItemIndex === -1)
                    {
                        FilterValues.push(Item);
                        ChartLabels.push(VendorName);
                        ItemIndex = ChartLabels.indexOf(VendorName);
                        for (i = 0; i < Countries.length; i++) {
                            var ValueToPush = new Array();                
                            ValueToPush = ChartValues[i];
                            ValueToPush.push(0);
                            ChartValues[i] = ValueToPush;
                        }
                        ChartValues[CountryId][ItemIndex] = parseInt(Item.days);
                    }
                    else
                    {
                        FilterValues.push(Item);
                        ChartValues[CountryId][ItemIndex] = parseInt(Item.days);
                    }
                }
            });
            createFilters(FilterValues);
        }
    }
    else
    {
        if(ChartFilter['country_id'] === "")
        {
            ChartValues = [];
            
            ChartJson.forEach(function(Item) {
                var VendorName  = Item.vendor_name;
                var vendor_id   = Item.vendor_id;
                var CountryId   = Item.country_id;
                var Country     = Item.country;
                
                if(parseInt(vendor_id) == parseInt(ChartFilter['vendor_id']))
                {
                    ItemIndex = ChartLabels.indexOf(Country);
                    if(ItemIndex === -1)
                    {
                        FilterValues.push(Item);
                        ChartLabels.push(Country);
                        ItemIndex = ChartLabels.indexOf(Country);
                        
                        ChartValues.splice(ItemIndex, 0, [Item.days]);
                    }
                    else
                    {
                        FilterValues.push(Item);
                        ChartValues.splice(ItemIndex, 0, [Item.days]);
                    }
                }
            });
            createFilters(FilterValues);
        }
        else
        {
            ChartJson.forEach(function(Item) {
                var VendorName = Item.vendor_name;
                var vendor_id = Item.vendor_id;
                var CountryId = Item.country_id;

                if(parseInt(vendor_id) === parseInt(ChartFilter['vendor_id']))
                {
                    if(CountryId == ChartFilter['country_id'])
                    {
                        ItemIndex = ChartLabels.indexOf(VendorName);
                        
                        if(ItemIndex === -1)
                        {
                            FilterValues.push(Item);
                            ChartLabels.push(VendorName);
                            ItemIndex = ChartLabels.indexOf(VendorName);
                            for (i = 0; i < Countries.length; i++) {
                                var ValueToPush = new Array();                
                                ValueToPush = ChartValues[i];
                                ValueToPush.push(0);
                                ChartValues[i] = ValueToPush;
                            }
                            ChartValues[CountryId][ItemIndex] = parseInt(Item.days);
                        }
                        else
                        {
                            FilterValues.push(Item);
                            ChartValues[CountryId][ItemIndex] = parseInt(Item.days);
                        }
                    }
                }
            });
            createFilters(FilterValues);
        }
    }
    
    
    
    var ChartDataFiltered = new Array();
    
    
    
    if(ChartFilter['vendor_id'] === "")
    {
        if(ChartFilter['country_id'] === "")
        {
            var key;
            for (key in ChartValues) {
                if(parseInt(ChartValues[key]) > 0)
                {
                    ChartDataFiltered.push({label: 'Total Avg.', backgroundColor: C_GREEN,data: ChartValues[key]});
                }
            }
            $("#chart_big_title").text('Vendor Total Avg.');
            $("#chart_small_title").text('Vendor Avg. Delivery Time by Country');
        }
        else
        {
            var key;
            for (key in ChartValues) {
                if(parseInt(ChartValues[key]) > 0)
                {
                    ChartDataFiltered.push({label: Countries[key],backgroundColor: C_YELLOW,data: ChartValues[key]});
                }
            }        
            $("#chart_big_title").text('Cumulative');
            $("#chart_small_title").text('Vendor Avg. Delivery Time by Country');
        }
    }
    else
    {
        if(ChartFilter['country_id'] === "")
        {
            var i = 0;
            var key;
            
            ChartDataFiltered.push({label: 'Country Avg.',backgroundColor: C_BLUE,data: ChartValues});
            $("#chart_big_title").text('Vendor Avg. By Country');
            $("#chart_small_title").text('Vendor Avg. Delivery Time by Country');
        }
        else
        {
            var key;
            for (key in ChartValues) {
                if(parseInt(ChartValues[key]) > 0)
                {
                    ChartDataFiltered.push({label: Countries[key],backgroundColor: C_GREEN,data: ChartValues[key]});
                }
            }        
            $("#chart_big_title").text('Cumulative');
            $("#chart_small_title").text('Vendor Avg. Delivery Time by Country');
        }
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
                display: false,
                position: 'left',
                fullHeight: true,
                reverse: true
            },
            responsive: true,
            maintainAspectRatio: false,
            barRadius: 0,
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
            exclude: "",
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