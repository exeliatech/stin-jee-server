define([
    'underscore',
    'backbone'
], function(_, Backbone) {

    var Specials = Backbone.Model.extend({
        urlRoot: '/api/specials',
        fileAttribute: 'image',
        defaults: {
            id: null,
            batchId: null,
            tokenId: null,
            name: null,
            description: null,
            store: null,
            addres: null,
            phone: null
        }
    });

    return Specials;

});