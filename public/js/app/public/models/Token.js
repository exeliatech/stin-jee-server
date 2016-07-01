define([
    'underscore',
    'backbone'
], function(_, Backbone) {

    var Token = Backbone.Model.extend({
        urlRoot: '/api/token',
        defaults: {
            id: null,
            batchId: null
        }
    });

    return Token;

});


