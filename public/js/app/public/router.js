define([
    'backbone',
    'models/AppState'
], function(Backbone, appState) {

    
    var Router = Backbone.Router.extend({
        
        routes: {
            "": "start",
            "!/": "start",
            "!/batch_not_found": "batch_not_found",
            "!/specials_list": "specials_list_all",
            "!/specials_list/:id": "specials_list",
            "!/specials_list/active": "specials_list_active",
            "!/token": "token",
            "!/invalid_token": "invalid_token",
            "!/new_specials": "new_specials",
            "!/new_specials_error": "new_specials_error",
            "!/specials_success": "specials_success",
            "!/special/reuse/:id": "specials_reuse",
            "!/get_more_tokens": "get_more_tokens",
            "!/payment_error/:message": "payment_error",
            "!/payment_success/:batch_id/:transaction_id": "payment_success",
            "!/invoice_success/:batch_id/:transaction_id": "invoice_success"
        },
        
        start: function() {
            appState.set({state: "start"});
        },
        
        batch_not_found: function() {
            if (appState.get('invalid_batch_id') === null || typeof appState.get('invalid_batch_id') === 'undefined')
                this.navigate("", true);
            appState.set({state: "batch_not_found"});
        },
        
        specials_list_all: function() {
            if (appState.get('batch') === null) this.navigate("", true);
            else appState.set({state: "specials_list", special_list_num: 0});
        },
        
        specials_list: function(id) {
            if (appState.get('batch') === null) this.navigate("", true);
            else {
                appState.set({
                    state: "specials_list",
                    special_list_num: id || 0
                });
            }
        },
        
        payment_error: function(message) {
            appState.set({
                state: "payment_error",
                message: message
            });
        },
        
        payment_success: function(batch_id, transaction_id) {
            appState.set({
                state: "payment_success",
                batch_id: batch_id,
                transaction_id: transaction_id
            });
        },
        
        get_more_tokens: function() {
            appState.set({state: "get_more_tokens"});
        },
        
        invoice_success: function(batch_id, transaction_id) {
            appState.set({
                state: "invoice_success",
                batch_id: batch_id,
                transaction_id: transaction_id
            });
        },
        
        token: function() {
            if (appState.get('batch') === null)
                this.navigate("", true);
            else
                appState.set({state: "token"});
        },
        
        invalid_token: function() {
            if (appState.get('batch') === null)
                this.navigate("", true);
            else
                appState.set({state: "invalid_token"});
        },
        
        new_specials_error: function() {
            if (appState.get('batch') === null)
                this.navigate("", true);
            else
                appState.set({state: "new_specials_error"});
        },
        
        new_specials: function() {
            if (appState.get('batch') === null)
                this.navigate("", true);
            else
                appState.set({state: "new_specials"});
        },
        
        specials_success: function() {
            if (appState.get('batch') === null) this.navigate("", true);
            else appState.set({state: "specials_success"});
        },
        
        specials_reuse: function(id) {
            appState.set({
                reuse_id: id,
                state: 'reuse'
            });
        }
        
    });
    var initialize = function() {

        var router = new Router;
        Backbone.history.start();
        return router;
        
    };
    
    return {
        initialize: initialize
    };
});




