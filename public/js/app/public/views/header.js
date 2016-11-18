define([
  'jquery',
  'underscore_mixin',
  'backbone',
  'models/AppState',
  'text!templates/header.html'
], function($, _, Backbone, appState, HeaderTemplate){

    var Header = Backbone.View.extend({
        el: $("#header"),
        
        model: appState,

        templates: {
            header: _.template(HeaderTemplate)
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
        }

    });

    var initialize = function() {

        var header = new Header();
        appState.trigger("change");
        return header;
        
    };
    
    return {
        initialize: initialize
    };
    
});