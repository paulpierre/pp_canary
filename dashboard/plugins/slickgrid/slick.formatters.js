/***
 * Contains basic SlickGrid formatters.
 * 
 * NOTE:  These are merely examples.  You will most likely need to implement something more
 *        robust/extensible/localizable/etc. for your use!
 * 
 * @module Formatters
 * @namespace Slick
 */

(function ($) {
  // register namespace
  $.extend(true, window, {
    "Slick": {
      "Formatters": {
        "PercentComplete"       : PercentCompleteFormatter,
        "PercentCompleteBar"    : PercentCompleteBarFormatter,
        "YesNo"                 : YesNoFormatter,
        "Checkmark"             : CheckmarkFormatter,
        "Checkbox"              : CheckboxFormatter,
        "Value"                 : ValueFormatter,
        "ValueBolder"           : ValueBolderFormatter,
        "ValueBoldest"          : ValueBoldestFormatter,
        "ValueBolderMetal"      : ValueBolderMetalFormatter,
        "ValueBoldestMetal"     : ValueBoldestMetalFormatter,
        "OrderStatus"           : OrderStatusFormatter,
        "DeliveryStatus"        : DeliveryStatusFormatter,
        "Vendor"                : VendorFormatter,
        "FinancialStatus"       : FinancialStatusFormatter,
        "IsItemRefunded"        : IsItemRefundedFormatter,
        "ItemValue"             : ItemValueFormatter,
        "PaymentGateway"        : PaymentGatewayFormatter,
        "OrderAlertStatus"      : OrderAlertStatusFormatter,
        "LoadSpinner"           : LoadSpinnerFormatter,
        "FulfillmentRowStatus"  : FulfillmentRowStatusFormatter,
        "FulfillmentRowAction"  : FulfillmentRowActionFormatter,
        "FulfillmentRowError"   : FulfillmentRowErrorFormatter,
        "FulfillmentOrderStatus": FulfillmentOrderStatusFormatter

      }
    }
  });
      
  function PercentCompleteFormatter(row, cell, value, columnDef, dataContext) {
    if (value == null || value === "") {
      return "-";
    } else if (value < 50) {
      return "<span style='color:red;font-weight:bold;'>" + value + "%</span>";
    } else {
      return "<span style='color:green'>" + value + "%</span>";
    }
  }

  function PercentCompleteBarFormatter(row, cell, value, columnDef, dataContext) {
    if (value == null || value === "") {
      return "";
    }

    var color;

    if (value < 30) {
      color = "red";
    } else if (value < 70) {
      color = "silver";
    } else {
      color = "green";
    }

    return "<span class='percent-complete-bar' style='background:" + color + ";width:" + value + "%'></span>";
  }

  function YesNoFormatter(row, cell, value, columnDef, dataContext) {
    return value ? "Yes" : "No";
  }

  function CheckboxFormatter(row, cell, value, columnDef, dataContext) {
    //return '<img class="slick-edit-preclick" src="../../assets/vendors/custom/slickgrid/images/' + (value ? "CheckboxY" : "CheckboxN") + '.png">';
    return value ? '<INPUT type="checkbox" value="true" class="editor-checkbox" hideFocus checked>':'<INPUT type="checkbox" value="false" class="editor-checkbox" hideFocus>';
  }

  function CheckmarkFormatter(row, cell, value, columnDef, dataContext) {
    return value ? "<img src='../../assets/vendors/custom/slickgrid/images/tick.png'>" : "";
  }  
  /*
   * 
   * Predefined for canary
   * 
   */
  function ValueFormatter(row, cell, value, columnDef, dataContext) {
    return value;
  }
  
  function ValueBolderFormatter(row, cell, value, columnDef, dataContext) {
    return '<span class="m--font-bolder">' + value + '</span>';
  }
  
  function ValueBoldestFormatter(row, cell, value, columnDef, dataContext) {
    return '<span class="m--font-boldest">' + value + '</span>';
  }
  
  function ValueBolderMetalFormatter(row, cell, value, columnDef, dataContext) {
    return '<span class="m--font-bolder m--font-metal">' + value + '</span>';
  }
  
  function ValueBoldestMetalFormatter(row, cell, value, columnDef, dataContext) {
    return '<span class="m--font-boldest m--font-metal">' + value + '</span>';
  }
  
  function DeliveryStatusFormatter(row, cell, value, columnDef, dataContext) {
        
    var color,text;
    
    if (value == 0) {
        color = "secondary";
        text = DELIVERY_STATUS_LEGEND_UNKNOWN;
    } else if (value == 1) {
        color = "info";
        text = DELIVERY_STATUS_LEGEND_CONFIRMED;
    } else if (value == 2) {
        color = "info";
        text = DELIVERY_STATUS_LEGEND_INTRANSIT;
    } else if (value == 3) {
        color = "warning";
        text = DELIVERY_STATUS_LEGEND_OUTFORDELIVERY;
    } else if (value == 4) {
        color = "success";
        text = DELIVERY_STATUS_LEGEND_DELIVERED;
    } else if (value == 5) {
        color = "danger";
        text = DELIVERY_STATUS_LEGEND_FAILURE;
    } else if (value == 6) {
        color = "danger";
        text = DELIVERY_STATUS_LEGEND_NOTFOUND;
    } else if (value == 7) {
        color = "warning";
        text = DELIVERY_STATUS_LEGEND_PICKUP;
    } else if (value == 8) {
        color = "warning";
        text = DELIVERY_STATUS_LEGEND_ALERT;
    } else if (value == 9) {
        color = "danger";
        text = DELIVERY_STATUS_LEGEND_EXPIRED;
    } else {
        color = "secondary";
        text = '';
    }
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }
  
  function VendorFormatter(row, cell, value, columnDef, dataContext) {
        
    var color,text,type;
    
    if (value == 0) {
        color = "warning";
        text = VENDOR_ID_0;
    } else if (value == 1) {
        color = "info";
        text = VENDOR_ID_1;
    } else if (value == 2) {
        color = "info";
        text = VENDOR_ID_2;
    } else if (value == 3) {
        color = "info";
        text = VENDOR_ID_3;
    } else if (value == 4) {
        color = "info";
        text = VENDOR_ID_4;
    } else if (value == 5) {
        color = "info";
        text = VENDOR_ID_5;
    } else if (value == 6) {
        color = "info";
        text = VENDOR_ID_6;
    } else if (value == 7) {
        color = "info";
        text = VENDOR_ID_7;
    } else {
        color = "secondary";
        text = 'EMPTY';
    }
    
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }
  
  
  function FinancialStatusFormatter(row, cell, value, columnDef, dataContext) {
        
    var color,text;
    
    if (value == 0) {
        color = "secondary";
        text = FINANCIAL_STATUS_0;
    } else if (value == 1) {
        color = "warning";
        text = FINANCIAL_STATUS_1;
    } else if (value == 2) {
        color = "info";
        text = FINANCIAL_STATUS_2;
    } else if (value == 3) {
        color = "warning";
        text = FINANCIAL_STATUS_3;
    } else if (value == 4) {
        color = "success";
        text = FINANCIAL_STATUS_4;
    } else if (value == 5) {
        color = "danger";
        text = FINANCIAL_STATUS_5;
    } else if (value == 6) {
        color = "danger";
        text = FINANCIAL_STATUS_6;
    } else if (value == 7) {
        color = "danger";
        text = FINANCIAL_STATUS_7;
    } else {
        color = "secondary";
        text = '';
    }
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }
  
  
  function IsOrderRefundedFormatter(row, cell, value, columnDef, dataContext) {
    var color,text;
    if (value == 0) {
        color = "secondary";
        text = REFUNDED_STATUS_NO_REFUND;
    } else if (value == 1) {
        color = "danger";
        text = REFUNDED_STATUS_FULL_REFUND;
    } else if (value == 2) {
        color = "warning";
        text = REFUNDED_STATUS_PARTIAL_REFUND;
    } else {
        color = "secondary";
        text = value;
    }
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }
  
  function OrderAlertStatusFormatter(row, cell, value, columnDef, dataContext) {
    
    var color,text;
    
    if (value == 0) {
        color = "secondary";
        text = ALERT_NOTIFICATION_STATUS_NONE;
    } else if (value == 1) {
        color = "success";
        text = ALERT_NOTIFICATION_STATUS_RESOLVED;
    } else if (value == 2) {
        color = "warning";
        text = ALERT_NOTIFICATION_STATUS_EXTENDED_NOT_FOUND;
    } else if (value == 3) {
        color = "info";
        text = ALERT_NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT;
    } else if (value == 4) {
        color = "warning";
        text = ALERT_NOTIFICATION_STATUS_CUSTOMER_PICKUP;
    } else if (value == 5) {
        color = "danger";
        text = ALERT_NOTIFICATION_STATUS_DELIVERY_FAILURE;
    } else if (value == 6) {
        color = "danger";
        text = ALERT_NOTIFICATION_STATUS_ALERT_CUSTOMS;
    } else {
        color = "secondary";
        text = value;
    }
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }  
  
  function IsItemRefundedFormatter(row, cell, value, columnDef, dataContext) {
    var color,text;
    
    if (value == 0) {
        color = "secondary";
        text = 'NOT REFUNDED';
    } else if (value == 1) {
        color = "danger";
        text = 'REFUNDED';
    } else {
        color = "secondary";
        text = 'UNDEFINED';
    }
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }  
  
  function ItemValueFormatter(row, cell, value, columnDef, dataContext) {
    var quantity = dataContext["item_quantity"];
    var price = dataContext["item_price"];
    var item_value = quantity * price;
    dataContext.item_value = item_value;
    //console.log(dataView.getRowById(row));
    //console.log(row + " | " + cell + " | " + value + " | " + JSON.stringify(columnDef) + " | " + JSON.stringify(dataContext));
    return item_value;
  }    
  
  function OrderStatusFormatter(row, cell, value, columnDef, dataContext) {
    var status, color, text;
    
    var open = dataContext["order_topen"] == '0000-00-00 00:00:00'?0:Date.parse(dataContext["order_topen"]);
    var close = dataContext["order_tclose"] == '0000-00-00 00:00:00'?0:Date.parse(dataContext["order_tclose"]);
    var cancel = dataContext["order_tcancel"] == '0000-00-00 00:00:00'?0:Date.parse(dataContext["order_tcancel"]);
    
    if(open > close && open > cancel){
        status = 1;
        color = 'info';
        text = 'OPENED';
    } else if(close > cancel){
        status = 2;
        color = 'success';
        text = 'CLOSED';
    } else if(cancel > open){
        status = 2;
        color = 'danger';
        text = 'CANCELLED';
    } else {
        status = 0;
        color = 'secondary';
        text = 'UNKNOWN';
    }
    
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }   
  
  function PaymentGatewayFormatter(row, cell, value, columnDef, dataContext) {
    var color,text;
    
    if (value == 0) {
        text = 'Unknown';
    } else if (value == 1) {
        text = 'Stripe';
    } else if (value == 2) {
        text = 'Pay-Pal';
    } else if (value == 3) {
        text = 'Shopify';
    } else {
        text = 'UNDEFINED';
    }
    return '<span data-value="' + value + '">' + text + '</span>';
  }
  
  function LoadSpinnerFormatter(row, cell, value, columnDef, dataContext) {
    var color,text,res;
    
    if (value == 0) {
        color = "danger";
        text = "NO FULFILLMENT ID";
        res = '<span class="m--font-' + color + '" >' + text + '</span>';
    } else if (value == 1) {
        color = "warning";
        text = "";
        res = '<div class="m-spinner m-spinner--' + color + ' m-spinner--sm"></div>';
    }  else if (value == 2) {
        color = "info";
        text = "STATUS THE SAME";
        res = '<span class="m--font-' + color + '" >' + text + '</span>';
    }  else if (value == 3) {
        color = "success";
        text = "STATUS CHANGED";
        res = '<span class="m--font-' + color + '" >' + text + '</span>';
    }  else if (value == 4) {
        color = "warning";
        text = "NOT REFRESHED";
        res = '<span class="m--font-' + color + '" >' + text + '</span>';
    } else {
        res = '';
    }
    return res;
  }
  
  
  function FulfillmentRowStatusFormatter(row, cell, value, columnDef, dataContext) {
        
    var color,text;
    
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
  }
  
  function FulfillmentRowActionFormatter(row, cell, value, columnDef, dataContext) {
        
    var color,text;
    
    if (value == 0) {
        color = "danger";
        text = ROW_ACTION_NONE;
    } else if (value == 1) {
        color = "secondary";
        text = ROW_ACTION_NONE_DATA_UP_TO_DATE;
    } else if (value == 2) {
        color = "success";
        text = ROW_ACTION_CREATE_TRACKING;
    }  else if (value == 3) {
        color = "info";
        text = ROW_ACTION_UPDATE_FULFILLMENT_CREATE_TRACKING;
    } else if (value == 4) {
        color = "warning";
        text = ROW_ACTION_CREATE_FULFILLMENT_CREATE_TRACKING;
    }  else if (value == 5) {
        color = "success";
        text = ROW_ACTION_UNFULFILLMENT;
    }  else {
        color = "danger";
        text = ROW_ACTION_NONE;
    }
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }  
  
  function FulfillmentRowErrorFormatter(row, cell, value, columnDef, dataContext) {
        
    var color,text;
    
    if (value == 0) {
        color = "";
        text = ERROR_ROW_PARSING_NO_ERROR;
        return;
    } else if (value == 1) {
        color = "danger";
        text = ERROR_ROW_PARSING_ORDER_RECEIPT_ID_NOT_FOUND;
    } else if (value == 2) {
        color = "danger";
        text = ERROR_ROW_PARSING_SKU_NOT_FOUND;
    } else if (value == 3) {
        color = "danger";
        text = ERROR_ROW_PARSING_TRACKING_NUMBER_NOT_FOUND;
    } else if (value == 4) {
        color = "danger";
        text = ERROR_ROW_PARSING_FULFILLMENT_ID_NOT_FOUND;
    }  else if (value == 5) {
        color = "danger";
        text = ERROR_ROW_PARSING_ORDER_ID_NOT_FOUND;
    } else if (value == 6) {
        color = "danger";
        text = ERROR_ROW_PARSING_ITEM_NOT_FOUND;
    }  else if (value == 7) {
        color = "danger";
        text = ERROR_ROW_PARSING_NO_RECEIPT_ID_NO_TRACKING_NUMBER;
    } else {
        color = "";
        text = ERROR_ROW_PARSING_NO_ERROR;
        return;
    }
    return '<span data-value="' + value + '" class="m-badge m-badge--' + color + ' m-badge--wide">' + text + '</span>';
  }  
  
  function FulfillmentOrderStatusFormatter(row, cell, value, columnDef, dataContext) {
        
    var text;
    
    if (value == 0) {
        color = "secondary";
        text = '<span class="m--font-' + color + '">' + ROW_STATUS_UNKNOWN + '</span>';
    } else if (value == 1) {
        color = "success";
        text = ROW_STATUS_SUCCESS;
    } else if (value == 2) {
        color = "warning";
        text = '<span class="m--font-' + color + '">' + ROW_STATUS_WARNING + '</span>';
    } else if (value == 3) {
        color = "danger";
        text = '<span class="m--font-' + color + '">' + ROW_STATUS_FAILURE + '</span>';
    } else {
        color = "secondary";
        text = ROW_STATUS_UNKNOWN;
    }
    
    return text;
  }
  
  
  /*
    
    var bage_small              = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR}">{TEXT}</span>';
    var bage_dot                = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR} m-badge--dot"></span>';
    var bage_wide               = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR} m-badge--wide">{TEXT}</span>';
    var bage_rounded            = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR} m-badge--wide m-badge--rounded">{TEXT}</span>';
    var bage_small_rounded      = '<span data-value="{VALUE}" class="m-badge m-badge--{COLOR}">{VALUE2}</span>' + ' - ' + '<span data-value="{VALUE3}" class="m-badge m-badge--secondary m-badge--wide m-badge--rounded">{TEXT}</span>';
    
    
    
   */
  
})(jQuery);
