define([
  'jquery',
  'underscore_mixin',
  'backbone',
  'router',
  'moment',
  'models/AppState',
  'models/Batch',
  'models/Specials',
  'text!templates/specials_info.html',
  'jquery_autocomplete',
  'jquery_csv',
  //'async!http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false!callback',
  'async!https://maps.googleapis.com/maps/api/js?key=AIzaSyCNTOvfbnPp6ODq9rMdEqW4MN9Gd1UHAaE&libraries=places&sensor=false!callback',
  'ddslick',
  'colorpicker',
  'niceinput',
  'exif'
], function($, _, Backbone, Router, moment, appState, Batch, Specials, SpecialsInfoTemplate){
    
    
    function base64_decode( data ) {	// Decodes data encoded with MIME base64

	var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var o1, o2, o3, h1, h2, h3, h4, bits, i=0, enc='';

	do {  // unpack four hexets into three octets using index points in b64
		h1 = b64.indexOf(data.charAt(i++));
		h2 = b64.indexOf(data.charAt(i++));
		h3 = b64.indexOf(data.charAt(i++));
		h4 = b64.indexOf(data.charAt(i++));

		bits = h1<<18 | h2<<12 | h3<<6 | h4;

		o1 = bits>>16 & 0xff;
		o2 = bits>>8 & 0xff;
		o3 = bits & 0xff;

		if (h3 == 64)	  enc += String.fromCharCode(o1);
		else if (h4 == 64) enc += String.fromCharCode(o1, o2);
		else			   enc += String.fromCharCode(o1, o2, o3);
	} while (i < data.length);

	return enc;
    }

    
    var router = Router.initialize();
    
    var countries = [];
    
    var header = null;
    
    var Block = Backbone.View.extend({

        el: $("#content"),
        
        import_link: '/api/specials/import',
        pay_link: '/api/pay',
        pay_info_link: '/api/pay_info',
        add_link: '/api/fontend/add_special',
        transaction_info_link: '/api/update_transaction',
        
        model: appState,

        events: {
            "click #batch_confirm": "getBatch",
            "click #pay": "pay",
            "click #token_confirm": "getToken",
            "click #specials_confirm": "addSpecials",
            "click #specials_preview": "previewSpecials",
            "click #edit": "editSpecials",
            "click .specials_item": "showSpecial",
            "click #batch_load_more": "loadMoreBatches",
            "click #specials_all_load_more": "loadMoreAllSpecials",
            "click #specials_active_load_more": "loadMoreActiveSpecials",
            "click #specials_queued_load_more": "loadMoreQueuedSpecials",
            "click #specials_declined_load_more": "loadMoreDeclinedSpecials",
            "click #specials_expired_load_more": "loadMoreExpiredSpecials",
            "change #csv": "sendCsv"
        },

        templates: {
            "start": _.template($('#StartTemplate').html()),
            "batch_not_found": _.template($('#BatchNotFoundTemplate').html()),
            "specials_list": _.template($('#SpecialsListTemplate').html()),
            "invalid_token": _.template($('#InvalidTokenTemplate').html()),
            "new_specials": _.template($('#NewSpecialsTemplate').html()),
            "specials_success": _.template($('#SpecialSuccessTemplate').html()),
            "reuse": _.template($('#SpecialReuseTemplate').html()),
            "get_more_tokens": _.template($('#GetMoreTokensTemplate').html()),
            "bulk": _.template($('#BulkTemplate').html()),
            "bulk_progress": _.template($('#BulkProgressTemplate').html()),
            "batch": _.template($('#BatchTemplate').html()),
            "payment_error": _.template($('#PaymentErrorTemplate').html()),
            "invoice_success": _.template($('#InvoiceSuccessTemplate').html()),
        },

        initialize: function () { 
            this.model.bind('change', this.render, this);
        },

        render: function () {
            
            var state = this.model.get("state"), this_ = this;       
            
            function initElements() {
                if (state === 'specials_list') {
                    $('.menu_right a:eq(' + this_.model.get("special_list_num") + ')').addClass('active');
                    $('.specials_list:eq(' + this_.model.get("special_list_num") + ')').show();
                }
                if (state === 'reuse' || state === 'new_specials') {
                    this_.googlemap();
                    this_.autocomplete();
                    this_.init_store_logo();
                } 
                
                if (typeof this_[state + '_after'] === 'function') {
                    this_[state + '_after']();
                }
                
                // replace dropdowns
                $('#specials_days').ddslick();
                
                $(".field input[type=file]").nicefileinput({
                    label: appState.get('locale').get('choose_file')
                });
            }
            
            if (typeof this[state] === 'function') this[state](function() {
                $(this_.el).html(this_.templates[state](this_.model.toJSON()));
                initElements();
            });
            else {
                $(this.el).html(this.templates[state](this.model.toJSON()));
                initElements();
            }
            
            return this;
        },
        
        get_more_tokens_after: function() {
            var this_ = this;
            
            function showSection(s) {
                $('#start_section').hide(); 
                $('#country_section').hide(); 
                $('#nopayment_section').hide(); 
                
                $('#' + s + '_section').show(); 
            }
            
            $('#start_section .pay').click(function() {
                if (!/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test($('#email').val())) {
                    alert(appState.get('locale').get('specify_valid_email_address'));
                    $('#email').focus();
                    return false;
                }
                
                $.cookie('payment_email', $('#email').val());
                
                showSection('country'); 
                return false;
            });
            
            $('.country_section-back').click(function() {
                showSection('start');
            });
            
            $('#country_section .pay').click(function() {
                $('.country_name').html($('#country .dd-selected-text').html());
                if(typeof $('#country input').val() === 'undefined'){
                    var country = $('#country').val();
                } else  {
                    var country = $('#country input').val();
                }
                appState.get('locale').setCountry(country);
                if (country.toLowerCase() !== 'it') {
                    showSection('nopayment');
                } else {
                    this_.pay();
                }
                return false;
            });
            
            $('#nopayment_section .pay').click(function() {
                showSection('country');
                return false;
            })
            
            // $('#country option[value="' + appState.get('locale').getCountry().toUpperCase() + '"]').attr('selected', 'selected');
            
            // prefill by ip address
            jQuery.ajax( { 
                url: '//freegeoip.net/json/', 
                type: 'POST', 
                dataType: 'jsonp',
                success: function(location) {
                    $('#country option[value="' + location.country_code + '"]').prop('selected', true);
                    $('#country').ddslick();
                }
            });
            
            $('#tokens').ddslick({onSelected: function(data) {
                $('#price_total').html(data.selectedData.value);
              }
            });
        },
        
        specials_list: function(callback) {            
            
            this.preloader();
            
            var batch = new Batch({id: appState.get('batch').id});
            batch.fetch({
                success: function (batch) {
                    appState.attributes.batch = batch;
                    header.render();
                    callback();
                }, 
                error: function() {
                    appState.set({invalid_batch_id: appState.get('batch').id});
                    router.navigate("!/batch_not_found", true);
                }
            });
            
        },
        
        /*sendCsv: function() {
            
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
                //$('#proggress').append(message + '<br/>');
            }

            
            handleFileSelect('csv', function(event) { 
                
                // render view
                $(this_.el).html(this_.templates['bulk_progress']({locale: this_.model.get('locale')}));

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
                        if (row[1].length !== 10) {
                            error(row, 'token_id');
                            continue;
                        }

                        if (row[0].length !== 10) {
                            error(row, 'batch_id');
                            continue;
                        }

                        if (!row[2].length || row[2].length > 40) {
                            error(row, 'name');
                            continue;
                        }

                        if (!row[3].length || row[3].length > 120) {
                            error(row, 'description');
                            continue;
                        }

                        if (!row[4].length || row[4].length > 100) {
                            error(row, 'store');
                            continue;
                        }

                        if (row[6].length !== 0 && !/^(http|https)\:\/\/([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/.test(row[6])) {
                            error(row, 'website');
                            continue;
                        }

                        if (row[7].length !== 0 && !/^[\d-\s]+$/.test(row[7])) {
                            error(row, 'phone');
                            continue;
                        }

                        if (row[8].length !== 0 && !/^(http|https)\:\/\/([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/.test(row[8])) {
                            error(row, 'image');
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
                                        valid_for: row[9],
                                        longitude: data.results[0].geometry.location.lng,
                                        latitude: data.results[0].geometry.location.lat
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
                            $(this_.el).html(this_.templates['bulk']({data: {'success': success_arr, 'errors': errors}, locale: this_.model.get('locale')}));
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
                                crossDomain: true,
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
                    this_.render();
                    return;
                }
                    
                
                validate(function() {
                    //alert('send to server');
                    proccess(current_num);
                    
                });
                
                
            });
            
        },*/
        
        invoice_success: function() {
            this.preloader();
            var this_ = this;
            $.post(this.pay_info_link, {batch_id: this_.model.get('batch_id'), transaction_id:  this_.model.get('transaction_id')}, function(response) {
                if (response.status === 'error') {

                    alert(this_.model.get('locale').get(response.message));
                    this_.render();
                    
                } else {
                    appState.attributes.batch = response.batch;
                    appState.attributes.transaction = response.transaction;
                    $(this_.el).html(this_.templates['invoice_success'](this_.model.toJSON()));
                }
            });
        },
        
        payment_success: function(calback) {
            this.preloader();
            var this_ = this;
            $.post(this.pay_info_link, {batch_id: this_.model.get('batch_id'), transaction_id:  this_.model.get('transaction_id')}, function(response) {
                if (response.status === 'error') {

                    alert(this_.model.get('locale').get(response.message));
                    this_.render();

                } else {
                    
                    if (response.transaction.updated === true) {
                        router.navigate("!/invoice_success/" + this_.model.get('batch_id') + "/" + this_.model.get('transaction_id'), true);
                        return;
                    }
                    
                    // show batch
                    appState.attributes.batch = response.batch;
                    appState.attributes.transaction = response.transaction;
                    $(this_.el).html(this_.templates['batch'](this_.model.toJSON()));
                    $('.specials_full_info .print').click(function() {
                        this_.printTokens();
                    });
                    
                    $('.button.pay').click(function() {
                        
                        /*if (!$('#store').val().length || $('#store').val().length > 200) {
                           // alert(appState.get('locale').get('specify_valid_store_name'));
                            //$('#store').focus();
                           // return;
                        }*/
                        
                        if (!$('#address').val().length || $('#address').val().length > 300) {
                            alert(appState.get('locale').get('specify_valid_address'));
                            $('#address').focus();
                            return;
                        }
                        
                        if (!$('#company').val().length || $('#company').val().length > 300) {
                            alert(appState.get('locale').get('specify_valid_company_name'));
                            $('#company').focus();
                            return;
                        }
                        
                        if (!$('#city').val().length || $('#city').val().length > 200) {
                            alert(appState.get('locale').get('specify_valid_city'));
                            $('#city').focus();
                            return;
                        }
                        
                        if (!$('#postal_id').val().length || $('#postal_id').val().length > 300) {
                            alert(appState.get('locale').get('specify_valid_postal_id'));
                            $('#postal_id').focus();
                            return;
                        }
                        
                        if (!$('#fiscal_id').val().length || $('#fiscal_id').val().length > 300) {
                            alert(appState.get('locale').get('specify_valid_fiscal_id'));
                            $('#fiscal_id').focus();
                            return;
                        }
                        
                        var data = {
                            email: $.cookie('payment_email'),
                            company: $('#company').val(),
                            store: $('#store').val(),
                            address: $('#address').val(),
                            city: $('#city').val(),
                            province: $('#province').val(),
                            postal_id: $('#postal_id').val(),
                            fiscal_id: $('#fiscal_id').val(),
                            batch_id: this_.model.get('batch_id'),
                            transaction_id: this_.model.get('transaction_id')
                        };
                        
                        this_.preloader();

                        $.post(this_.transaction_info_link, data, function(response) {
                            if (response.status === 'error') {
                                alert(this_.model.get('locale').get(response.message));
                                this_.render();
                            } else {
                                router.navigate("!/invoice_success/" + this_.model.get('batch_id') + "/" + this_.model.get('transaction_id'), true);
                            }
                        });
                        
                    });
                    
                }
            });
        },
        
        reuse: function(callback) {
            
            if (!this.model.get('batch')) {
                router.navigate("!/", true);
                return;
            }
            
            this.preloader();
            
            $.get('/api/specials/' + this.model.get("reuse_id"), {}, function(data) {
                appState.attributes.special = data;
                
                // try to update country code
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    address: appState.attributes.special.addres
                }, function(responses) {
                    if (responses && responses.length > 0) {

                        // save country name
                        for (var i in responses[0].address_components) {
                            if (responses[0].address_components[i].types[0] === 'country') {
                                appState.attributes.special.country = responses[0].address_components[i].long_name;
                                appState.attributes.special.country_code = responses[0].address_components[i].short_name;
                            }
                        }
                    }
                
                    callback();
                });
            });
            
        },
                
        loadMoreAllSpecials: function() {
            $('#specials_all_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/batch/specials/all/' + appState.get('batch').id, {offset: appState.get('batch').get('specials').all.length, limit: 20}, function(items) {
                appState.attributes.batch.attributes.specials.all = appState.attributes.batch.attributes.specials.all.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(0)').addClass('active');
                $('.specials_list:eq(0)').show();
                $("html, body").scrollTop($("html, body").height());
                if (items.length !== 20) $('#specials_all_load_more').remove();
            });
        },
        
        loadMoreActiveSpecials: function() {
            $('#specials_active_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/batch/specials/active/' + appState.get('batch').id , {offset: appState.get('batch').get('specials').active.length, limit: 20}, function(items) {
                appState.attributes.batch.attributes.specials.active = appState.attributes.batch.attributes.specials.active.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(1)').addClass('active');
                $('.specials_list:eq(1)').show();
                $("html, body").scrollTop($("html, body").height());
                if (items.length !== 20) $('#specials_active_load_more').remove();
            });
        },
        
        loadMoreQueuedSpecials: function() {
            $('#specials_queued_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/batch/specialsqueued' + appState.get('batch').id, {offset: appState.get('batch').get('specials').queued.length, limit: 20}, function(items) {
                appState.attributes.batch.attributes.specials.queued = appState.attributes.batch.attributes.specials.queued.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(2)').addClass('active');
                $('.specials_list:eq(2)').show();
                $("html, body").scrollTop($("html, body").height());
                if (items.length !== 20) $('#specials_queued_load_more').remove();
            });
        },
        
        loadMoreDeclinedSpecials: function() {
            $('#specials_expired_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/specials/list/declined', {offset: appState.get('batch').get('specials').expired.length, limit: 20}, function(items) {
                appState.attributes.batch.attributes.specials.expired = appState.attributes.batch.attributes.specials.expired.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(3)').addClass('active');
                $('.specials_list:eq(3)').show();
                $("html, body").scrollTop($("html, body").height());
                if (items.length !== 20) $('#specials_expired_load_more').remove();
            });
        },
        
        loadMoreExpiredSpecials: function() {
            $('#specials_expired_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/specials/list/expired', {offset: appState.get('batch').get('specials').expired.length, limit: 20}, function(items) {
                appState.attributes.batch.attributes.specials.expired = appState.attributes.batch.attributes.specials.expired.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(4)').addClass('active');
                $('.specials_list:eq(4)').show();
                $("html, body").scrollTop($("html, body").height());
                if (items.length !== 20) $('#specials_expired_load_more').remove();
            });
        },

        getBatch: function() {
            
            var batchId = $('#batch_id').val();
            var batch = new Batch({id: batchId, specials: []});
            appState.attributes.batch = batch;
            router.navigate("!/specials_list/0", true);

        },
        
        getToken: function() {

            var tokenId = $('#token_id').val();
            this.preloader();

            var token = new Token({id: tokenId});
            token.fetch({
                success: function (token) {
                    if (token.get("batchId") === appState.get('batch').id && token.get("specialsId") === null) {
                        appState.set({token: token});
                        router.navigate("!/new_specials", true);
                    } else {
                        appState.set({invalid_token_id: $('#token_id').val()});
                        router.navigate("!/invalid_token", true);
                    }
                }, 
                error: function() {
                    appState.set({invalid_token_id: tokenId});
                    router.navigate("!/invalid_token", true);
                }
            });

        },
        
        showSpecial: function(e) {
            
            var el = $(e.target).hasClass('specials_item')? $(e.target).find('.specials_full_info') : $(e.target).parents('.specials_item').find('.specials_full_info');
            var specialId = e.target.id || $(e.target).parents('.specials_item').attr('id');
            
            if (el.is(':animated')) {
                return;
            } else if (el.is(':visible')) {
                el.slideUp();
            } else {
                el.html('<img src="/i/small_loader.gif" style="margin: 40px auto; height: auto; width: auto;display: block;" />').slideDown();
                
                var specials = new Specials({id: specialId});
                specials.fetch({
                    success: function (specials) {
                        specials.set({'locale': appState.get('locale')});
                        el.html(_.template(SpecialsInfoTemplate)(specials.toJSON()));
                    }, 
                    error: function() {
                        el.html('error');
                    }
                });
            }
            
        },
        
        printTokens: function() {
            var date = new Date();
            var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
            
            var text = this.model.get('batch').tokens.length + ' Stin Jee tokens for creating specials;<br/>';
            text += 'Purchased on ' + date.getUTCDate() + ' ' +  monthNames[date.getMonth()] + ' ' +  date.getFullYear() + ';<br/>';
            text += 'To upload specials, please go to: http://client.stinjee.com;<br/>';
            text += ';<br/>';
            text += 'Batch ID: ;' + this.model.get('batch').id + '<br/>';
            text += ';<br/>';
            text += 'Token IDs;<br/>';
            text += ';<br/>';
            text += this.model.get('batch').tokens.join(';<br/>') + ';';
            
            var win = window.navigator.userAgent.indexOf("MSIE ") !== -1? window.open("", "_blank", 'height=600,width=400') : window.open("", "_blank");
            win.document.write(text);
            win.document.close();
            win.focus();
            win.print();
            win.close();
        },
        
        pay: function() {
            
            // https://developer.paypal.com/webapps/developer/docs/classic/express-checkout/integration-guide/ECGettingStarted/
            
            var tokens = {
                '36.60': 5,
                '73.20': 10,
                '146.40': 20
            };

            //because sometimes some libs coudnt loaded
            if(typeof $('#country input').val() === 'undefined'){
                var country = $('#country').val();
            } else  {
                var country = $('#country input').val();
            }

            if(typeof $('#tokens input').val() === 'undefined'){
                var tokens_total = $('#tokens option:checked').val();
            } else  {
                var tokens_total = $('#tokens input').val();
            }
            var values = {
                tokens_num: tokens[tokens_total],
                email: $('#email').val(),
                country: country.toLowerCase()
            };
            
            this.preloader();
            
            $.post(this.pay_link, values, function(response) {
                if (typeof response === 'object' && response.ACK === 'Success') {
                    window.location.href = response.pay_link;
                } else {
                    if (typeof response === 'object' && response.error) {
                        router.navigate("!/payment_error/" + response.error, true);
                    } else {
                        router.navigate("!/payment_error/paypal_connection_error", true);
                    }
                }
            });
            
        },
        
        validateSpecial: function(callback) {
            
            // check for valid batch and token
            var tokenId = $('#specials_token').val();
            var batchId = $('#specials_batch').val() || appState.get('batch').id;
            
            
            // validate
            
            if ($.trim(tokenId).length < 9 || $.trim(tokenId).length > 16) {
                alert(appState.get('locale').get('specify_valid_token_id'));
                $('#specials_token').focus();
                return;
            }
            
            if ($.trim(batchId).length < 9 || $.trim(batchId).length > 16) {
                alert(appState.get('locale').get('specify_valid_batch_id'));
                $('#specials_batch').focus();
                return;
            }
            
            if (!appState.get('position')) {
                alert(appState.get('locale').get('specify_valid_address'));
                $('#specials_addres').focus();
                return;
            }
            
            if (!$('#specials_name').val().length || $('#specials_name').val().length > 50) {
                alert(appState.get('locale').get('specify_valid_special_name'));
                $('#specials_name').focus();
                return;
            }
            
            if (!$('#specials_description').val().length || $('#specials_description').val().length > 200) {
                alert(appState.get('locale').get('specify_valid_special_description'));
                $('#specials_description').focus();
                return;
            }
            
            if (!$('#specials_store').val().length || $('#specials_store').val().length > 40) {
                alert(appState.get('locale').get('specify_valid_store_name'));
                $('#specials_store').focus();
                return;
            }
            
            var website = $('#specials_website').val();
            if(website.length > 0 && !_(website).startsWith("http") && !_(website).startsWith("//")) {
                $('#specials_website').val("http://" + website);
            }
            // if ($('#specials_website').val().length !== 0 &&  !/^(http\:\/\/|https\:\/\/|\/\/)?([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/.test($('#specials_website').val())) {
            //     alert(appState.get('locale').get('specify_valid_website_address'));
            //     $('#specials_website').focus();
            //     return;
            // }
            
            if ($('#specials_phone').val().length !== 0 && !/^[\d-\s]+$/.test($('#specials_phone').val())) {
                alert(appState.get('locale').get('specify_valid_phone_number'));
                $('#specials_phone').focus();
                return;
            }
            
            var longitude = appState.get('position').lng();
            var latitude = appState.get('position').lat();
            
            var image = $('#specials_image')[0].files[0];
            if (image && image.size > 1024 * 1024 * 4) {
                alert(appState.get('locale').get('image_too_big'));
                return;
            }
            
            var store_logo = $('#specials_store_logo')[0].files[0];

            
            if ($('.image img').length) image = $('.image img').attr('src');
            

            //if ($('.image_store img').length) store_logo = $('.image_store img').attr('src');

             var values = {
                batch_id: batchId,
                token_id: tokenId,
                name: $('#specials_name').val(),
                description: $('#specials_description').val(),
                store: $('#specials_store').val(),
                address: $('#specials_addres').val(),
                website: $('#specials_website').val(),
                country: $('#specials_country').val(),
                country_code: $('#specials_country_code').val(),
                phone: $('#specials_phone').val(),
                let_admin_choose_image: $('#specials_admin_image').is(':checked'),
                urgent: $('#specials_urgent').is(':checked'),
                valid_for: $('#specials_days input').val(),
                longitude: longitude,
                latitude: latitude,
                image: image,
                store_logo_bg: $('#store_logo_bg').val(),
            };

            if (store_logo){
                values.store_logo = store_logo;
            }

            function testImageSize(element, callback) {
                if (element && typeof element === 'object') {
                    var _URL = window.URL;
                    var  img;
                    img = new Image();
                    img.onload = function () {
                        if (this.width * this.height < 640000) {
                            alert(appState.get('locale').get('image_resolution_is_too_small'));
                            return;
                        }
                        callback(values);
                    };
                    img.src = _URL.createObjectURL(element);
                } else {
                    callback(values);
                }
            }
            
            testImageSize(image, callback);
        },
        
        previewSpecials: function() {
            this.validateSpecial(function(values) { 
                $('.special_preview-content').html('');
                var v = ['batch_id', 'token_id', 'name', 'description', 'store', 'address', 'website', 'country', 'phone', 'image'], value;
                
                for (var i in values) {
                    if (v.indexOf(i) !== -1) {
                        value = values[i]? values[i] : '---';
                        if (i === 'image' && values[i] !== '---' && typeof values[i] !== 'undefined') {
                            
                            // upload image to server for preview and auto rotating                            
                            var FR = new FileReader();
                            FR.onload = function(e) {
                                $('.special_preview-content').append('<img id="_preview_img" src="' + e.target.result + '" style="max-width: 190px; display: none;" />');
                                 
                                EXIF.getData($('#_preview_img').get(0), function() {
                                    var orientation = EXIF.getTag(this, "Orientation");
                                    $('.preview_field-value_image').html('<div id="preview_img" style="display: none; background: url(' + $(this).attr('src') + '); background-position: center; width: 190px; height: 190px; background-size:cover"></div>');
                                    if (orientation === 6) {
                                        $('#preview_img').addClass('img_90r');
                                    } else if (orientation === 8) {
                                        $('#preview_img').addClass('img_90l');
                                    } else if (orientation === 3) {
                                        $('#preview_img').addClass('img_180');
                                    }
                                    
                                    $('#preview_img').fadeIn();
                                });
                            };       
                            
                            FR.readAsDataURL(values[i]);
                            value = '<div class="preview_field-value_image">Loading...</div>';
                        }
                        $('.special_preview-content').append('<div class="preview_field"><span>' + appState.get('locale').get(i) + ': </span> <div class="preview_field-value">' + value + '</div></div>');
                    }
                }
                
                $('.special_preview').show();
                window.scrollTo(0, 0);
            });
        },
        
        editSpecials: function() {
            $('.special_preview').hide();
        },

        addSpecials: function() {
            
            var this_ = this;
            
            this.validateSpecial(function(values) {

                var specials = new Specials();
                
                // preloader
                $('.field_buttons a, .field_buttons input').hide();
                $('.field_buttons').append('<img src="/i/small_loader.gif" style="margin: 0px auto; height: auto; width: auto; display: block;" />');
                
                function remove_preloader() {
                    $('.field_buttons a, .field_buttons input').show();
                    $('.field_buttons img').remove();
                }
                // preloader end
                
                var data_ = new FormData();
                $.each(values, function(key, value) {
                        data_.append(key, value);
                });
                data_.append('reuse_id', appState.get('reuse_id'));

                if ($('#source_id').length) values.source_id = $('#source_id').val();

                // checking batch and token
                $.get('/api/is_valid/' + values.batch_id + '/' + values.token_id, {}, function(data){
                    if (data.success) {

                        $.ajax({
                            url: this_.add_link,
                            type: 'POST',
                            data: data_,
                            cache: false,
                            dataType: 'json',
                            processData: false, // Don't process the files
                            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                            success: function(data, textStatus, jqXHR)
                            {
                                if (data.status === 'success') {
                                    
                                    this_.preloader();
                                    
                                    var batch = new Batch({id: values.batch_id});
                                    batch.fetch({

                                        success: function (batch) {
                                            appState.attributes.token = null;
                                            appState.attributes.batch = batch;

                                            // google analitics trigger
                                            ga('send', 'event', 'website_action', appState.get('reuse_id')? appState.get('locale').get('special_reused') : appState.get('locale').get('special_created'));
                                            if (values.let_admin_choose_image) ga('send', 'event', 'website_action', appState.get('locale').get('admin_should_add_photo'));

                                            appState.attributes.reuse_id = null;

                                            // going to queued specials screen
                                            router.navigate("!/specials_list/2", true);
                                        }, 
                                        error: function() {
                                            appState.set({token: null});
                                            appState.set({invalid_batch_id: appState.get('batch').id});
                                            router.navigate("!/batch_not_found", true);
                                        }

                                    });
                                } else {
                                    $('#special_preview').hide();
                                    alert(appState.get('locale').get('special_creation_failed') + ' ' + data.error.charAt(0).toUpperCase() + data.error.slice(1)) + '.';
                                    remove_preloader();
                                }
                            },
                            error: function(data, textStatus, errorThrown) {
                                $('#special_preview').hide();
                                alert(appState.get('locale').get('special_creation_failed'));
                                remove_preloader();
                            }
                        });


                    } else {
                        $('#special_preview').hide();
                        alert(appState.get('locale').get('invalid_batch_or_token'));
                        remove_preloader();
                    }

                });
                
            });

        },

        preloader: function() {
            $("#content").html('<img src="/i/loader.gif" style="position: absolute; top: 50%; left: 50%; margin-top: -33px; margin-left: -33px; display: block;" />');
        },
        
        init_store_logo: function() {
            // initiate store logo image preview
            $('#specials_store_logo').change(function() {
                if ( $('#specials_store_logo')[0].files && $('#specials_store_logo')[0].files[0] ) {
                    var FR = new FileReader();
                    FR.onload = function(e) {
                        $('.store_logo_holder .image_store').remove();
                        $('.store_logo_holder').append('<div class="image_store">' +
                            '<div class="image_wrapper" style="background: ' + $('#store_logo_bg').val() + '"><span></span><img src="' + e.target.result + '" /></div>' +
                            '<i>x</i>' +
                        '</div>');
                        $('.store_logo_holder i').click(function() {
                            $('#specials_store_logo')[0].value = '';
                            $('#specials_store_logo')[0].type = 'text';
                            $('#specials_store_logo')[0].type = 'file';
                            $('.store_logo_holder .image_store').remove();
                        });
                    };       
                    FR.readAsDataURL( $('#specials_store_logo')[0].files[0] );
                }
            });

            $('#store_logo_bg').ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    $('.store_logo_holder .image_wrapper').css('background', '#' + hex);
                    $('#store_logo_bg').val('#' + hex);
                },
                onChange: function(hsb, hex, rgb, el) {
                    $('.store_logo_holder .image_wrapper').css('background', '#' + hex);
                    $('#store_logo_bg').val('#' + hex);
                }
            });
        },
        
        googlemap: function() {
            
            var mapOptions = {
                center: new google.maps.LatLng(-33.8688, 151.2195),
                zoom: 10
            };

            var map = new google.maps.Map(document.getElementById('map'), mapOptions);
            var input = (document.getElementById('specials_addres'));
            
            var autocomplete = new google.maps.places.Autocomplete(input);

            var marker = new google.maps.Marker({
                map: map,
                draggable: true,
                anchorPoint: new google.maps.Point(0, -29)
            });
            
            google.maps.event.addListener(marker, 'dragend', function(event) {
                
                appState.attributes.position = false;
                
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    latLng: marker.getPosition()
                }, function(responses) {
                    if (responses && responses.length > 0) {
                        
                        // save country name
                        for (var i in responses[0].address_components) {
                            if (responses[0].address_components[i].types[0] === 'country') {
                                $('#specials_country').val(responses[0].address_components[i].long_name);
                                $('#specials_country_code').val(responses[0].address_components[i].short_name);
                            }
                        }
                        
                        appState.attributes.position = marker.getPosition();
                        $('#specials_addres').val(responses[0].formatted_address);
                    } else {
                        $('#specials_addres').val(appState.get('locale').get('cannot_determinate_address'));
                    }
                });
            });

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                
                appState.attributes.position = false;
                
                marker.setVisible(false);

                var place = autocomplete.getPlace();

                if (!place.geometry) {
                    return;
                }
                
                // save country name
                for (var i in place.address_components) {
                    if (place.address_components[i].types[0] === 'country') {
                        $('#specials_country').val(place.address_components[i].long_name);
                        $('#specials_country_code').val(place.address_components[i].short_name);
                    }
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);  // Why 17? Because it looks good.
                }
                marker.setIcon(/** @type {google.maps.Icon} */({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                }));

                marker.setPosition(place.geometry.location);
                marker.setVisible(true);
                
                appState.attributes.position = marker.getPosition();
            });
            
            // fix bag with first select
            $(input).blur(function() {
                var val = $(input).val();
                setTimeout(function() {
                    if (val === $(input).val()) return;
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        address: $(input).val()
                    }, function(responses) {
                        if (responses && responses.length > 0) {
                            
                            // save country name
                            for (var i in responses[0].address_components) {
                                if (responses[0].address_components[i].types[0] === 'country') {
                                    $('#specials_country').val(responses[0].address_components[i].long_name);
                                    $('#specials_country_code').val(responses[0].address_components[i].short_name);
                                }
                            }
                        
                            marker.setPosition(responses[0].geometry.location);
                            marker.setIcon(/** @type {google.maps.Icon} */({
                                url: 'https://maps.gstatic.com/mapfiles/ms2/micons/red-dot.png',
                                size: new google.maps.Size(71, 71),
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(17, 34),
                                scaledSize: new google.maps.Size(35, 35)
                            }));
                            marker.setVisible(true);
                            map.setCenter(responses[0].geometry.location);
                            map.setZoom(8); 
                            appState.attributes.position = marker.getPosition();
                            $('#specials_addres').val(responses[0].formatted_address);
                        } else {
                            $('#specials_addres').val(appState.get('locale').get('cannot_determinate_address'));
                        }
                    });
                }, 100);
            });
            
            if (typeof appState.get('special') !== 'undefined' && appState.get('special').longitude && appState.get('special').latitude) {
                var latLng = new google.maps.LatLng(parseFloat(appState.get('special').latitude), parseFloat(appState.get('special').longitude));
                map.setCenter(latLng);
                map.setZoom(10); 
                marker.setPosition(latLng);
                marker.setVisible(true);
                appState.attributes.position = latLng;
            }
            
        },
        
        autocomplete: function() {
            
            var values = {
                serviceUrl: '/api/autocomplete/store_names', 
                minChars: 2, 
                delimiter: /(,|;)\s*/,
                maxHeight: 200, 
                width: 194, 
                zIndex: 9999, 
                deferRequestBy: 300
            };
            
            
            $('#specials_store').autocomplete(values);
            
            var autocomplete = new google.maps.places.Autocomplete(document.getElementById('specials_addres'), {});
        }
        
    });

    var initialize = function(h) {

        header = h;
        var block = new Block();
        appState.trigger("change");
        
    };
    
    return {
        initialize: initialize
    };
    
});