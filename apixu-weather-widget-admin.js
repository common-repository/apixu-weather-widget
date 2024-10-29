// SEARCH FOR LOCATION ID
jQuery(document).ready(function(){
    jQuery(document.body).on('keyup','.awe-location-search-field-apixu',_.debounce(function(){
        if(jQuery(this).val() != ""){
            var units_val = jQuery('#c-' + jQuery(this).data('unitsfield')).prop('checked') ? "f" : "c";
            var location_id = jQuery(this).attr('id');
            var owm_city_id_selector = "#" + jQuery(this).data('cityidfield');
            jQuery('#awe-owm-spinner-' + location_id).removeClass("hidden");

            // PING
            var data = {action:'awe_ping_owm_for_id',location:jQuery(this).val(),units:units_val};

            jQuery.getJSON(ajaxurl,data,function(response){
                //console.log(response);
                var place_count = response.length;
                var places = response;
                //console.log(owm_city_id_selector);
                jQuery(owm_city_id_selector).val('');

                // IF NO PLACES DISPLAY AN ERROR
                if(!places){
                    jQuery('#owmid-selector-' + location_id).html("<span style='color:red;'>" + awe_script.no_owm_city + "</span>");
                }
                else{
                    if(place_count == 1){
                        jQuery(owm_city_id_selector).val(places[0].name);
                        jQuery('#owmid-selector-' + location_id).html("<span style='color:red;'>" + awe_script.one_city_found + "</span>");
                    }
                    else{
                        var rtn = awe_script.confirm_city;
                        for(p = 0; p < places.length; p++){
                            if(places[p].name && places[p].name != 0){
                                // SET TO FIRST
                                if(p == 0){
                                    jQuery(owm_city_id_selector).val(places[p].name);
                                }

                                rtn = rtn + "<div style='padding: 5px 5px 0 10px;'> - <a href='javascript:;' onclick=\"jQuery('#" + location_id + "').val('" + places[p].name.replace(/'/g,"\\'") + "');\" style='text-decoration:none;'>" + places[p].name + "</a></div>";
                            }
                        }
                        jQuery('#owmid-selector-' + location_id).html(rtn);
                    }
                }
                jQuery('#awe-owm-spinner-' + location_id).addClass("hidden");
            });
        }
    },250));
});