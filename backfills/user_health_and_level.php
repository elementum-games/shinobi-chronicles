<?php

require 'admin/_authenticate_admin.php';

/**
 * @var System $system
 * @var User $user
 *
 */

$system->log('backfill', 'user_health_and_level', "ran by {$user->user_name} ({$user->user_id})");

$rankManager = new RankManager($system);
$rankManager->loadRanks();

$users_result = $system->query("SELECT `user_id`, `user_name`, 
       `rank`, `level`, `exp`,
       `max_health`,
       `max_chakra`,
       `max_stamina`,
       `ninjutsu_skill`,
       `genjutsu_skill`, 
       `taijutsu_skill`,
       `bloodline_skill`, 
       `cast_speed`, 
       `speed`, 
       `intelligence`, 
       `willpower`
       FROM users");

$count = 0;
while($user_row = $system->db_fetch($users_result)) {
    $rank = $rankManager->ranks[$user_row['rank']];

    $total_stats = $user_row['ninjutsu_skill'] +
        $user_row['genjutsu_skill'] +
        $user_row['taijutsu_skill'] +
        $user_row['bloodline_skill'] +
        $user_row['cast_speed'] +
        $user_row['speed'] +
        $user_row['intelligence'] +
        $user_row['willpower'];

    $stats_for_level = $rankManager->statsForRankAndLevel($rank->id, $user_row['level']);

    $level = $user_row['level'];
    $exp = $user_row['exp'];
    $max_health = $user_row['max_health'];
    $max_chakra = $user_row['max_chakra'];
    $max_stamina = $user_row['max_stamina'];

    $to_update = [
        'exp' => $total_stats * 10,
        'health' => $max_health,
        'max_health' => $max_health,
        'max_chakra' => $max_chakra,
        'max_stamina' => $max_stamina,
        'level' => $level,
    ];

    if($total_stats < $stats_for_level) {
        $levels_to_subtract = ceil(($stats_for_level - $total_stats) / $rank->stats_per_level);
        $new_level = $user_row['level'] - $levels_to_subtract;
        if($new_level < $rank->base_level) {
            $new_level = $rank->base_level;
        }
        $to_update['level'] = $new_level;
    }

    $to_update['max_health'] = $rankManager->healthForRankAndLevel($rank->id, $to_update['level']);
    $to_update['health'] = $to_update['max_health'];

    $to_update['max_chakra'] = $rankManager->chakraForRankAndLevel($rank->id, $to_update['level']);
    $to_update['max_stamina'] = $rankManager->chakraForRankAndLevel($rank->id, $to_update['level']);

    echo "<b>Update {$user_row['user_name']} (#{$user_row['user_id']})</b><br />";
        if($level != $to_update['level']) {
            echo "Level: {$level} => {$to_update['level']}<br />";
        }
        if($exp != $to_update['exp']) {
            echo "Exp: {$exp} => {$to_update['exp']}<br />";
        }
        if($max_health != $to_update['max_health']) {
            echo "Max Health: {$max_health} => {$to_update['max_health']}<br />";
        }
        if($max_chakra != $to_update['max_chakra']) {
            echo "Max chakra: {$max_chakra} => {$to_update['max_chakra']}<br />";
        }
        if($max_stamina != $to_update['max_stamina']) {
            echo "Max stamina: {$max_stamina} => {$to_update['max_stamina']}<br />";
        }
    echo "<br /><br />";

    $system->query("UPDATE `users` SET 
        `exp`='{$to_update['exp']}',
        `level`='{$to_update['level']}',
        `health`='{$to_update['health']}',
        `max_health`='{$to_update['max_health']}',
        `max_chakra`='{$to_update['max_chakra']}',
        `max_stamina`='{$to_update['max_stamina']}'
        WHERE `user_id`={$user_row['user_id']} LIMIT 1");
}