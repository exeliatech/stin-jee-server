define([
    'underscore_mixin',
    '../locales/en',
    '../locales/it',
    '../locales/hr',
    'jquery_cookie'
], function(_, en, it, hr) {
    
    var Locale = function() {

        var that = this;
        
        var locales = {
            en: en,
            it: it,
            hr: hr
        };
        
        var current_locale = _.keys(locales)[0];

        this.setCountry = function(country) {
            $.cookie('country', country);
        };
        
        if (_.indexOf(_.keys(locales), $.cookie('locale')) !== -1) {
            current_locale = $.cookie('locale');
        } else {
            that.setCountry('it');
            //$.getJSON("http://www.telize.com/geoip?callback=?", function(json) {
            //    that.setCountry(json.country_code);
            //    if (_.indexOf(_.keys(locales), json.country_code.toLowerCase()) !== - 1) {
            //        that.setLocale(json.country_code.toLowerCase());
            //        window.location.reload();
            //    }
            //});
        }     
        
        //if (!$.cookie('country')) {
        //    $.getJSON("http://www.telize.com/geoip?callback=?", function(json) {
        //        that.setCountry(json.country_code);
        //        window.location.reload();
        //    });
        //}

        var texts = locales[current_locale];

        this.get = function(name, data) {
            if (typeof texts[name] === 'undefined') return name;
            try {
                return _.sprintf(texts[name], data);
            } catch (ex) {
                console.log('Error in translation: ' + current_locale + ', ' + name + ', ' + JSON.stringify(data));
                return name;
            }
        };
        
        this.setLocale = function(locale) {
            if (_.indexOf(_.keys(locales), locale) !== -1) {
                $.cookie('locale', locale, { domain: '.stinjee.com' });
                current_locale = locale;
                texts = locales[current_locale];
            }
        };
        
        this.getList = function() {
            return _.keys(locales);
        };
        
        this.getCurrent = function() {
            return current_locale;
        };
        
        this.getCountry = function() {
            return $.cookie('country');
        };

    };
    
    return new Locale();
    
});


