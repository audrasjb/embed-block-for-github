
jQuery(document).ready( function () {

    embed_block_for_github_admin_api_github_rate_info_update();

});


function embed_block_for_github_admin_api_github_rate_info_update() {
    var namefun = arguments.callee.name;
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

            var html = `<p>Rate: ${limit}/${remaining} (${porcent_rest}% remaining)</p>`;
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

            setTimeout( function(){ window[namefun](); } , 5000);
        },
        error: function(result) {
            //console.log("Error!!");
            //console.log(result);
            jQuery( "#"+id_info_rate ).html("Error: " + result.statusText);
            jQuery( "#"+id_info_resources ).html("Error: " + result.statusText);
            setTimeout( function(){ window[namefun](); } , 15000);
        }
    });
    
}