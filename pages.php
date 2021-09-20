<?php

// KEEP IDS IN SYNC WITH System::PAGE_IDS

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
        'menu' => System::MENU_ACTIVITY,
        'battle_type' => Battle::TYPE_AI_ARENA,
        'village_ok' => System::IN_VILLAGE_OKAY,
        'min_rank' => 2
    ],
    22 => [
        'file_name' => 'spar.php',
        'title' => 'Spar',
        'function_name' => 'spar',
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
    16 => [
        'file_name' => 'modPanel.php',
        'title' => 'Moderator Control Panel',
        'function_name' => 'modPanel',
        'staff_level_required' => System::SC_MODERATOR
    ],
    17 => [
        'file_name' => 'adminPanel.php',
        'title' => 'Administrator Control Panel',
        'function_name' => 'adminPanel',
        'staff_level_required' => System::SC_ADMINISTRATOR
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
        'battle_type' => Battle::TYPE_FIGHT,
    ],
    25 => [
        'file_name' => 'levelUp.php',
        'title' => 'Rank Exam',
        'function_name' => 'rankUp',
        'battle_type' => Battle::TYPE_AI_RANKUP,
    ],
];

return $pages;