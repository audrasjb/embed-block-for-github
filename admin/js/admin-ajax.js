jQuery(document).ready( function () {
    embed_block_for_github_admin            = new Embed_Block_For_GitHub();
    embed_block_for_github_admin.debug      = true;
    embed_block_for_github_admin.pagenow    = pagenow;
    if (typeof embed_block_for_github__ajax_var !== 'undefined') {
        embed_block_for_github_admin.ajax_var = embed_block_for_github__ajax_var;
    }
    embed_block_for_github_admin.run_sec();
});

//TODO: Pending i18n
//TODO: Pending Style Table


class Embed_Block_For_GitHub {
    
    constructor() {
        this._debug = false;
        this._ajax_var = null;
        this._timer_datatable_refres;
    }

    get debug() {
        return this._debug;
    }

    set debug(x) {
        this._debug = x;
    }

    get pagenow() {
        return this._pagenow;
    }

    set pagenow(x) {
        this._pagenow = x;
    }

    get ajax_var() {
        return this._ajax_var;
    }

    set ajax_var(x) {
        this._ajax_var = x;
    }

    get ajax_var_is_null() {
        if ( this.ajax_var === null ) {
            return true;
        }
        return false;
    }

    get ajax_url() {
        var data_return = null;
        if ( ! this.ajax_var_is_null ) {
            if (typeof this.ajax_var.url !== 'undefined') {
                data_return = this.ajax_var.url;
            }
        }
        return data_return;
    }

    get ajax_security() {
        var data_return = null;
        if ( ! this.ajax_var_is_null ) {
            if (typeof this.ajax_var.check_nonce !== 'undefined') {
                data_return = this.ajax_var.check_nonce;
            }
        }
        return data_return;
    }

    get ajax_action() {
        var data_return = null;
        if ( ! this.ajax_var_is_null ) {
            if (typeof this.ajax_var.action !== 'undefined') {
                data_return = this.ajax_var.action;
            }
        }
        return data_return;
    }



    run_sec() {
        switch(this.pagenow) {
            case "embed-block-for-github_page_embed-block-for-github-admin-api-github-rate":
                this.api_github_rate_update_info();
                break;
    
            case "embed-block-for-github_page_embed-block-for-github-admin-cache":
                this.cache_info_update();
                break;
    
            default:
                console.log("admin-ajax > Embed_Block_For_GitHub > Page (" + this.pagenow + ") not used JS!!");
        }
    }


    update_html(array_data_update) {
        for (const [key, value] of Object.entries(array_data_update)) {
            //console.log("key (" + key + ") - Val (" + value + ")");
            if ( jQuery(key).html() != value ) {
                jQuery(key).html(value);
            }
        }
    }



    api_github_rate_set_timeout(interval) {
        setTimeout( this.api_github_rate_update_info.bind(this) , interval);
    }

    api_github_rate_update_info() {
        var self = this;

        if ( this.ajax_var_is_null ) {
            console.log("admin-ajax > Embed_Block_For_GitHub > api_github_rate_update_info() > ajax_var_is_null = TRUE !!");
            self.api_github_rate_set_timeout(15000);
            return;
        }

        var id_info_rate        = this.ajax_var.css_id.info_rate;
        var id_info_resources   = this.ajax_var.css_id.info_resources;

        var data_url        = this.ajax_url;
        var data_action     = this.ajax_action;
        var data_security   = this.ajax_security;

        jQuery.ajax({
            type: "POST",
            url: data_url,
            dataType: 'JSON',
            data: {
                action : data_action,
                security : data_security,
            },
            success: function(result){
                if ( self.debug ) {
                    console.log("admin-ajax > Embed_Block_For_GitHub > api_github_rate_update_info() > UPDATE INIT...");
                    console.log(">> ajax_var:");
                    console.log(self.ajax_var);
                    console.log(">> result:");
                    console.log(result);
                }

                var limit = result.rate.limit;
                var remaining = result.rate.remaining;
                var rest = (limit - remaining);
                var porcent_rest = ((100 / limit) * remaining).toFixed(0);
                var html = `<p>Rate: ${remaining}/${limit} (${porcent_rest}% remaining)</p>`;
                
                
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

                var array_data = [];
                array_data['#' + id_info_rate]      = html;
                array_data['#' + id_info_resources] = table;
                self.update_html( array_data );

                if ( self.debug ) {
                    console.log("admin-ajax > Embed_Block_For_GitHub > api_github_rate_update_info() > UPDATE - END OK!");
                }
                self.api_github_rate_set_timeout(5000);
            },
            error: function(result) {
                var array_data = [];
                array_data['#' + id_info_rate]      = "Error: " + result.statusText;
                array_data['#' + id_info_resources] = "Error: " + result.statusText;
                self.update_html( array_data );

                if ( self.debug ) {
                    console.log("admin-ajax > Embed_Block_For_GitHub > api_github_rate_update_info() > UPDATE ERR!!");
                    console.log(result);
                }
                self.api_github_rate_set_timeout(15000);
            }
        });
        
    }
















    cache_info_update() {
        //https://datatables.net/examples/ajax/null_data_source.html

        var self = this;

        if ( this.ajax_var_is_null ) {
            console.log("admin-ajax > Embed_Block_For_GitHub > cache_info_update() > ajax_var_is_null = TRUE !!");
            return;
        }

        var id_info_table   = this.ajax_var.css_id.info_table;
        
        var data_url        = this.ajax_url;
        var data_action     = this.ajax_var.action_list;
        var data_security   = this.ajax_var.check_nonce_list;
        var data_locate     = this.ajax_var.locate;

        var delete_action     = this.ajax_var.action_remove;
        var delete_security   = this.ajax_var.check_nonce_remove;
        
        
        var datatable = jQuery('#'+id_info_table).DataTable({
            //processing: true,
            ajax: {
                type: "POST",
                url: data_url,
                data: function ( d ) {
                    d.action = data_action;
                    d.security = data_security;
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
                    return d.toLocaleString(data_locate, options);
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
                    url: data_url,
                    dataType: 'JSON',
                    data: {
                        action : delete_action,
                        security : delete_security,
                        remove_id : id_remove,
                    },
                    success: function(result){
                        if ( self.debug ) {
                            console.log("admin-ajax > Embed_Block_For_GitHub > cache_info_update() > REMOVE OK!!");
                            console.log(result);
                        }

                        if (result.code != 0) {
                            alert (result.message);
                        } else {
                            select_row.remove().draw();
                        }
                    },
                    error: function(result) {
                        if ( self.debug ) {
                            console.log("admin-ajax > Embed_Block_For_GitHub > cache_info_update() > REMOVE ERR!!");
                            console.log(result);
                        }
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
            if ( self.debug ) {
                console.log("admin-ajax > Embed_Block_For_GitHub > cache_auto_update() > REFRES!!");
                console.log(result);
            }
        }, 5000 );
    }

}




