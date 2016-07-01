define([
    'underscore',
    'backbone'
], function(_, Backbone) {

    var Manager = Backbone.Model.extend({
        urlRoot: '/api/manager',
        defaults: {
            id: null,
            name: null,
            email: null,
            country: null
        }
    });

    return Manager;

});


