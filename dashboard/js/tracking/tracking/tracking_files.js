/** --------------
 *  THIS SITE NAME
 *  -------------- */
var thisSite = 'tracking_files';

/** +----------+
 *  | NAV MENU |
 *  +----------+
 *
 *  DESCRIPTION:
 *  Create site's Navigation Menu
 *
 */
setHeaderMenu(thisSite);

/** -------
 *  API URL
 *  -------
 *
 *  DESCRIPTION:
 *  Create site's Navigation MenuChoose correct API url for sending requests
 *  PRO_URL -> production
 *  LOC_URL -> local for testing.
 *  
 */

var Treeview = function () {

    var tree = function () {
        $('#m_tree').jstree({
            "core" : {
                "themes" : {
                    "responsive": false
                }            
            },
            "types" : {
                "default" : {
                    "icon" : "fa fa-folder m--font-warning"
                },
                "file" : {
                    "icon" : "fa fa-file"
                }
            },
            "plugins": ["types"]
        });

        // handle link clicks in tree nodes(support target="_blank" as well)
        $('#m_tree').on('select_node.jstree', function(e,data) { 
            var link = $('#' + data.selected).find('a');
            if (link.attr("href") != "#" && link.attr("href") != "javascript:;" && link.attr("href") != "") {
                if (link.attr("target") == "_blank") {
                    link.attr("href").target = "_blank";
                }
                document.location.href = link.attr("href");
                return false;
            }
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            tree();
        }
    };
}();

function displayResults(response){
    var obj     = {};
    try {
        obj     = JSON.parse(response);
    } catch(e) {
        var n = response.indexOf("{");
        var l = response.length;
        
        $("#modal_sm_title").empty();
        $("#modal_sm_message").empty();
        $("#modal_sm_footer").empty();
        $("#modal_sm_title").append('Server error');
        $("#modal_sm_message").append(response.slice(0, n-1));
        $("#modal_sm").modal('show');
        
        obj     = JSON.parse(response.slice(n, l));
    }
    var code    = obj.code;
    var msg     = obj.msg;
    var data    = obj.data;
    
    if(code == 1){
        $("#vendors_tree").append(data);
    }
    
    Treeview.init();
    return;
}

function executeGET()
{
    var url = API_URL + 'vendor/tree/';    
    var MyResult = $.ajax({
        type: "GET",
        url: url,        
        async: false,
        processData:'text',
        complete: function(response)
        {
            displayResults(response.responseText);
        }
    });
    return MyResult;
}


jQuery(document).ready(function() {    
    executeGET();
});
