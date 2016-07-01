define([
  'views/header',
  'views/content'
], function(Header, Content){
    
  var initialize = function(){
   
    Content.initialize(Header.initialize());
    
  };

  return { 
    initialize: initialize
  };
  
});