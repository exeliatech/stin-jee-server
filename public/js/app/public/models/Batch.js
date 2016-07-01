define([
    'underscore',
    'backbone'
], function(_, Backbone) {

    var Batch = Backbone.Model.extend({
        urlRoot: '/api/batch',
        defaults: {
            id: null,
            specials: []
        },
        initialize: function() {
        }
    });

    return Batch;

});



