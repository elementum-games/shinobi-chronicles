<?php

/*
five chakra natures
Jutsu clash – elemental factors
DATA STRUCTURE
    player1
    player2
    player1_action (bool)
    player2_action (bool)
    player1_active_element
    player2_active_element
    player1_raw_damage
    player2_raw_damage
    player1_battle_text
    player2_battle_text
    turn_time
    winner (0 or player ID, -1 for tie)
Two players
Keep turn timer
Both users must submit move by end of turn
Moves happen same time
Moves clash - damage comparison with advantage slanted towards elemental advantages
First person to load page calculates damages dealt
if both users have submitted move(player1_action and player2_action)
    run damage calcs, jutsu clash, blah blah blah
else
if both users have not submitted move (check player1_action and player2_action)
-prompt user for turn or send message ("Please wait for other user")
if player has not submitted move
    prompt for it
*/

class Battle {
    const TYPE_AI_ARENA = 1;
    const TYPE_SPAR = 2;
    const TYPE_FIGHT = 3;
    const TYPE_CHALLENGE = 4;
    const TYPE_AI_MISSION = 5;

}