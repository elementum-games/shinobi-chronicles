<?php

// KEEP IDS IN SYNC WITH System::PAGE_IDS
// NEXT ID: 29 (i.e. if you add 28, update this to 29 to help other contributors)

$pages = [
    // User Menu
    1 => [
        'file_name' => 'profile.php',
        'title' => 'Profile',
        'function_name' => 'userProfile',
        'menu' => System::MENU_USER,
    ],
    2 => [
        'file_name' => 'privateMessages.php',
        'title' => 'Inbox',
        'function_name' => 'privateMessages',
        'menu' => System::MENU_USER,
    ],
    7 => [
        'file_name' => 'chat.php',
        'title' => 'Chat',
        'function_name' => 'chat',
        'ajax_ok' => true,
        'menu' => System::MENU_USER,
    ],
    4 => [
        'file_name' => 'equip.php',
        'title' => 'Jutsu',
        'function_name' => 'jutsu',
        'battle_ok' => false,
        'menu' => System::MENU_USER,
    ],
    5 => [
        'file_name' => 'equip.php',
        'title' => 'Gear',
        'function_name' => 'gear',
        'battle_ok' => false,
        'menu' => System::MENU_USER,
    ],
    10 => [
        'file_name' => 'bloodline.php',
        'title' => 'Bloodline',
        'function_name' => 'bloodline',
        'battle_ok' => false,
        'village_ok' => System::IN_VILLAGE_OKAY,
        'menu' => 'conditional',
    ],
    6 => [
        'file_name' => 'members.php',
        'title' => 'Members',
        'function_name' => 'members',
        'menu' => System::MENU_USER,
    ],
    24 => [
        'file_name' => 'team.php',
        'title' => 'Team',
        'function_name' => 'team',
        'min_rank' => 3,
        'menu' => 'conditional',
    ],
    29 => [
        'file_name' => 'marriage.php',
        'title' => 'Marriage',
        'function_name' => 'marriage',
        'menu' => System::MENU_USER,
    ],

    // Activity Menu
    11 => [
        'file_name' => 'travel.php',
        'title' => 'Travel',
        'function_name' => 'travel',
        'menu' => System::MENU_ACTIVITY,
        'battle_ok' => false,
        'survival_ok' => false,
        'village_ok' => System::IN_VILLAGE_OKAY,
        'min_rank' => 2
    ],
    12 => [
        'file_name' => 'arena.php',
        'title' => 'Arena',
        'function_name' => 'arena',
        'battle_api_function_name' => 'arenaFightAPI',
        'menu' => System::MENU_ACTIVITY,
        'village_ok' => System::NOT_IN_VILLAGE,
        'battle_type' => Battle::TYPE_AI_ARENA,
    ],
    13 => [
        'file_name' => 'training.php',
        'title' => 'Training',
        'function_name' => 'training',
        'menu' => System::MENU_ACTIVITY,
        'battle_ok' => false,
        'village_ok' => System::NOT_IN_VILLAGE,
    ],
    14 => [
        'file_name' => 'missions.php',
        'title' => 'Missions',
        'function_name' => 'missions',
        'battle_api_function_name' => 'missionFightAPI',
        'menu' => System::MENU_ACTIVITY,
        'battle_type' => Battle::TYPE_AI_MISSION,
        'village_ok' => System::IN_VILLAGE_OKAY,
        'min_rank' => 2
    ],
    15 => [
        'file_name' => 'specialmissions.php',
        'title' => 'Special Missions',
        'function_name' => 'specialMissions',
        'battle_ok' => false,
        'menu' => System::MENU_ACTIVITY,
        'village_ok' => System::IN_VILLAGE_OKAY,
        'min_rank' => 2,
    ],
    22 => [
        'file_name' => 'spar.php',
        'title' => 'Spar',
        'function_name' => 'spar',
        'battle_api_function_name' => 'sparFightAPI',
        'battle_type' => Battle::TYPE_SPAR,
        'village_ok' => System::IN_VILLAGE_OKAY,
        'menu' => System::MENU_ACTIVITY,
    ],
    23 => [
        'file_name' => 'healingShop.php',
        'title' => 'Ramen Shop',
        'function_name' => 'healingShop',
        'menu' => System::MENU_ACTIVITY,
        'battle_ok' => false,
        'village_ok' => System::ONLY_IN_VILLAGE,
    ],
    26 => [
        'file_name' => 'viewBattles.php',
        'title' => 'View Battles',
        'function_name' => 'viewBattles',
        'menu' => System::MENU_ACTIVITY,
        'battle_ok' => false,
        'village_ok' => System::IN_VILLAGE_OKAY,
    ],

    // Village Menu
    8 => [
        'file_name' => 'store.php',
        'title' => 'Shop',
        'function_name' => 'store',
        'village_ok' => System::ONLY_IN_VILLAGE,
        'menu' => System::MENU_VILLAGE,
    ],
    9 => [
        'file_name' => 'villageHQ.php',
        'title' => 'Village HQ',
        'function_name' => 'villageHQ',
        'village_ok' => System::ONLY_IN_VILLAGE,
        'menu' => System::MENU_VILLAGE,
    ],
    20 => [
        'file_name' => 'clan.php',
        'title' => 'Clan',
        'function_name' => 'clan',
        'menu' => 'conditional',
    ],
    21 => [
        'file_name' => 'premium.php',
        'title' => 'Ancient Market',
        'function_name' => 'premium',
        'menu' => System::MENU_VILLAGE,
    ],

    // Staff menu
    30 => [
        'file_name' => 'supportPanel.php',
        'title' => 'Support Panel',
        'function_name' => 'supportPanel',
        'user_check' => function(User $u) {
            return $u->isSupportStaff();
        }
    ],
    16 => [
        'file_name' => 'modPanel.php',
        'title' => 'Mod Panel',
        'function_name' => 'modPanel',
        'user_check' => function(User $u) {
            return $u->isModerator();
        }
    ],
    17 => [
        'file_name' => 'adminPanel.php',
        'title' => 'Admin Panel',
        'function_name' => 'adminPanel',
        'user_check' => function(User $u) {
            return $u->hasAdminPanel();
        }
    ],

    // Misc
    3 => [
        'file_name' => 'settings.php',
        'title' => 'Settings',
        'function_name' => 'userSettings',
        'menu' => 'none',
    ],
    18 => [
        'file_name' => 'report.php',
        'title' => 'Report',
        'function_name' => 'report'
    ],
    19 => [
        'file_name' => 'battle.php',
        'title' => 'Battle',
        'function_name' => 'battle',
        'battle_api_function_name' => 'battleFightAPI',
        'battle_type' => Battle::TYPE_FIGHT,
    ],
    25 => [
        'file_name' => 'levelUp.php',
        'title' => 'Rank Exam',
        'function_name' => 'rankUp',
        'battle_api_function_name' => 'rankupFightAPI',
        'battle_type' => Battle::TYPE_AI_RANKUP,
    ],
    27 => [
        'file_name' => 'event.php',
        'title' => 'Event',
        'function_name' => 'event',
    ],
];

return $pages;