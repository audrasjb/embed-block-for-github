jQuery(document).ready( function () {
    embed_block_for_github_admin = new Embed_Block_For_GitHub();
    embed_block_for_github_admin.pagenow = pagenow;
    embed_block_for_github_admin.run_sec();
});

//TODO: Pending i18n
//TODO: Pending Style Table


class Embed_Block_For_GitHub {
    
    constructor() {
        this._timer_datatable_refres;
    }

    get pagenow() {
        return this._pagenow;
    }

    set pagenow(x) {
        this._pagenow = x;
    }

    run_sec() {
        switch(this.pagenow) {
            case "embed-block-for-github_page_embed-block-for-github-admin-api-github-rate":
                this.api_github_rate_info_update();
                break;
    
            case "embed-block-for-github_page_embed-block-for-github-admin-cache":
                this.cache_info_update();
                break;
    
            default:
                console.log("admin-ajax > Embed_Block_For_GitHub > Page (" + this.pagenow + ") not used JS!!");
        }
    }

    api_github_rate_info_update() {
        var self = this;
        var id_info_rate = "embed_block_for_github_admin_api_github_rate_info_rate";
        var id_info_resources = "embed_block_for_github_admin_api_github_rate_info_resources";
    
        //console.log(ajax_var);
        jQuery.ajax({
            type: "POST",
            url: ajax_var.url,
            dataType: 'JSON',
            data: {
                action : ajax_var.action,
                security : ajax_var.check_nonce
            },
            success: function(result){
                //console.log("OK");
                //console.log("check_nonce:" + ajax_var.check_nonce);
                //console.log(result);
                
                var limit = result.rate.limit;
                var remaining = result.rate.remaining;
                var rest = (limit - remaining);
                var porcent_rest = ((100 / limit) * remaining).toFixed(0);
    
                var html = `<p>Rate: ${remaining}/${limit} (${porcent_rest}% remaining)</p>`;
                if (jQuery('#'+id_info_rate).html() != html) {
                    jQuery('#'+id_info_rate).html(html);
                }
    
                
                var head_array = ["resources", "limit", "remaining", "rest", "porcent_rest", ""];
    
                var table = '<table>';
                table += '<tbody>';
                table += '<tr>';
                jQuery.each(head_array, function (head_array_key, head_array_value) {
                    table += '<th>' + head_array_value + '</th>';
                });
                table += '</tr>';
                jQuery.each(result.resources, function (key, value) {
                    var limit = value.limit;
                    var remaining = value.remaining;
                    var rest = (limit - remaining);
                    var porcent_rest = ((100 / limit) * remaining).toFixed(0);
                    table += '<tr>';
                    table += '<td width="200px">' + key + '</td>';
                    table += '<td width="100px">' + limit + '</td>';
                    table += '<td width="100px">' + remaining + '</td>';
                    table += '<td width="50px">' + rest + '</td>';
                    table += '<td width="100px">' + porcent_rest + '%</td>';
                    table += '<td width="auto"></td>';
                    table += '</tr>';
                });
                table += '</tbody>';
                table += '</table>';
            
                if (jQuery('#'+id_info_resources).html() != table) {
                    jQuery('#'+id_info_resources).html(table);
                    //console.log("A:" + jQuery('#'+id_info_resources).html() );
                    //console.log("B:" + table);
                }
                setTimeout( self.api_github_rate_info_update.bind(self) , 5000);
            },
            error: function(result) {
                //console.log("Error!!");
                //console.log(result);
                jQuery( "#"+id_info_rate ).html("Error: " + result.statusText);
                jQuery( "#"+id_info_resources ).html("Error: " + result.statusText);
                setTimeout( self.api_github_rate_info_update.bind(self) , 15000);
            }
        });
        
    }

    cache_info_update() {
        var self = this;
        var id_info_table = "embed_block_for_github_admin_cache_table";
        
        //https://datatables.net/examples/ajax/null_data_source.html
    
        var datatable = jQuery('#'+id_info_table).DataTable({
            //processing: true,
            ajax: {
                type: "POST",
                url: ajax_var.url,
                data: function ( d ) {
                    d.action = ajax_var.action_list;
                    d.security = ajax_var.check_nonce_list;
                }
            },
            
            columns: [
                {data:"id"},
                {data:"time_update"},
                {data:"time_expire"},
                {data:"expire"},
                {data:"url"},
                {
                    data: null,
                    defaultContent: '<button>Delete</button>'
                }
            ],
    
            columnDefs:[
                { targets: [1,2], render: function(data) {
                    var options = { hour: '2-digit', minute: '2-digit', second: '2-digit',  year: 'numeric', month: '2-digit', day: '2-digit', hourCycle: 'h24' };
                    var d = new Date(data);
                    //return d.toLocaleString("en-US", options);
                    return d.toLocaleString("es-ES", options);
                } },
                { targets: 4, render: function(data) { return `<a href="${data}" target="_blank">${data}</a>`; } }
            ]
    
        });
    
        jQuery('#'+id_info_table+' tbody').on( 'click', 'button', function () {
    
            var select_row = datatable.row( jQuery(this).parents('tr') );
            var id_remove = select_row.data()['id'];
    
            clearInterval(self._timer_datatable_refres);
    
            var r = confirm("Are you sure you wish to remove this record (" + id_remove + ")?");
            if (r == true) {
                jQuery.ajax({
                    type: "POST",
                    url: ajax_var.url,
                    dataType: 'JSON',
                    data: {
                        action : ajax_var.action_remove,
                        security : ajax_var.check_nonce_remove,
                        remove_id : id_remove
                    },
                    success: function(result){
                        //console.log("OK");
                        //console.log(result);
    
                        if (result.code != 0) {
                            alert (result.message);
                        } else {
                            select_row.remove().draw();
                        }
                    },
                    error: function(result) {
                        //console.log("Error!!");
                        //console.log(result);
                        alert("Error:" + result.statusText);
                    }
                });
            }
            
            self.cache_auto_update(datatable);
        } );
    
        this.cache_auto_update(datatable);
    }

    cache_auto_update(datatable) {
        this._timer_datatable_refres = setInterval( function () {
            datatable.ajax.reload().draw();
            console.log("refres");
        }, 5000 );
    }

}




