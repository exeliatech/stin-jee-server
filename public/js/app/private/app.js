define([
  'views/header',
  'views/content'
], function(Header, Content){
    
  var initialize = function(){
   
    Header.initialize();
    Content.initialize();
    
  };

  return { 
    initialize: initialize
  };
  
});