jQuery(document).ready( function () {
    embed_block_for_github_admin            = new Embed_Block_For_GitHub();
    //embed_block_for_github_admin.debug      = true;
    embed_block_for_github_admin.pagenow    = pagenow;
    if (typeof embed_block_for_github__ajax_var !== 'undefined') {
        embed_block_for_github_admin.ajax_var = embed_block_for_github__ajax_var;
    }
    embed_block_for_github_admin.run_sec();
});

//TODO: Pending Style Table

class Embed_Block_For_GitHub {
    
    constructor() {
        this._debug = false;
        this._ajax_var = null;
        this._count_refres = 0;
        this.i18n = window.wp.i18n;
        
        this._call_update = null;

        this._timer_count = null;
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

    get count_refres() {
        return this._count_refres;
    }

    set count_refres(x) {
        this._count_refres = x;
    }

    count_refres_next() {
        var new_count = 0;
        if ( ( this.count_refres !== null ) && ( ! isNaN(this.count_refres) ) ) {
            new_count = this.count_refres - 1;
            if (new_count < 0) {
                new_count = -1;
            }
        }
        this.count_refres = new_count;
    }

    update_html(array_data_update) {
        for (const [key, value] of Object.entries(array_data_update)) {
            //console.log("key (" + key + ") - Val (" + value + ")");
            if ( jQuery(key).html() != value ) {
                jQuery(key).html(value);
            }
        }
    }

    refres_count(id, args) {
        var self = this;
        var i18n = this.i18n;
        
        this.refres_count_stop();

        this._timer_count = setInterval( function () {
            if ( self.debug ) {
                console.log("Refres Count:" + self.count_refres);
            }

            var array_data = [];
            if (self.count_refres === 0) {
                array_data['#' + id] = i18n.__( 'Updating...' );
            } else {
                array_data['#' + id] = sprintf( i18n.__( 'Refres in %1$s seconsd' ), self.count_refres);
            }
            self.update_html( array_data );

            if (self.count_refres === 0) { 
                self.refres_count_stop();
                self._call_update(args);
            } else {
                self.count_refres_next();
            }

        }, 1000 );
    }

    refres_count_stop() {
        clearInterval(this._timer_count);
    }


    // RUN FUNCTION ACCORDING TO CURRENT PAGE
    run_sec() {
        switch(this.pagenow) {
            case "embed-block-for-github_page_embed-block-for-github-admin-api-github-rate":
                this._call_update = this.api_github_rate_update_info;
                break;
    
            case "embed-block-for-github_page_embed-block-for-github-admin-cache":
                this._call_update = this.cache_info_update;
                break;
    
            default:
                this._call_update = null;
                console.log("admin-ajax > Embed_Block_For_GitHub > Page (" + this.pagenow + ") not used JS!!");
        }

        if ( this._call_update !== null ) {
            this._call_update();
        }
    }

    // Pag - API GitHub Rate Limit
    api_github_rate_update_info() {
        var self = this;
        var i18n = this.i18n;

        if ( this.ajax_var_is_null ) {
            console.log("admin-ajax > Embed_Block_For_GitHub > api_github_rate_update_info() > ajax_var_is_null = TRUE !!");
            return;
        }

        var id_info_rate        = this.ajax_var.css_id.info_rate;
        var id_info_resources   = this.ajax_var.css_id.info_resources;
        var id_info_refres      = this.ajax_var.css_id.info_refres;
        
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
                var html = '<p>' + sprintf( i18n.__( 'Rate: %1$s/%2$s (%3$s%% remaining)' ), remaining, limit, porcent_rest) + '</p>';
                
                var head_array = [
                    i18n.__( 'Resources' ),
                    i18n.__( 'Query Limit' ),
                    i18n.__( 'Query Remaining' ),
                    i18n.__( 'Query Used' ),
                    i18n.__( 'Percentage Remaining' ), 
                    "",
                ];
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
                    table += '<td width="150px">' + remaining + '</td>';
                    table += '<td width="100px">' + rest + '</td>';
                    table += '<td width="200px">' + porcent_rest + '%</td>';
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

                self.count_refres = 5;
                self.refres_count(id_info_refres);
            },
            error: function(result) {
                var array_data = [];
                array_data['#' + id_info_rate]      = sprintf( i18n.__( 'Error: %1$s' ), result.statusText);
                array_data['#' + id_info_resources] = sprintf( i18n.__( 'Error: %1$s' ), result.statusText);
                self.update_html( array_data );

                if ( self.debug ) {
                    console.log("admin-ajax > Embed_Block_For_GitHub > api_github_rate_update_info() > UPDATE ERR!!");
                    console.log(result);
                }

                self.count_refres = 15;
                self.refres_count(id_info_refres);
            }
        });
        
    }


    // Pag - Cache Manager
    cache_info_update(args) {
        //https://datatables.net/examples/ajax/null_data_source.html

        var self = this;
        var i18n = this.i18n;

        if ( this.ajax_var_is_null ) {
            console.log("admin-ajax > Embed_Block_For_GitHub > cache_info_update() > ajax_var_is_null = TRUE !!");
            return;
        }

        var id_info_table   = this.ajax_var.css_id.info_table;
        var id_info_refres  = this.ajax_var.css_id.info_refres;
        
        var data_url        = this.ajax_url;
        var data_action     = this.ajax_var.action_list;
        var data_security   = this.ajax_var.check_nonce_list;
        var data_locate     = this.ajax_var.locate;

        var delete_action     = this.ajax_var.action_remove;
        var delete_security   = this.ajax_var.check_nonce_remove;
        
        if (typeof args !== 'undefined') {
            var datatable = args;
            datatable.ajax.reload().draw();
        } else {

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
                        defaultContent: '<button>' + i18n.__( 'Delete' ) + '</button>'
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
        
                self.refres_count_stop();
        
                var r = confirm( sprintf( i18n.__( 'Are you sure you wish to remove this record (%1$s)?' ), id_remove) );
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
                            self.count_refres = 5;
                        },
                        error: function(result) {
                            if ( self.debug ) {
                                console.log("admin-ajax > Embed_Block_For_GitHub > cache_info_update() > REMOVE ERR!!");
                                console.log(result);
                            }
                            alert( sprintf( i18n.__( 'Error: %1$s' ), result.statusText) );
                        }
                    });
                }

                self.refres_count(id_info_refres, datatable);
            } );
        }

        this.count_refres = 5;
        this.refres_count(id_info_refres, datatable);
    }

}