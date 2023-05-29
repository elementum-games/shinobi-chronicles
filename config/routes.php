<?php

require_once __DIR__ . '/../classes/Route.php';

// KEEP IDS IN SYNC WITH Router::PAGE_IDS
// NEXT ID: 32 (i.e. if you add 28, update this to 29 to help other contributors)
return $routes = [
    // User Menu
    1 => new Route(
        file_name: 'profile.php',
        title: 'Profile',
        function_name: 'userProfile',
        menu: Route::MENU_USER,
    ),
    2 => new Route(
        file_name: 'inbox.php',
        title: 'Inbox',
        function_name: 'inbox',
        menu: Route::MENU_USER,
    ),
    7 => new Route(
        file_name: 'chat.php',
        title: 'Chat',
        function_name: 'chat',
        menu: Route::MENU_USER,
        ajax_ok: true,
    ),
    4 => new Route(
        file_name: 'jutsu.php',
        title: 'Jutsu',
        function_name: 'jutsu',
        menu: Route::MENU_USER,
        battle_ok: false,
    ),
    5 => new Route(
        file_name: 'gear.php',
        title: 'Gear',
        function_name: 'gear',
        menu: Route::MENU_USER,
        battle_ok: false,
    ),
    10 => new Route(
        file_name: 'bloodline.php',
        title: 'Bloodline',
        function_name: 'bloodline',
        menu: 'conditional',
        battle_ok: false,
        village_ok: Route::IN_VILLAGE_OKAY,
    ),
    6 => new Route(
        file_name: 'members.php',
        title: 'Members',
        function_name: 'members',
        menu: Route::MENU_USER,
    ),
    24 => new Route(
        file_name: 'team.php',
        title: 'Team',
        function_name: 'team',
        menu: 'conditional',
        min_rank: 3,
    ),
    29 => new Route(
        file_name: 'marriage.php',
        title: 'Marriage',
        function_name: 'marriage',
        menu: Route::MENU_USER,
    ),

    // Activity Menu
    11 => new Route(
        file_name: 'travel.php',
        title: 'Travel',
        function_name: 'travel',
        menu: Route::MENU_ACTIVITY,
        min_rank: 2,
        battle_ok: false,
        village_ok: Route::IN_VILLAGE_OKAY,
        survival_mission_ok: false
    ),
    12 => new Route(
        file_name: 'arena.php',
        title: 'Arena',
        function_name: 'arena',
        menu: Route::MENU_ACTIVITY,
        battle_type: Battle::TYPE_AI_ARENA,
        village_ok: Route::NOT_IN_VILLAGE,
    ),
    13 => new Route(
        file_name: 'training.php',
        title: 'Training',
        function_name: 'training',
        menu: Route::MENU_ACTIVITY,
        battle_ok: false,
        village_ok: Route::NOT_IN_VILLAGE,
    ),
    14 => new Route(
        file_name: 'missions.php',
        title: 'Missions',
        function_name: 'missions',
        menu: Route::MENU_ACTIVITY,
        battle_type: Battle::TYPE_AI_MISSION,
        min_rank: 2,
        village_ok: Route::IN_VILLAGE_OKAY
    ),
    15 => new Route(
        file_name: 'specialmissions.php',
        title: 'Special Missions',
        function_name: 'specialMissions',
        menu: Route::MENU_ACTIVITY,
        min_rank: 2,
        battle_ok: false,
        village_ok: Route::IN_VILLAGE_OKAY,
    ),
    22 => new Route(
        file_name: 'spar.php',
        title: 'Spar',
        function_name: 'spar',
        menu: Route::MENU_ACTIVITY,
        battle_type: Battle::TYPE_SPAR,
        village_ok: Route::IN_VILLAGE_OKAY,
    ),
    23 => new Route(
        file_name: 'healingShop.php',
        title: 'Ramen Shop',
        function_name: 'healingShop',
        menu: Route::MENU_ACTIVITY,
        battle_ok: false,
        village_ok: Route::ONLY_IN_VILLAGE,
    ),
    26 => new Route(
        file_name: 'viewBattles.php',
        title: 'View Battles',
        function_name: 'viewBattles',
        menu: Route::MENU_ACTIVITY,
        battle_ok: false,
        village_ok: Route::IN_VILLAGE_OKAY,
    ),

    // Village Menu
    8 => new Route(
        file_name: 'store.php',
        title: 'Shop',
        function_name: 'store',
        menu: Route::MENU_VILLAGE,
        village_ok: Route::ONLY_IN_VILLAGE,
    ),
    9 => new Route(
        file_name: 'villageHQ.php',
        title: 'Village HQ',
        function_name: 'villageHQ',
        menu: Route::MENU_VILLAGE,
        village_ok: Route::ONLY_IN_VILLAGE,
    ),
    20 => new Route(
        file_name: 'clan.php',
        title: 'Clan',
        function_name: 'clan',
        menu: 'conditional',
    ),
    21 => new Route(
        file_name: 'premium.php',
        title: 'Ancient Market',
        function_name: 'premium',
        menu: Route::MENU_VILLAGE,
    ),

    // Staff menu
    30 => new Route(
        file_name: 'supportPanel.php',
        title: 'Support Panel',
        function_name: 'supportPanel',
        menu: Route::MENU_CONDITIONAL,
        user_check: function(User $u) {
            return $u->isSupportStaff();
        }
    ),
    16 => new Route(
        file_name: 'modPanel.php',
        title: 'Mod Panel',
        function_name: 'modPanel',
        menu: Route::MENU_CONDITIONAL,
        user_check: function(User $u) {
            return $u->isModerator();
        }
    ),
    17 => new Route(
        file_name: 'adminPanel.php',
        title: 'Admin Panel',
        function_name: 'adminPanel',
        menu: Route::MENU_CONDITIONAL,
        user_check: function(User $u) {
            return $u->hasAdminPanel();
        }
    ),
    31 => new Route(
        file_name: 'chat_log.php',
        title: 'Chat Log',
        function_name: 'chatLog',
        menu: Route::MENU_CONDITIONAL,
        user_check: function(User $u) {
            return $u->isModerator();
        }
    ),

    // Misc
    3 => new Route(
        file_name: 'settings.php',
        title: 'Settings',
        function_name: 'userSettings',
        menu: Route::MENU_NONE,
    ),
    18 => new Route(
        file_name: 'report.php',
        title: 'Report',
        function_name: 'report',
        menu: Route::MENU_CONDITIONAL,
    ),
    19 => new Route(
        file_name: 'battle.php',
        title: 'Battle',
        function_name: 'battle',
        menu: Route::MENU_NONE,
        battle_type: Battle::TYPE_FIGHT,
    ),
    25 => new Route(
        file_name: 'levelUp.php',
        title: 'Rank Exam',
        function_name: 'rankUp',
        menu: Route::MENU_NONE,
        battle_type: Battle::TYPE_AI_RANKUP,
    ),
    27 => new Route(
        file_name: 'event.php',
        title: 'Event',
        function_name: 'event',
        menu: Route::MENU_NONE,
    ),
    32 => new Route(
        file_name: 'news.php',
        title: 'News',
        function_name: 'news',
        menu: Route::MENU_NONE,
    ),
    33 => new Route(
        file_name: 'battleHistory.php',
        title: 'Battle History',
        function_name: 'battleHistory',
        menu: Route::MENU_NONE
    )
];