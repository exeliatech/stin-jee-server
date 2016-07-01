define([
    'models/Batch',
    'backbone'
], function(Batch, Backbone) {

    var BatchList = Backbone.Collection.extend({
      model: Batch,
      url: '/api/batch/list'
    });

    return BatchList;

});



