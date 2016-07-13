define([
  'jquery',
  'underscore_mixin',
  'backbone',
  'router',
  'moment',
  'models/AppState',
  'models/Batch',
  'models/Manager',
  'models/BatchList',
  'models/Specials',
  'text!templates/invoices.html',
  'text!templates/invoice_info.html',
  'text!templates/transactions_list.html',
  'text!templates/managers_list.html',
  'text!templates/new_manager.html',
  'text!templates/manager_edit.html',
  'text!templates/batch_not_found.html',
  'text!templates/specials_list.html',
  'text!templates/new_batch.html',
  'text!templates/batch_info.html',
  'text!templates/specials_info.html',
  'text!templates/server_error.html',
  'text!templates/batch_list.html',
  'text!templates/special_edit.html',
  'text!templates/batch.html',
  'async!http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false!callback',
  'ddslick',
  'jquery_autocomplete',
  'colorpicker',
  'niceinput',
  'datatables',
  'daterangepicker'
], function($, _, Backbone, Router, moment, appState, Batch, Manager, BatchList, Specials, InvoicesTemplate, InvoiceInfoTemplate, TransactionsListTemplate, ManagersListTemplate, NewManagerTemplate, ManagerEditTemplate, BatchNotFoundTemplate, SpecialsListTemplate, NewBatchTemplate, BatchInfoTemplate, SpecialsInfoTemplate, ServerErrorTemplate, BatchListTemplate, SpecialEditTemplate, BatchTemplate){
    
    var router = Router.initialize();
    
    var Block = Backbone.View.extend({

        el: $("#content"),
        
        review_link: '/api/frontend/review_special',
        manager_create_link: '/api/frontend/manager_create',
        manager_update_link: '/api/frontend/manager_update',
        manager_reserve_tokens_link: '/api/frontend/reserve_tokens',
        manager_transactions_link: '/api/frontend/get_transactions',
        manager_transactions_export: '/api/frontend/info_transactions',

        //generate_link: 'http://stinjee.com/stinjee/c.php',
        
        model: appState,

        events: {
            "click .batch_button": "getBatch",
            "click #batch_confirm": "createBatch",
            "click #create_report": "createInvoiceReport",
            "click .aprove_button": "aproveSpecials",
            "click .reactivate_button": "reactivateSpecials",
            "click .decline_button": "declineSpecials",
            "click .specials_item": "showSpecial",
            "click .batch_item": "showBatch",
            "click .invoice_item-item": "showInvoice",
            "click #batch_load_more": "loadMoreBatches",
            "click #manager_load_more": "loadMoreManagers",
            "click #transaction_load_more": "loadMoreTransactions",
            "click #specials_all_load_more": "loadMoreAllSpecials",
            "click #specials_active_load_more": "loadMoreActiveSpecials",
            "click #specials_queued_load_more": "loadMoreQueuedSpecials",
            "click #specials_declined_load_more": "loadMoreDeclinedSpecials",
            "click #manager_confirm": "createManager",
            "click .delete_manager": "deleteManager",
            "click #manager_save": "saveManager",
            "click #reserve_tokens": "reserveTokens",
            "change #csv": "sendCsv"
        },

        templates: {
            "batch_list": _.template(BatchListTemplate),
            "new_batch": _.template(NewBatchTemplate),
            "batch_not_found": _.template(BatchNotFoundTemplate),
            "specials_list": _.template(SpecialsListTemplate),
            "server_error": _.template(ServerErrorTemplate),
            "special_edit": _.template(SpecialEditTemplate),
            "batch": _.template(BatchTemplate),
            
            "managers": _.template(ManagersListTemplate),
            "new_manager": _.template(NewManagerTemplate),
            "manager_edit": _.template(ManagerEditTemplate),
            
            "transactions": _.template(TransactionsListTemplate),
            "invoices": _.template(InvoicesTemplate),
        },

        initialize: function () { 
            
            this.model.bind('change', this.render, this);            

        },
        
        select_change: function(data) {
            
            if (data.selectedData.value === '') {                
                $('.invoice_item-item').show();
                $('.no_invoices').hide();
            }
            
            if ($(data.original.context).hasClass('batch_item_header-select') && $('#invoice_month').data('ddslick') && $('#invoice_country').data('ddslick') && $('#invoice_manager').data('ddslick')) {
                
                // update visable invoices
                var info = {
                    month: $('#invoice_month').data('ddslick').selectedData.value,
                    country: $('#invoice_country').data('ddslick').selectedData.value,
                    manager: $('#invoice_manager').data('ddslick').selectedData.value
                };
                
                $('.invoice_item-item').each(function() {
                    var show = true;
                    
                    if (info.month) {
                        if ($(this).data('month') !== info.month) show = false;
                    }
                   
                    if (info.country) {
                        if ($(this).data('country') !== info.country) show = false;
                    }
                    
                    if (info.manager) {
                        if ($(this).data('manager') !== info.manager) show = false;
                    }
                    
                    $(this).css('display', show? 'block' : 'none');
                    
                    if ($('.invoice_item-item:visible').length === 0) {
                        $('.no_invoices').show();
                    } else {
                        $('.no_invoices').hide();
                    }
                });
            }
            
            if ($(data.original.context).hasClass('status_select')) {
                if (data.selectedData.value == $(data.selectedItem[0]).parents('.status_tooltip').data('status')) return;
                
                $.post('/api/update_invoice_status', {invoice: $(data.original.context).data('invoice'), status: data.selectedData.value}, function(res) {
                    if (res.success === true) {
                        $(data.selectedItem[0]).parents('.status_tooltip').data('status', data.selectedData.value);
                        $(data.selectedItem[0]).parents('.status_tooltip').hide();
                        $(data.selectedItem[0]).parents('.specials_name').find('> a').html(data.selectedData.text);
                    } else {
                        
                    }
                });
            }
        },

        render: function () {
            
            var state = this.model.get("state"), this_ = this;
            
            function afterRender() {
                
                $(this_.el).html(this_.templates[state](this_.model.toJSON()));
                
                // replace dropdowns
                // for some reason this doesn't work due to the loading sequence
                $('select').each(function() {
                    $(this).ddslick({
                        onSelected: this_.select_change
                    });
                });

                /* Custom filtering function which will search data in column 4 (start date) between two values */
                //enable datatables
                var tables = $('.table_specials_list').DataTable( {
                    "dom": 'l<"toolbar">frtip'
                });

                $("div.toolbar").html('<label>Search start date: <input type="search" class="date_range" aria-controls="DataTables_Table_2"></label>');

                // Date range script - Start of the sscript
                $(".date_range").daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        "cancelLabel": "Clear",
                    }
                });

                $(".date_range").on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
                    tables.draw();
                });

                $(".date_range").on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    tables.draw();
                });
                // Date range script - END of the script

                $.fn.dataTableExt.afnFiltering.push(
                    function(oSettings, aData, iDataIndex) {

                        var grab_daterange = $("#date_range").val();
                        var give_results_daterange = grab_daterange.split(" to ");
                        var filterstart = give_results_daterange[0];
                        var filterend = give_results_daterange[1];
                        var iStartDateCol = 4; //using column 2 in this instance
                        var iEndDateCol = 4;
                        var tabledatestart = aData[iStartDateCol];
                        var tabledateend = aData[iEndDateCol];

                        if (!filterstart && !filterend) {
                            return true;
                        } else if ((moment(filterstart).isSame(tabledatestart) || moment(filterstart).isBefore(tabledatestart)) && filterend === "") {
                            return true;
                        } else if ((moment(filterstart).isSame(tabledatestart) || moment(filterstart).isAfter(tabledatestart)) && filterstart === "") {
                            return true;
                        } else if ((moment(filterstart).isSame(tabledatestart) || moment(filterstart).isBefore(tabledatestart)) && (moment(filterend).isSame(tabledateend) || moment(filterend).isAfter(tabledateend))) {
                            return true;
                        }
                        return false;
                    }
                );
                
                // for some reason this doesn't work due to the loading sequence
                $(".field input[type=file]").nicefileinput({
                    label: appState.get('locale').get('choose_file')
                });

                if (state === 'special_edit') {
                    this_.googlemap();
                    this_.autocomplete();
                    
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

                        //first select fix
                        document.getElementById("specials_addres").focus();
                        document.getElementById("specials_addres").blur();

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
                }
                
                if (typeof this_[state + '_after'] === 'function') {
                    this_[state + '_after']();
                }
            }
            
            
            if (typeof this[state] === 'function') {
                this[state](afterRender);
            }
            else {
                afterRender();
            }

            return this;
            
        },
        
        managers: function(callback) {
            this.preloader();
            $.get('/api/managers/list', {}, function(data) {
                appState.attributes.managers = data;
                callback();
            });
        },
        
        transactions: function(callback) {
            this.preloader();
            $.get('/api/transactions/list', {}, function(data) {
                appState.attributes.transactions = data;
                callback();
            });
        },
        
        invoices: function(callback) {
            this.preloader();
            $.get('/api/invoices/list', {}, function(data) {
                appState.attributes.invoices = data;                
                callback();
            });
        },
        
        createInvoiceReport: function(e) {
            var info = {
                month: $('#invoice_create_month').data('ddslick').selectedData.value,
                country: $('#invoice_create_country').data('ddslick').selectedData.value,
                type: $('#invoice_create_type').data('ddslick').selectedData.value,
                
                /*manager: $('#invoice_create_manager').data('ddslick').selectedData.value*/
            };
            
            if (!info.month) {
                alert(appState.get('locale').get('specify_month'));
                return;
            }
            
            /*if (!info.manager) {
                alert(appState.get('locale').get('specify_manager'));
                return;
            }*/
            
            if (!info.country) {
                alert(appState.get('locale').get('specify_country'));
                return;
            }
            
            window.location.href = this.manager_transactions_export + '?month=' + info.month + '&type=' + info.type + '&country=' + info.country;
            
            return false;
        },
        
        showInvoice: function(e) {
            var el = $(e.target).hasClass('invoice_item-item')? $(e.target).find('.specials_full_info') : $(e.target).parents('.invoice_item-item').find('.specials_full_info');
            
            if (el.is(':animated')) {
                return;
            } else if (el.is(':visible')) {
               el.slideUp();
            } else {
                el.html('<img src="/i/small_loader.gif" style="margin: 40px auto; height: auto; width: auto;display: block;" />').slideDown();
                this.renderInvoiceInfo(el);
            }
        },
        
        renderInvoiceInfo: function(el) {
            var p = $(el).parents('.invoice_item-item');
            this.getInvoiceInfo($(p).data('month'), $(p).data('manager-id'), function(info) {    
                info['locale'] = appState.get('locale');
                el.html(_.template(InvoiceInfoTemplate)(info));
                el.find('.transaction-item').click(function() {
                    $(this).find('.transaction-item-all-tokens').slideToggle();
                })
            });
        },
        
        getInvoiceInfo: function(month, manager_id, callback) {
            
            $.post(this.manager_transactions_link, {month: month, manager_id: manager_id}, function(data) {
                if (data.success == true) {
                    callback(data);
                } else {
                   router.navigate("!/server_error", true); 
                }
            });
            
        },
       
        invoices_after: function() {
            $(document).click(function() {
                $('.status_tooltip, .menu menu_right').hide();
            });           
            
            $('.status_tooltip, .specials_name > a').click(function(e) {
                e.stopPropagation();
            });
        },
        
        manager_edit: function(callback) {
            this.preloader();
            
            $.get('/api/manager/' + this.model.get("manager_edit_id"), {}, function(data) {
                appState.attributes.manager = data;
                callback();
            });
        },
        
        special_edit: function(callback) {
            
            this.preloader();
            
            $.get('/api/specials/f/' + this.model.get("special_edit_id"), {}, function(data) {
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
        
        specials_list: function(callback) {
            
            this.preloader();
            
            $.get('/api/specials/list/queued', {}, function(data) {
                appState.attributes.specials = data;
                callback();                
            });                      
        },
        
        new_batch: function(callback) {
            
            this.preloader();
            
            $.get('/api/managers/list', {}, function(data) {
                appState.attributes.managers = data;
                callback();
            });
            
            /*$.get('/api/countries', {}, function(data) {
                appState.attributes.manager_countries = data;
                callback();
            });*/
            
        },
        
        batch_list: function(callback) {
            
            this.preloader();

            var batchList = new BatchList();
            batchList.fetch({
                success: function (batchList) {
                    appState.attributes.batchList = batchList;
                    callback();
                }, 
                error: function() {
                    router.navigate("!/server_error", true);
                }
            });
            
        },
        
        deleteManager: function(e) {
            e.stopPropagation();
            
            var this_ = this;
            
            if (confirm(appState.get('locale').get('manager_delete_confirm'))) {
                this.preloader();
                $.get('/api/manager/delete/' + e.target.id, {}, function(data) {
                    this_.render();
                });
            }
            
            return false;
        },
        
        saveManager: function() {
            if (!this._validateManager()) {
                return false;
            }
            
            $('.field_buttons:eq(0) a, .field_buttons:eq(0) input').hide();
            $('.field_buttons:eq(0)').append('<img src="/i/small_loader.gif" style="margin: 0px auto; height: auto; width: auto; display: block;" />');
            
            function remove_preloader() {
                $('.field_buttons:eq(0) a, .field_buttons:eq(0) input').show();
                $('.field_buttons:eq(0) img').remove();
            }
            
            var this_ = this;
            
            var data = new FormData();
            $.each({
                    id: this_.model.get("manager_edit_id"),
                    name: $('#manager_name').val(),
                    email: $('#manager_email').val(),
                    country: $('#manager_country input').val()
                }, function(key, value) {
                    data.append(key, value);
            });
            
            $.ajax({
                url: this.manager_update_link,
                type: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                processData: false, // Don't process the files
                contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                success: function(data, textStatus, jqXHR) {

                    if (data.status === 'success') {
                        this_.preloader();
                        router.navigate("!/managers", true);
                    } else {
                        alert(appState.get('locale').get('manager_creation_failed') + ' ' + data.error.charAt(0).toUpperCase() + data.error.slice(1)) + '.';
                        remove_preloader();
                    }

                },
                error: function(data) {
                    alert(appState.get('locale').get('manager_creation_failed'));
                    remove_preloader();
                }
            });
        },
        
        reserveTokens: function() {
            $('.field_buttons:eq(1) a, .field_buttons:eq(1) input').hide();
            $('.field_buttons:eq(1)').append('<img src="/i/small_loader.gif" style="margin: 0px auto; height: auto; width: auto; display: block;" />');
            
            function remove_preloader() {
                $('.field_buttons:eq(1) a, .field_buttons:eq(1) input').show();
                $('.field_buttons:eq(1) img').remove();
            }
            
            var this_ = this;
            
            var data = new FormData();
            $.each({
                    id: this_.model.get("manager_edit_id"),
                    tokens: $('#manager_tokens').val()
                }, function(key, value) {
                    data.append(key, value);
            });
            
            $.ajax({
                url: this.manager_reserve_tokens_link,
                type: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                processData: false, // Don't process the files
                contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                success: function(data, textStatus, jqXHR) {

                    if (data.status === 'success') {
                        this_.preloader();
                        window.location.reload();
                    } else {
                        alert(appState.get('locale').get('token_reservation_failed') + ' ' + data.error.charAt(0).toUpperCase() + data.error.slice(1)) + '.';
                        remove_preloader();
                    }

                },
                error: function(data) {
                    alert(appState.get('locale').get('token_reservation_failed'));
                    remove_preloader();
                }
            });
        },
        
        loadMoreBatches: function() {
            $('#batch_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/batch/list', {offset: appState.get('batchList').size(), limit: 20}, function(items) {
                appState.get('batchList').add(items);
                $(this_.el).html(this_.templates['batch_list'](this_.model.toJSON()));
                if (items.length == 20) { 
                    $(this_.el).find('.specials_list').append('<button class="button load_more" id="batch_load_more">' + appState.get('locale').get('load_more') + '</button>');
                }
            });
        },
        
        loadMoreManagers: function() {
            $('#manager_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/managers/list', {offset: appState.get('managers').length, limit: 20}, function(items) {
                for (var i in items) appState.get('managers').push(items[i]);
                $(this_.el).html(this_.templates['managers'](this_.model.toJSON()));
                if (items.length == 20) { 
                    $(this_.el).find('.specials_list').append('<button class="button load_more" id="manager_load_more">' + appState.get('locale').get('load_more') + '</button>');
                }
            });
        },
        
        loadMoreTransactions: function() {
            $('#transaction_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/transactions/list', {offset: appState.get('transactions').length, limit: 20}, function(items) {
                for (var i in items) appState.get('transactions').push(items[i]);
                $(this_.el).html(this_.templates['transactions'](this_.model.toJSON()));
                if (items.length == 20) { 
                    $(this_.el).find('.specials_list').append('<button class="button load_more" id="transaction_load_more">' + appState.get('locale').get('load_more') + '</button>');
                }
            });
        },
        
        loadMoreAllSpecials: function() {
            $('#specials_all_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/specials/list/all', {offset: appState.get('specials').all.length, limit: 20000}, function(items) {
                appState.attributes.specials.all = appState.attributes.specials.all.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(0)').trigger('click');
                if (items.length !== 20) $('#specials_all_load_more').remove();
            });
        },
        
        loadMoreActiveSpecials: function() {
            $('#specials_active_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/specials/list/active', {offset: appState.get('specials').active.length, limit: 20000}, function(items) {
                appState.attributes.specials.active = appState.attributes.specials.active.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(1)').trigger('click');
                if (items.length !== 20) $('#specials_active_load_more').remove();
            });
        },
        
        loadMoreQueuedSpecials: function() {
            $('#specials_queued_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/specials/list/queued', {offset: appState.get('specials').queued.length, limit: 20000}, function(items) {
                appState.attributes.specials.queued = appState.attributes.specials.queued.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(2)').trigger('click');
                if (items.length !== 20) $('#specials_queued_load_more').remove();
            });
        },
        
        loadMoreDeclinedSpecials: function() {
            $('#specials_declined_load_more').html('<img src="/i/small_loader.gif" style="margin: 5px 20px 6px; height: auto; width: auto; display: block;" />');
            
            var this_ = this;
            $.get('/api/specials/list/declined', {offset: appState.get('specials').declined.length, limit: 20000}, function(items) {
                appState.attributes.specials.declined = appState.attributes.specials.declined.concat(items);
                $(this_.el).html(this_.templates['specials_list'](this_.model.toJSON()));
                $('.menu_right a:eq(3)').trigger('click');
                if (items.length !== 20) $('#specials_declined_load_more').remove();
            });
        },
        
        printTokens: function(e) {
            var batchId = $(e.target).parents('.batch_item').attr('id');
            var date = new Date();
            var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
            
            this.getBatchInfo(batchId, function(info) {
                var text = info.tokens.valid.length + ' Stin Jee tokens for creating specials;<br/>';
                text += 'Generated on ' + date.getUTCDate() + ' ' +  monthNames[date.getMonth()] + ' ' +  date.getFullYear() + ';<br/>';
                text += 'To upload specials, please go to: http://client.stinjee.com;<br/>';
                text += ';<br/>';
                text += 'Batch ID: ;' + batchId + '<br/>';
                text += ';<br/>';
                text += 'Token IDs;<br/>';
                text += ';<br/>';
                text += info.tokens.valid.join(';<br/>') + ';';

                var win = window.navigator.userAgent.indexOf("MSIE ") !== -1? window.open("", "_blank", 'height=600,width=400') : window.open("", "_blank");
                win.document.write(text);
                win.document.close();
                win.focus();
                win.print();
                win.close();
            });
        },

        getBatch: function(e) {

            this.preloader();
            var batch = new Batch({id: e.target.id});

            batch.fetch({
                success: function (batch) {
                    appState.set({batch: batch});
                    router.navigate("!/batch", true);
                }, 
                error: function() {
                    appState.set({invalid_batch_id: e.target.id});
                    router.navigate("!/batch_not_found", true);
                }
            });

        },
        
        _validateManager: function() {
            if (!$('#manager_name').val().length || $('#manager_name').val().length > 64) {
                alert(appState.get('locale').get('specify_valid_manager_name'));
                $('#manager_name').focus();
                return false;
            }
            
            if (!/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test($('#manager_email').val())) {
                alert(appState.get('locale').get('specify_valid_manager_email_address'));
                $('#manager_email').focus();
                return false;
            }
            
            if (!$('#manager_country input').val().length || $('#manager_country input').val().length > 64) {
                alert(appState.get('locale').get('specify_valid_manager_country'));
                $('#manager_country').focus();
                return false;
            }
            
            return true;
        },
        
        createManager: function(e) {
            
            if (!this._validateManager()) {
                return false;
            }
            
            $('.field_buttons a, .field_buttons input').hide();
            $('.field_buttons').append('<img src="/i/small_loader.gif" style="margin: 0px auto; height: auto; width: auto; display: block;" />');
            
            function remove_preloader() {
                $('.field_buttons a, .field_buttons input').show();
                $('.field_buttons img').remove();
            }
            
            var this_ = this;
            
            var data = new FormData();
            $.each({
                    name: $('#manager_name').val(),
                    email: $('#manager_email').val(),
                    country: (($('#manager_country input').val() == 'undefined') ? $('#manager_country option:checked').val() : $('#manager_country input').val()),
                }, function(key, value) {
                    data.append(key, value);
            });
            
            $.ajax({
                url: this.manager_create_link,
                type: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                processData: false, // Don't process the files
                contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                success: function(data, textStatus, jqXHR) {

                    if (data.status === 'success') {
                        this_.preloader();
                        router.navigate("!/managers", true);
                    } else {
                        alert(appState.get('locale').get('manager_creation_failed') + ' ' + data.error.charAt(0).toUpperCase() + data.error.slice(1)) + '.';
                        remove_preloader();
                    }

                },
                error: function(data) {
                    alert(appState.get('locale').get('manager_creation_failed'));
                    remove_preloader();
                }
            });
            
        },

        createBatch: function(e) {

            // validate tokens num
            if (!/^\d+$/.test($('#tokens_num').val())) {
                alert(appState.get('locale').get('invalid_tokens_number'));
                return;
            }
            
            // validate batches num
            if (!/^\d+$/.test($('#batches_num').val())) {
                alert(appState.get('locale').get('invalid_batches_number'));
                return;
            }
            if (!($('#batch_manager .dd-selected-value').val())) {
                alert(appState.get('locale').get('invalid_batches_manager'));
                return;
            }
            
            var tokensNum = parseInt($('#tokens_num').val());
            var batchesNum = parseInt($('#batches_num').val());
            var batchesPrice = parseFloat($('#batches_price').val());
            var promotion =  $('#promo_tokens').is(':checked');
            var manager = $('#batch_manager .dd-selected-value').val();
            
            if (tokensNum > 1000) {
                alert(appState.get('locale').get('maximum_value_number', [1000]));
                $('#tokens_num').val(1000);
                return;
            }
            
            if (batchesNum > 50) {
                alert(appState.get('locale').get('maximum_value_number', [50]));
                $('#batches_num').val(50);
                return;
            }
            
            if (tokensNum < 1) {
                alert(appState.get('locale').get('minimum_value_number', [1]));
                $('#tokens_num').val(1);
                return;
            }
            
            if (batchesNum < 1) {
                alert(appState.get('locale').get('minimum_value_number', [1]));
                $('#batches_num').val(1);
                return;
            }
            
            if (tokensNum === 0 || isNaN(tokensNum)) {
                alert(appState.get('locale').get('enter_valid_number_of_tokens'));
                $('#tokens_num').focus();
                return;
            }
            
            if (batchesNum === 0 || isNaN(batchesNum)) {
                alert(appState.get('locale').get('enter_valid_number_of_batches'));
                $('#batches_num').focus();
                return;
            }

            this.preloader();
            
            var batchIndex = 0, batches = [];
            function batchSync() {
                
                if (batchIndex >= batchesNum) {
                    // google analitics trigger
                    ga('send', 'event', 'website_action', promotion? appState.get('locale').get('tokens_created_free') : appState.get('locale').get('tokens_created_paid'));

                    // send email with created batches 
                    $.post('/api/batch/email', {batches: batches, promotion: promotion, tokensNum: tokensNum, manager: manager, price: batchesPrice}, function(data) {
                        if (data.success) {
                            router.navigate("!/batch_list", true);
                        } else {
                            router.navigate("!/server_error", true);
                        }
                    });
                } else {
                    runBatch();
                }
            }
            
            function runBatch() {
                
                var bulk = [], batch_id, this_ = this, proccessed = 0, limit = 400;
                for (var i = 0; i < Math.floor(tokensNum / limit); i++) {
                    bulk[i] = limit;
                }

                if (tokensNum % limit) bulk[i] = tokensNum - bulk.length * limit;

                function sync(bid) {
                    batch_id = bid;
                    proccessed++;
                    if (proccessed === bulk.length) {
                        
                        batchIndex++;
                        batches.push(batch_id);
                        batchSync();

                    } else {
                        send();
                    }
                }

                function send() {
                    $.post('/api/batch', {tokensNum: bulk[proccessed], promotion: promotion, batch_id: batch_id, manager: manager, price: batchesPrice}, function(data) {
                        if (typeof data.batchId !== 'undefined') {
                            sync(data.batchId);
                        } else {
                            router.navigate("!/server_error", true);
                        }
                    })
                }
            
                send();
            }
            
            runBatch();
        },

        aproveSpecials: function(e) {
            
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
                $('#specials_website').val("https://" + website);
            }
            
            if ($('#specials_website').val().length !== 0 &&  !/^(http\:\/\/|https\:\/\/|\/\/)?([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(\/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$/.test($('#specials_website').val())) {
                alert(appState.get('locale').get('specify_valid_website_address'));
                $('#specials_website').focus();
                return;
            }
            
            /*if ($('#specials_facebook').val().length !== 0 && !/^(http|https)\:\/\/(www.)?facebook\.com.*$/.test($('#specials_facebook').val())) {
                alert(appState.get('locale').get('specify_valid_facebook_page_address'));
                $('#specials_facebook').focus();
                return;
            }*/
            
            if ($('#specials_phone').val().length !== 0 && !/^[\d-\s]+$/.test($('#specials_phone').val())) {
                alert(appState.get('locale').get('specify_valid_phone_number'));
                $('#specials_phone').focus();
                return;
            }
            
            var longitude = appState.get('position').lng();
            var latitude = appState.get('position').lat();

            var this_ = this;
            
            
            var image = $('#specials_image')[0].files[0];
            if (image && image.size > 1024 * 1024 * 4) {
                alert(appState.get('locale').get('image_too_big'));
                return;
            }
            
            function remove_preloader() {
                $('.field_buttons a, .field_buttons input').show();
                $('.field_buttons img').remove();
            }
            
            function testImageSize(element, callback) {
                if (element) {
                    var _URL = window.URL;
                    var  img;
                    img = new Image();
                    img.onload = function () {
                        if (this.width * this.height < 640000) {
                            alert(appState.get('locale').get('image_resolution_is_too_small'));
                            return;
                        }
                        callback();
                    };
                    img.src = _URL.createObjectURL(element);
                } else {
                    callback();
                }
            }
            
            var store_logo = $('#specials_store_logo')[0].files[0];
            
            testImageSize(image, function() {
            
                if (!image && !$('.image img').length) {
                    alert(appState.get('locale').get('specify_image'));
                    return;
                }
                
                $('.field_buttons a, .field_buttons input').hide();
                $('.field_buttons').append('<img src="/i/small_loader.gif" style="margin: 0px auto; height: auto; width: auto; display: block;" />');

                var values = {
                    id: this_.model.get("special_edit_id"),
                    name: $('#specials_name').val(),
                    description: $('#specials_description').val(),
                    store: $('#specials_store').val(),
                    address: $('#specials_addres').val(),
                    website: $('#specials_website').val(),
                    /*facebook: $('#specials_facebook').val(),*/
                    phone: $('#specials_phone').val(),
                    valid_for: $('#specials_days input').val() || $('#specials_days').val(),
                    let_admin_choose_image: $('#specials_admin_image').is(':checked'),
                    longitude: longitude,
                    latitude: latitude,
                    country: $('#specials_country').val(),
                    country_code: $('#specials_country_code').val(),
                    image: image,
                    store_logo: store_logo,
                    store_logo_bg: $('#store_logo_bg').val(),
                    created_at: $('#specials_created_at').val(),
                    updated_at: $('#specials_updated_at').val()
                };

                var data_ = new FormData();
                $.each(values, function(key, value) {
                        data_.append(key, value);
                });
                
                        
                $.ajax({
                    url: this_.review_link,
                    type: 'POST',
                    data: data_,
                    cache: false,
                    dataType: 'json',
                    processData: false, // Don't process the files
                    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                    success: function(data, textStatus, jqXHR) {

                        if (data.status === 'success') {
                            
                            this_.preloader();

                            // google analitics trigger
                            ga('send', 'event', 'website_action', appState.get('locale').get('special_approved'));

                            router.navigate("!/specials_list", true);
                        } else {
                            alert(appState.get('locale').get('special_creation_failed') + ' ' + data.error.charAt(0).toUpperCase() + data.error.slice(1)) + '.';
                            remove_preloader();
                        }

                    },
                    error: function(data) {
                        alert(appState.get('locale').get('special_creation_failed'));
                        remove_preloader();
                    }
                });

            
            });

        },

        reactivateSpecials: function(e) {
            this.aproveSpecials(e);
        },

        declineSpecials: function(e) {

            this.preloader();
            var this_ = this;

            $.get('/api/specials/decline/' + e.target.id, {}, function(data) {
                
                if (data.success === true) {
                    
                    // google analitics trigger
                    ga('send', 'event', 'website_action', appState.get('locale').get('special_declined'));
                    
                    router.navigate("!/specials_list", true);
                    
                }
                else {
                    alert(data.message.message);
                    this_.render();
                }
                
            });

        },

        showSpecial: function(e) {

            var tr = $(e.target).closest('tr');
            var row = $(e.target).closest('table').DataTable().row( tr );
     
            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                // Open this row
                row.child( '<div class="specials_full_info specials_full_info_datatables" onclick="event.stopPropagation();"></div>' ).show();

                //bind item data
                var specialId = e.target.id || $(e.target).parents('.specials_item').attr('id');                
                this.renderSpecialInfo(specialId, row.child().find('.specials_full_info'));

                //show item data
                tr.addClass('shown');
            }
            
        },

        showBatch: function(e) {
            
            var batchId = e.target.id || $(e.target).parents('.batch_item').attr('id');
            
            if ($('#' + batchId + ' .specials_full_info').is(':animated')) {
                return;
            } else if ($('#' + batchId + ' .specials_full_info').is(':visible')) {
                $('#' + batchId + ' .specials_full_info').slideUp();
            } else {
                $('#' + batchId + ' .specials_full_info').html('<img src="/i/small_loader.gif" style="margin: 40px auto; height: auto; width: auto;display: block;" />').slideDown();
                this.renderBatchInfo(batchId);
            }
            
        },
        
        deactivateBatch: function(e) {
            
            var batchId = $(e.target).parents('.batch_item').attr('id'), this_ = this;
            $('#' + batchId + ' .specials_full_info').html('<img src="/i/small_loader.gif" style="margin: 40px auto; height: auto; width: auto;display: block;" />')
            
            $.get('/api/batch/deactivate/' + batchId, {}, function(data) {
                if (data.success === true) {
                    
                    appState.get('batchList').each(function(item) {
                        if (item.id === batchId) {
                            item.set('active', false);
                            this_.renderBatchInfo(batchId);
                        }
                    });
                    
                } else {
                    router.navigate("!/server_error", true);
                }
            });
            
        },
        
        activateBatch: function(e) {
            
            var batchId = $(e.target).parents('.batch_item').attr('id'), this_ = this;
            $('#' + batchId + ' .specials_full_info').html('<img src="/i/small_loader.gif" style="margin: 40px auto; height: auto; width: auto;display: block;" />')
            
            $.get('/api/batch/activate/' + batchId, {}, function(data) {
                if (data.success === true) {
                    
                    appState.get('batchList').each(function(item) {
                        if (item.id === batchId) {
                            item.set('active', true);
                            this_.renderBatchInfo(batchId);
                        }
                    });
                    
                } else {
                    router.navigate("!/server_error", true);
                }
            });
            
        },
        
        deactivateSpecial: function(e) {
            
            var specialId = $(e.target).parents('.specials_item').attr('id'), this_ = this;
            var el = $(e.target).hasClass('specials_item')? $(e.target).next('tr').find('.specials_full_info') : $(e.target).parents('.specials_item').next('tr').find('.specials_full_info');
            
            $('#' + specialId + ' .specials_full_info').html('<img src="/i/small_loader.gif" style="margin: 40px auto; height: auto; width: auto;display: block;" />')
            
            $.get('/api/specials/deactivate/' + specialId, {}, function(data) {
                if (data.success === true) {
                    
                    this_.renderSpecialInfo(specialId, el);
                    
                } else {
                    router.navigate("!/server_error", true);
                }
            });
            
        },
        
        activateSpecial: function(e) {
            
            var specialId = $(e.target).parents('.specials_item').attr('id'), this_ = this;
            var el = $(e.target).hasClass('specials_item')? $(e.target).next('tr').find('.specials_full_info') : $(e.target).parents('.specials_item').next('tr').find('.specials_full_info');
            
            $('#' + specialId + ' .specials_full_info').html('<img src="/i/small_loader.gif" style="margin: 40px auto; height: auto; width: auto;display: block;" />')
            
            $.get('/api/specials/activate/' + specialId, {}, function(data) {
                if (data.success === true) {
                    this_.renderSpecialInfo(specialId, el);
                } else {
                    router.navigate("!/server_error", true);
                }
            });
            
        },

        preloader: function() {
            $("#content").html('<img src="/i/loader.gif" style="margin: 140px auto; display: block;" />');
        },
        
        getBatchInfo: function(batchId, callback) {
            
            if (appState.get('batchList')) {
                appState.get('batchList').each(function(item) {
                    if (item.id === batchId) {
                        var tokens = item.get('tokens');
                        var info = {
                            id: batchId,
                            tokens: {
                                valid: [],
                                used: []
                            },
                            specials: item.get('specials'),
                            locale: appState.get('locale'),
                            active: item.get('active')
                        }

                        _.each(info.specials, function(item) {
                            info.tokens.used.push(item.tokenId) 
                        });

                        _.each(tokens, function(item) {
                            if (_.indexOf(info.tokens.used, item) === -1) info.tokens.valid.push(item); 
                        });

                        callback(info);
                    }
                });
            }
        },
        
        getSpecialInfo: function(specialId, callback) {
            
            var specials = new Specials({id: specialId}), this_ = this;
            specials.fetch({
                success: function (special) {
                    special.set({'locale': appState.get('locale')});
                    callback(special);
                }, 
                error: function() {
                    router.navigate("!/server_error", true);
                }
            });
            
        },
        
        renderBatchInfo: function(batchId) {
            
            var this_ = this;
            this.getBatchInfo(batchId, function(info) {
                $('#' + batchId).removeClass('deactivated');
                if (!info.active) $('#' + batchId).addClass('deactivated');
                
                $('#' + batchId + ' .specials_full_info').html(_.template(BatchInfoTemplate)(info));
                $('#' + batchId + ' .specials_full_info .batch_deactivate').click(function(e) {
                    this_.deactivateBatch(e);
                });
                $('#' + batchId + ' .specials_full_info .batch_activate').click(function(e) {
                    this_.activateBatch(e);
                });
                
                $('#' + batchId + ' .specials_full_info .print').click(function(e) {
                    this_.printTokens(e);
                });
            });
            
        },
        
        renderSpecialInfo: function(specialId, el) {
            
            var this_ = this;
            this.getSpecialInfo(specialId, function(info) {
                var si = el.closest('tr').prev('.specials_item');
                si.removeClass('deactivated');
                if (!info.get('active')) si.addClass('deactivated');
                
                el.html(_.template(SpecialsInfoTemplate)(info.toJSON()));
                el.find('.special_deactivate').click(function(e) {
                    this_.deactivateSpecial(e);
                });
                el.find('.special_activate').click(function(e) {
                    this_.activateSpecial(e);
                });
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
                    //if (val === $(input).val()) return;
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
                });
            });
            
            if (appState.get('special').longitude && appState.get('special').latitude) {
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
            
        },
        
        /*readImage: function(image, callback) {
            if (!image) callback('');
            else {
                var reader = new FileReader();
                reader.onload = function () {
                    callback(this.result);
                };
                reader.readAsDataURL(image);
            }
        },

        loadImages: function(callback) {
            var block = this;
            block.readImage($('#specials_image')[0].files[0], function(image) {
                callback(image);
            });
        }*/     
    });
    
    

    var initialize = function() {

        var block = new Block();
        appState.trigger("change");
        
    };
    
    return {
        initialize: initialize
    };
    
});