// console.log("Script is working!");
//jQuery required to run

//
$(document).ready(function(){


  //Profile Page
  //style names are important
  //moves the sidebar menu (I'm lazy, hope this works)
  $('#container').append( $('#sidebar') );
  //this one isn't necessary i'm just actually really lazy rn
  $('#footer').append( $('.logout') );

  //onClick Listener for Menu items
  $(".menuTouchItem").on("click", function(){
    $(".topMenu").toggleClass("open");
  })

  $("#character_header").on("click", function(){
    $("#player_menu").toggleClass("open");
  })

  $("#action_header").on("click", function(){
    $("#action_menu").toggleClass("open");
  })

  $("#travel_header").on("click", function(){
    $("#travel_menu").toggleClass("open");
  })

  $("#staff_header").on("click", function(){
    $("#staff_menu").toggleClass("open");
  })


 //Travel Page
 //Travel Buttons
 //insert div after travelcontainer
 $("<div id='buttonContainer'> </div>").insertAfter(".travelContainer");
 //move the buttons into the div created after travel container
 $(".travelButton").appendTo("#buttonContainer");


//add text to the buttons
 $("<span>North</span>").appendTo(".north");
 $("<span>East</span>").appendTo(".east");
 $("<span>West</span>").appendTo(".west");
 $("<span>South</span>").appendTo(".south");




})
