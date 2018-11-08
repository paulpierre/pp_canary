/*
 * This file contains global settings for Dashboard.
 * 1. Urls.
 * 2. Date today and standard periods.
 */


/*
 * Choose correct API url for sending requests
 * PRO_URL -> production
 * LOC_URL -> local for testing.
 */
var PRO_URL = 'http://api.########/';
var LOC_URL = 'http://########/';
if(location.hostname=='dashboard.myshopify.eu'){
    var API_URL = LOC_URL;
} else {
    var API_URL = PRO_URL;
}

function addLeadZero(value)
{
    if(parseInt(value) < 10)
    {
        var result = '0' + value;        
    }
    else
    {
        var result = value;
    }
    
    return result;
}

/*
 * Set date used in dashboard.
 * DATE_TODAY = today
 * DATE_MINUS_SEVEN = today - 7 days
 * DATE_MINUS_FOURTEEN = today - 14 days
 * DATE_MINUS_TWENTYONE = today - 21 days
 * DATE_MINUS_TWENTYEIGHT = today - 28 days
 * DATE_MINUS_ONE_MONTH = today - 1 month
 * DATE_MINUS_TWO_MONTHS = today - 2 months
 * DATE_MINUS_THREE_MONTHS = today - 3 months
 */
var dateobj = new Date();
var DATE_TODAY = dateobj.getFullYear() + "-" + addLeadZero((dateobj.getMonth()+1)) + "-" + addLeadZero(dateobj.getDate());
var DATE_TIME_NOW = dateobj.getFullYear() + "-" + addLeadZero((dateobj.getMonth()+1)) + "-" + addLeadZero(dateobj.getDate()) + " " + addLeadZero(dateobj.getHours()) + ":" + addLeadZero(dateobj.getMinutes()) + ":" + addLeadZero(dateobj.getSeconds());
var DATE_TIME_NOW_FILE = dateobj.getFullYear() + "" + addLeadZero((dateobj.getMonth()+1)) + "" + addLeadZero(dateobj.getDate()) + "" + addLeadZero(dateobj.getHours()) + "" + addLeadZero(dateobj.getMinutes()) + "" + addLeadZero(dateobj.getSeconds());

var dateobj_minusseven = new Date(dateobj);
dateobj_minusseven.setDate(dateobj_minusseven.getDate() - 7 + 1);
var date_minusseven = new Date(dateobj_minusseven);
var DATE_MINUS_SEVEN = date_minusseven.getFullYear() + "-" + addLeadZero((date_minusseven.getMonth()+1)) + "-" + addLeadZero(date_minusseven.getDate());

var dateobj_minusfourteen = new Date(dateobj);
dateobj_minusfourteen.setDate(dateobj_minusfourteen.getDate() - 14 + 1);
var date_minusfourteen = new Date(dateobj_minusfourteen);
var DATE_MINUS_FOURTEEN = date_minusfourteen.getFullYear() + "-" + addLeadZero((date_minusfourteen.getMonth()+1)) + "-" + addLeadZero(date_minusfourteen.getDate());

var dateobj_minustwentyone = new Date(dateobj);
dateobj_minustwentyone.setDate(dateobj_minustwentyone.getDate() - 21 + 1);
var date_minustwentyone = new Date(dateobj_minustwentyone);
var DATE_MINUS_TWENTYONE = date_minustwentyone.getFullYear() + "-" + addLeadZero((date_minustwentyone.getMonth()+1)) + "-" + addLeadZero(date_minustwentyone.getDate());

var dateobj_minustwentyeight = new Date(dateobj);
dateobj_minustwentyeight.setDate(dateobj_minustwentyeight.getDate() - 28 + 1);
var date_minustwentyeight = new Date(dateobj_minustwentyeight);
var DATE_MINUS_TWENTYEIGHT = date_minustwentyeight.getFullYear() + "-" + addLeadZero((date_minustwentyeight.getMonth()+1)) + "-" + addLeadZero(date_minustwentyeight.getDate());

var dateobj_minusonemonth = new Date(dateobj);
dateobj_minusonemonth.setMonth(dateobj_minusonemonth.getMonth() - 1);
var DATE_MINUS_ONE_MONTH = dateobj_minusonemonth.getFullYear() + "-" + addLeadZero((dateobj_minusonemonth.getMonth()+1)) + "-" + addLeadZero(dateobj_minusonemonth.getDate());

var dateobj_minustwomonths = new Date(dateobj);
dateobj_minustwomonths.setMonth(dateobj_minustwomonths.getMonth() - 2);
var DATE_MINUS_TWO_MONTHS = dateobj_minustwomonths.getFullYear() + "-" + addLeadZero((dateobj_minustwomonths.getMonth()+1)) + "-" + addLeadZero(dateobj_minustwomonths.getDate());

var dateobj_minusthreemonths = new Date(dateobj);
dateobj_minusthreemonths.setMonth(dateobj_minusthreemonths.getMonth() - 3);
var DATE_MINUS_THREE_MONTHS = dateobj_minusthreemonths.getFullYear() + "-" + addLeadZero((dateobj_minusthreemonths.getMonth()+1)) + "-" + addLeadZero(dateobj_minusthreemonths.getDate());

var dateobj_minussixmonths = new Date(dateobj);
dateobj_minussixmonths.setMonth(dateobj_minussixmonths.getMonth() - 6);
var DATE_MINUS_SIX_MONTHS = dateobj_minussixmonths.getFullYear() + "-" + addLeadZero((dateobj_minussixmonths.getMonth()+1)) + "-" + addLeadZero(dateobj_minussixmonths.getDate());

var DATE_TODAY_NAME = 'Today';
var DATE_MINUS_SEVEN_NAME = '- 7 days';
var DATE_MINUS_FOURTEEN_NAME = '- 14 days';
var DATE_MINUS_TWENTYONE_NAME = '- 21 days';
var DATE_MINUS_TWENTYEIGHT_NAME = '- 28 days';
var DATE_MINUS_ONE_MONTH_NAME = '- 1 month';
var DATE_MINUS_TWO_MONTHS_NAME = '- 2 months';
var DATE_MINUS_THREE_MONTHS_NAME = '- 3 months';
var DATE_MINUS_SIX_MONTHS_NAME = '- 6 months';

/*
 * Variables describing selected period and value
 *     • GET_DATE_SELECTED_PERIOD_NAME
 *     • GET_DATE_SELECTED_PERIOD_VALUE
 *     • GET_DATE_SELECTED_PERIOD_RESULT
 */
var GET_DATE_SELECTED_PERIOD_NAME;
var GET_DATE_SELECTED_PERIOD_VALUE;
var GET_DATE_SELECTED_PERIOD_RESULT;
/*
 * Functions for calculating date.
 *     • getDateMinusDays(d) = today - (d)days
 *     • getDateMinusMonths(m) = today - m(months)
 *     • getDateFirstDayOfMonthMinusMonths(m) = first day of today - m(months)
 *     • getDateFirstDayOfThisMonth() = date of first day of this month
 *     • getDateLastDayOfLastMonth() = date of last day of last month
 *     • getDateFirstDayOfThisWeek() = date of last Sunday or today if it is Sunday
 *     • getDateLastDayOfLastWeek() = date of last Saturday
 *     • getDateFirstDayOfWeekMinustWeeks(w) = date of Sunday of last Sunday (or today if it is Sunday) - (w * 7) dyas
 */
function getDateToday(){
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateToday';
    GET_DATE_SELECTED_PERIOD_VALUE = 0;
    GET_DATE_SELECTED_PERIOD_RESULT = DATE_TODAY;
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateMinusDays(d){
    var dateobj_minus = new Date(dateobj);
    dateobj_minus.setDate(dateobj_minus.getDate() - d);
    var date_minus = new Date(dateobj_minus);
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateMinusDays';
    GET_DATE_SELECTED_PERIOD_VALUE = d;
    GET_DATE_SELECTED_PERIOD_RESULT = date_minus.getFullYear() + "-" + addLeadZero((date_minus.getMonth()+1)) + "-" + addLeadZero(date_minus.getDate());
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateMinusMonths(m){
    var dateobj_minus = new Date(dateobj);
    dateobj_minus.setMonth(dateobj_minus.getMonth() - m);
    var date_minus = new Date(dateobj_minus);
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateMinusMonths';
    GET_DATE_SELECTED_PERIOD_VALUE = m;
    GET_DATE_SELECTED_PERIOD_RESULT = date_minus.getFullYear() + "-" + addLeadZero((date_minus.getMonth()+1)) + "-" + addLeadZero(date_minus.getDate());
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateFirstDayOfMonthMinusMonths(m){
    var dateobj_minus = new Date(dateobj);
    dateobj_minus.setMonth(dateobj_minus.getMonth() - m);
    var date_minus = new Date(dateobj_minus);
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateFirstDayOfMonthMinusMonths';
    GET_DATE_SELECTED_PERIOD_VALUE = m;
    GET_DATE_SELECTED_PERIOD_RESULT = date_minus.getFullYear() + "-" + addLeadZero((date_minus.getMonth()+1)) + "-" + '01';
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateFirstDayOfThisMonth(){
    var dateobj_day = new Date(dateobj);
    dateobj_day.setDate(dateobj_day.getDate());
    var date_day = new Date(dateobj_day);
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateFirstDayOfThisMonth';
    GET_DATE_SELECTED_PERIOD_VALUE = 0;
    GET_DATE_SELECTED_PERIOD_RESULT = date_day.getFullYear() + "-" + addLeadZero((date_day.getMonth()+1)) + "-" + "01";
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateLastDayOfLastMonth(){
    var dateobj_day = new Date(Date.parse(getDateFirstDayOfThisMonth()));
    dateobj_day.setDate(dateobj_day.getDate() - 1);
    var date_day = new Date(dateobj_day);
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateLastDayOfLastMonth';
    GET_DATE_SELECTED_PERIOD_VALUE = 0;
    GET_DATE_SELECTED_PERIOD_RESULT = date_day.getFullYear() + "-" + addLeadZero((date_day.getMonth()+1)) + "-" + addLeadZero(date_day.getDate());
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateFirstDayOfThisWeek(){
    var d = new Date();
    var n = d.getDay();
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateFirstDayOfThisWeek';
    GET_DATE_SELECTED_PERIOD_VALUE = 0;
    GET_DATE_SELECTED_PERIOD_RESULT = getDateMinusDays(n);
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateLastDayOfLastWeek(){
    var d = new Date();
    var n = d.getDay() + 1;
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateLastDayOfLastWeek';
    GET_DATE_SELECTED_PERIOD_VALUE = 0;
    GET_DATE_SELECTED_PERIOD_RESULT = getDateMinusDays(n);
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

function getDateFirstDayOfWeekMinustWeeks(w){
    var d = new Date();
    var n = d.getDay() + (7 * w);
    GET_DATE_SELECTED_PERIOD_NAME = 'getDateFirstDayOfWeekMinustWeeks';
    GET_DATE_SELECTED_PERIOD_VALUE = w;
    GET_DATE_SELECTED_PERIOD_RESULT = getDateMinusDays(n);
    return GET_DATE_SELECTED_PERIOD_RESULT;
};

/*
 * Set date for landing report
 */

var REPORT_LANDING_PERIOD = DATE_MINUS_ONE_MONTH;


/*
 * Get current time
 */
var now = new Date();
var _now = [
  now.getFullYear(),
  '-',
  now.getMonth() + 1,
  '-',
  now.getDate(),
  ' ',
  now.getHours(),
  ':',
  now.getMinutes(),
  ':',
  now.getSeconds()
].join('');



/*
 * Colors pallete
 */

var C_RED       = '#E94858';
var C_YELLOW    = '#F3A32A';
var C_GREEN     = '#82BF6E';
var C_BLUE      = '#3CB4CB';
var C_TEAL      = '#16434B';
//https://color.adobe.com/pl/FINAL-CREATIVEMIND-COLOURS-color-theme-10361914/?showPublished=true

var C_D01        = '#800000';
var C_D02        = '#802000';
var C_D03        = '#804000';
var C_D04        = '#806000';
var C_D05        = '#808000';
var C_D06        = '#608000';
var C_D07        = '#408000';
var C_D08        = '#208000';
var C_D09        = '#008000';
var C_D10        = '#008020';
var C_D11        = '#008040';
var C_D12        = '#008060';
var C_D13        = '#008080';
var C_D14        = '#006080';
var C_D15        = '#004080';
var C_D16        = '#002080';
var C_D17        = '#000080';
var C_D18        = '#200080';
var C_D19        = '#400080';
var C_D20        = '#600080';
var C_D21        = '#800080';
var C_D22        = '#800060';
var C_D23        = '#800040';
var C_D24        = '#800020';
var C_D25        = '#800000';

//https://www.w3schools.com/colors/colors_picker.asp?colorhex=008080

var C_L01        = '#ff0000';
var C_L02        = '#ff4000';
var C_L03        = '#ff8000';
var C_L04        = '#ffbf00';
var C_L05        = '#ffd700';
var C_L06        = '#ffff00';
var C_L07        = '#bfff00';
var C_L08        = '#80ff00';
var C_L09        = '#40ff00';
var C_L10        = '#00ff00';
var C_L11        = '#00ff40';
var C_L12        = '#00ff80';
var C_L13        = '#00ffbf';
var C_L14        = '#00ffff';
var C_L15        = '#00bfff';
var C_L16        = '#0080ff';
var C_L17        = '#0040ff';
var C_L18        = '#0000ff';
var C_L19        = '#4000ff';
var C_L20        = '#8000ff';
var C_L21        = '#bf00ff';
var C_L22        = '#ff00ff';
var C_L23        = '#ff00bf';
var C_L24        = '#ff0080';
var C_L25        = '#ff0040';
var C_L25        = '#ff0000';
var C_L26        = '#ff0000';

//https://www.w3schools.com/colors/colors_picker.asp?colorhex=FFD700