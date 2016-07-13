require.config({

    paths: {
        jquery: 'https://code.jquery.com/jquery-1.12.4.min',
        jquery_cookie: 'https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min',
        jquery_autocomplete: 'https://cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.25/jquery.autocomplete.min',
        jquery_csv: 'https://cdnjs.cloudflare.com/ajax/libs/jquery-csv/0.71/jquery.csv-0.71.min',
        underscore: 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min',
        underscore_string: 'https://cdnjs.cloudflare.com/ajax/libs/underscore.string/3.3.4/underscore.string.min',
        underscore_mixin: '../../libs/underscore/underscore.mixin',
        backbone: 'https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.3.3/backbone-min',
        text: 'https://cdnjs.cloudflare.com/ajax/libs/require-text/2.0.12/text.min',
        async: '../../libs/require/async',
        moment: 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min',
        colorpicker: '../../libs/colorpicker/colorpicker',
        niceinput: '../../libs/niceinput/niceinput',
        datatables: '//cdn.datatables.net/1.10.12/js/jquery.dataTables.min',
        ddslick: 'https://cdn.jsdelivr.net/ddslick/2.0/jquery.ddslick.min',
        pickaday: 'https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.4.0/pikaday.min'
    },
  
    shim: {
        underscore: {
            exports: '_'
        },  
        underscore_string: {
            deps: ['underscore']
        },
        niceinput: {
            deps: ['jquery']  
        },
        ddslick: {
            deps: ['jquery']  
        },
        colorpicker: {
            deps: ['jquery']  
        },
        jquery_csv: {
            deps: ['jquery']  
        },
        jquery_date_range_picker: {
            deps: ['jquery', 'moment']  
        }
    }

});

require([
  'app'
], function(App){
    
  App.initialize();
  
});