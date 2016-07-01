define([
    'backbone',
    'models/AppState'
], function(Backbone, appState) {

    
    var Router = Backbone.Router.extend({
        
        routes: {
            "": "specials_list",
            "!/": "specials_list",
            "!/batch_list": "batch_list",
            "!/batch": "batch",
            "!/batch_not_found": "batch_not_found",
            "!/specials_list": "specials_list",
            "!/server_error": "server_error",
            "!/new_batch": "new_batch",
            "!/special/:id": "special_edit",
            
            "!/managers": "managers",
            "!/managers/new": "managers_new",
            "!/managers/edit/:id": "managers_edit",
            
            "!/transactions": "transactions",
            "!/invoices": "invoices",
        },

        batch_list: function () {
            if (appState.get('batchList') === null) controller.navigate("!/", true);
            else appState.set({ state: "batch_list" });
        },

        specials_list: function () {
            if (appState.get('specialsList') === null) controller.navigate("!/", true);
            else appState.set({ state: "specials_list" });
        },

        server_error: function () {
            appState.set({ state: "server_error" });
        },

        batch_not_found: function () {
            if (appState.get('invalid_batch_id') === null || typeof appState.get('invalid_batch_id') === 'undefined') this.navigate("", true);
            appState.set({ state: "batch_not_found" });
        },

        batch: function () {
            if (appState.get('batch') === null || typeof appState.get('batch') === 'undefined') this.navigate("", true);
            appState.set({ state: "batch" });
        },

        new_batch: function () {
            appState.set({ state: "new_batch" });
        },

        managers: function () {
            appState.set({ state: "managers" });
        },

        transactions: function () {
            appState.set({ state: "transactions" });
        },

        invoices: function () {
            appState.set({ state: "invoices" });
        },

        managers_new: function () {
            appState.set({ state: "new_manager" });
        },

        managers_edit: function (manager_id) {
            appState.set({
                manager_edit_id: manager_id,
                state: "manager_edit" 
            });
        },

        special_edit: function (special_id) {
            appState.set({
                special_edit_id: special_id,
                state: "special_edit" 
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




