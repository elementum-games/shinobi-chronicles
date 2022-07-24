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

//arena
$("#bi_td_opponent").before($("#bi_th_opponent"));
$("#bi_th_opponent").css("margin-top", "1.5rem");

//Team Page
$("#team_information_data").before($("#team_information_header"));
$("#team_boost_data").before($("#team_boost_header"));
$("#team_leader_data").before($("#team_leader_header"));

$("#team_actions_data").before($("#team_actions_header"));
$("#team_logo_data").before($("#team_logo_header"));
$("#team_boost2_data").before($("#team_boost2_header"));
/*there's actually 2 boost ID's with the same name headers - might cause confusiong */

//Jutsu page
$("#ninjutsu_table_data").before($("#ninjutsu_title_header"));
$("#taijutsu_table_data").before($("#taijutsu_title_header"));
$("#genjutsu_table_data").before($("#genjutsu_title_header"));

/*Premum Page*/
$("#premium_individualStatReset_data").before($("#premium_individualStatReset_header"));
$("#premium_fourDragonSeal_data").before($("#premium_fourDragonSeal_header"));

console.log("Cextralite Script Working");
})
