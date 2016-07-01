define([
  'jquery',
  'underscore_mixin',
  'backbone',
  'models/AppState',
  'text!templates/header.html',
  'text!templates/bulk.html',
  'text!templates/bulk_progress.html',
  'jquery_csv'
], function($, _, Backbone, appState, HeaderTemplate, BulkTemplate, BulkProgressTemplate){

    var Header = Backbone.View.extend({

        el: $("#header"),
        
        events: {
            "change #csv": "sendCsv"
        },
        
        import_link: '/api/specials/import',

        model: appState,

        templates: {
            "header": _.template(HeaderTemplate),
            "bulk": _.template(BulkTemplate),
            "bulk_progress": _.template(BulkProgressTemplate)
        },

        initialize: function () { 
            this.model.bind('change', this.render, this);
        },

        render: function () {
            var that = this;
            $(this.el).html(this.templates['header'](this.model.toJSON()));
            
            $(this.el).find('.language_selector span').click(function() {
                $(that.el).find('.language_selector ul').slideToggle();
            });
            $(this.el).find('.language_selector li').click(function() {
                appState.attributes.locale.setLocale($(this).data('value'));
                that.model.trigger('change'); 
            });
            
            return this;
        },
        
        sendCsv: function() {
            
            var this_ = this, errors = {}, success_arr = [], errors_count = 0;

            function handleFileSelect(id, callback)
            {
                if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
                    alert(this_.model.get('locale').get('file_api_not_supported'));
                    return;
                }

                input = document.getElementById(id);
                if (!input) {
                    //alert("Um, couldn't find the fileinput element.");
                }
                else if (!input.files) {
                    //alert("This browser doesn't seem to support the `files` property of file inputs.");
                }
                else if (!input.files[0]) {
                    //alert("Please select a file before clicking 'Load'");               
                }
                else {
                    file = input.files[0];
                    fr = new FileReader();
                    fr.onload = callback;
                    fr.readAsText(file);
                }
            }
            
            
            function sleep(ms) {
                ms += new Date().getTime();
                while (new Date() < ms){}
            } 
            
            function log(message) {
                console.log(message);
                //$('#proggress').append(message + '<br/>');
            }

            
            handleFileSelect('csv', function(event) { 
                
                // render view
                $("#content").html(this_.templates['bulk_progress']({locale: this_.model.get('locale')}));

                var specials_num = 0, csv, validated = [], geocoded = [[]], row, sending_running = false; current_num = 0;

                function update_view() {
                    $('#proccessed').html(success_arr.length + errors_count);
                    $('#all').html(csv.length);
                    $('#success').html(success_arr.length);
                    $('#errors').html(errors_count );
                }
                
                function error(row, error_name) {
                    errors_count++;
                    if (typeof errors[error_name] === 'undefined') errors[error_name] = [];
                    errors[error_name].push(row[1]);
                    update_view();
                }
                
                function success(row) {
                    success_arr.push(row[1]);
                    update_view();
                }
                
                function validate(callback) {

                    log(appState.get('locale').get('start_validating_specials'));
                    
                    for (var i in csv) {
                        row = csv[i];
                    
                        // validate special
                        if (row.length < 10) {
                            error(row, 'invalid_row');
                            continue;
                        }
                    
                        // validate special
                        if (row[0].length > 16) {
                            error(row, 'batch_id');
                            continue;
                        }
                        
                        if (row[1].length > 16) {
                            error(row, 'token_id');
                            continue;
                        }

                        if (!row[2].length || row[2].length > 26) {
                            error(row, 'name');
                            continue;
                        }

                        if (!row[3].length || row[3].length > 100) {
                            error(row, 'description');
                            continue;
                        }

                        if (!row[4].length || row[4].length > 40) {
                            error(row, 'store');
                            continue;
                        }

                        if (row[6].length !== 0 && !/^(http|https)\:\/\/([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/.test(row[6])) {
                            error(row, 'website');
                            continue;
                        }

                        if (row[7].length !== 0 && !/^\+?[\d-\s]+$/.test(row[7])) {
                            error(row, 'phone');
                            continue;
                        }

                        if (row[8].length !== 0 && !/^(http|https)\:\/\/([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/.test(row[8])) {
                            error(row, 'image_link');
                            continue;
                        }

                        if (row[9].length !== 0 && !/^(http|https)\:\/\/([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/.test(row[9])) {
                            error(row, 'image_link');
                            continue;
                        }
                        
                        validated.push(row);
                    }
                    
                    log(appState.get('locale').get('validating_specials_finished'));
                    get_choordinates();
                }
                
                function get_choordinates() {
                    
                    var active_queries_num = 0;
                    log(appState.get('locale').get('start_geocoding_addresses'));
                    
                    function sync() {
                        
                        // set number of paralel queries 
                        while (active_queries_num < 1) {
                            run();
                        }
                        
                    }
                    
                    function run() {
                        
                        active_queries_num++;

                        if (!validated.length) {
                            if (sending_running === true) return;
                            
                            log(appState.get('locale').get('geocoding_addresses_finished'));
                            send_to_server();
                            return;
                        }
                        
                        row = validated.splice(0, 1)[0];
                        
                        $.get('http://maps.google.com/maps/api/geocode/json?address=' + row[5] + '&sensor=false', (function(row) {
                            return function(data) {
                                
                                active_queries_num--;
                                
                                if (data.results && data.results.length > 0) {
                                    
                                    if (geocoded[geocoded.length - 1].length >= 2) geocoded[geocoded.length] = [];
                                    
                                    // save country name and code
                                    var country = '';
                                    var country_code = '';
                                    for (var i in data.results[0].address_components) {
                                        if (data.results[0].address_components[i].types[0] === 'country') {
                                            country = data.results[0].address_components[i].long_name;
                                            country_code = data.results[0].address_components[i].short_name;
                                        }
                                    }
                                    
                                    geocoded[geocoded.length - 1].push({
                                        token_id: row[1],
                                        batch_id: row[0],
                                        name: row[2],
                                        description: row[3],
                                        store: row[4],
                                        address: row[5],
                                        website: row[6],
                                        phone: row[7],
                                        image: row[8],
                                        logo: row[9],
                                        logo_bg: row[10],
                                        valid_for: row[11],
                                        status: typeof row[12] !== 'undefined'? row[12] : 1,
                                        longitude: data.results[0].geometry.location.lng,
                                        latitude: data.results[0].geometry.location.lat,
                                        country: country,
                                        country_code: country_code
                                    });
                                    
                                } else {
                                    error(row, 'address');
                                }
                                
                                sync();
                            }
                        })(row));
                        
                    }  
                    
                    sync();
                    
                }
                
                function send_to_server() {
                    
                    if (sending_running === true) return;
                    sending_running = true;
                    
                    log(appState.get('locale').get('start_sending_to_server'));
                    
                    function sync() {
                        update_view();

                        if (success_arr.length + errors_count === csv.length) {
                            log(appState.get('locale').get('sending_to_server_finished'));
                            $("#content").html(this_.templates['bulk']({data: {'success': success_arr, 'errors': errors}, locale: this_.model.get('locale')}));
                        } else {
                            proccess(current_num + 1);
                        }
                    }

                    function proccess(num) {
                        current_num = num;
                        
                        var pack = geocoded[num];

                        // add specials
                        (function(pack) {
                            $.ajax({
                                type: 'POST',
                                url: this_.import_link,
                                //crossDomain: true,
                                data: {'specials': pack},
                                success: function(response, textStatus, jqXHR) {

                                    for (var i in response.success) {
                                        success_arr.push(response.success[i]);
                                    } 

                                    for (var i in response.errors) {
                                        if (typeof errors[i] === 'undefined') errors[i] = [];
                                        for (var j in response.errors[i]) {
                                           errors_count++;
                                           errors[i].push(response.errors[i][j]); 
                                        }
                                    } 

                                    sync();

                                    },
                                error: function (response, textStatus, errorThrown) {

                                    for (var i in pack) {
                                        error([pack[i].batch_id, pack[i].token_id], 'server_error');
                                    }    

                                    sync();

                                }
                            });
                        })(pack);

                    }
                    
                    
                    proccess(0);
                }
                
                
                
                
                try {
                    
                    csv = $.csv.toArrays(event.target.result, {separator: ';', delimiter: '"', headers: false});
                    specials_num = csv.length;
                    
                } catch(e) {
                    alert(appState.get('locale').get('invalid_csv_file'));
                    console.log(e.toString());
                    
                    //window.location.href = "/private";
                    return;
                }
                    
                
                validate(function() {
                    //alert('send to server');
                    proccess(current_num);
                    
                });
                
                
            });
            
        }
        

    });

    var initialize = function() {

        var header = new Header();
        appState.trigger("change");
        
    };
    
    return {
        initialize: initialize
    };
    
});