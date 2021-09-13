<?php

$pages = [
    // User Menu
    1 => [
        'file_name' => 'profile.php',
        'title' => 'Profile',
        'function_name' => 'userProfile',
        'menu' => SystemFunctions::MENU_USER,
    ],
    2 => [
        'file_name' => 'privateMessages.php',
        'title' => 'Inbox',
        'function_name' => 'privateMessages',
        'menu' => SystemFunctions::MENU_USER,
    ],
    3 => [
        'file_name' => 'settings.php',
        'title' => 'Settings',
        'function_name' => 'userSettings',
        'menu' => SystemFunctions::MENU_USER,
    ],
    4 => [
        'file_name' => 'equip.php',
        'title' => 'Jutsu',
        'function_name' => 'jutsu',
        'battle_ok' => false,
        'menu' => SystemFunctions::MENU_USER,
    ],
    5 => [
        'file_name' => 'equip.php',
        'title' => 'Gear',
        'function_name' => 'gear',
        'battle_ok' => false,
        'menu' => SystemFunctions::MENU_USER,
    ],
    6 => [
        'file_name' => 'members.php',
        'title' => 'Members',
        'function_name' => 'members',
        'menu' => SystemFunctions::MENU_USER,
    ],
    7 => [
        'file_name' => 'chat.php',
        'title' => 'Chat',
        'function_name' => 'chat',
        'ajax_ok' => true,
        'menu' => SystemFunctions::MENU_USER,
    ],
    10 => [
        'file_name' => 'bloodline.php',
        'title' => 'Bloodline',
        'function_name' => 'bloodline',
        'battle_ok' => false,
        'village_ok' => 1,
        'menu' => 'conditional',
    ],
    20 => [
        'file_name' => 'clan.php',
        'title' => 'Clan',
        'function_name' => 'clan',
        'menu' => 'conditional',
    ],
    24 => [
        'file_name' => 'team.php',
        'title' => 'Team',
        'function_name' => 'team',
        'min_rank' => 3,
        'menu' => 'conditional',
    ],

    // Activity Menu
    11 => [
        'file_name' => 'training.php',
        'title' => 'Training',
        'function_name' => 'training',
        'menu' => SystemFunctions::MENU_ACTIVITY,
        'battle_ok' => false,
        'village_ok' => 0
    ],
    12 => [
        'file_name' => 'arena.php',
        'title' => 'Arena',
        'function_name' => 'arena',
        'menu' => SystemFunctions::MENU_ACTIVITY,
        'pvp_ok' => false,
        'village_ok' => 0
    ],
    13 => [
        'file_name' => 'healingShop.php',
        'title' => 'Ramen Shop',
        'function_name' => 'healingShop',
        'menu' => SystemFunctions::MENU_ACTIVITY,
        'battle_ok' => false,
        'village_ok' => 2
    ],
    14 => [
        'file_name' => 'travel.php',
        'title' => 'Travel',
        'function_name' => 'travel',
        'menu' => SystemFunctions::MENU_ACTIVITY,
        'battle_ok' => false,
        'survival_ok' => false,
        'village_ok' => 1,
        'min_rank' => 2
    ],
    23 => [
        'file_name' => 'missions.php',
        'title' => 'Missions',
        'function_name' => 'missions',
        'menu' => SystemFunctions::MENU_ACTIVITY,
        'pvp_ok' => false,
        'village_ok' => 1,
        'min_rank' => 2
    ],

    // Village Menu
    8 => [
        'file_name' => 'store.php',
        'title' => 'Shop',
        'function_name' => 'store',
        'village_ok' => 2,
        'menu' => SystemFunctions::MENU_VILLAGE,
    ],
    9 => [
        'file_name' => 'villageHQ.php',
        'title' => 'Village HQ',
        'function_name' => 'villageHQ',
        'village_ok' => 2,
        'menu' => SystemFunctions::MENU_VILLAGE,
    ], 
    21 => [
        'file_name' => 'premium.php',
        'title' => 'Ancient Market',
        'function_name' => 'premium',
        'menu' => SystemFunctions::MENU_VILLAGE,
    ],

    // Staff menu
    16 => [
        'file_name' => 'modPanel.php',
        'title' => 'Moderator Control Panel',
        'function_name' => 'modPanel',
        'staff_level_required' => SystemFunctions::SC_MODERATOR
    ],
    17 => [
        'file_name' => 'adminPanel.php',
        'title' => 'Administrator Control Panel',
        'function_name' => 'adminPanel',
        'staff_level_required' => SystemFunctions::SC_ADMINISTRATOR
    ],

    // Misc
    18 => [
        'file_name' => 'report.php',
        'title' => 'Report',
        'function_name' => 'report'
    ],
    19 => [
        'file_name' => 'battle.php',
        'title' => 'Battle',
        'function_name' => 'battle',
        'battle_type' => 1
    ],
];

return $pages;
