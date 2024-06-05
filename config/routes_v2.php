<?php
require_once __DIR__ . '/../classes/routing/Route.php';
/**
 * Page Template
 * Note: See classes/routing/Route.php for configuration details
 * 
 * 'page_name' => RouteV2::load(
 *      file_name: "",
 *      title: "",
 *      function_name: "",
 *      
 *      These have default values and can be omitted when not applicable
 *      menu: ,
 *      battle_type: ,
 *      min_rank: ,
 *      battle_ok: ,
 *      survival_mission_ok: ,
 *      challenge_lock_ok: ,
 *      user_check: ,
 *      dev_only: ,
 *      allowed_location_types: ,
 *      location_access_mode: 
 *  ),
 */
return [
    'home' => RouteV2::load(
        file_name: 'home.php',
        title: 'Home',
        function_name: 'home',
    ),

    // User Menu
    'profile' => RouteV2::load(
        file_name: 'profile.php',
        title: 'Profile',
        function_name: 'userProfile',
        menu: RouteV2::MENU_USER,
    ),
    'inbox' => RouteV2::load(
        file_name: 'inbox.php',
        title: 'Inbox',
        function_name: 'inbox',
        menu: RouteV2::MENU_USER,
    ),
    'jutsu' => RouteV2::load(
        file_name: 'jutsu.php',
        title: 'Jutsu',
        function_name: 'jutsu',
        menu: RouteV2::MENU_USER,
        battle_ok: true,
    ),
    'gear' => RouteV2::load(
        file_name: 'gear.php',
        title: 'Gear',
        function_name: 'gear',
        menu: RouteV2::MENU_USER,
        battle_ok: false,
    ),
    'bloodline' => RouteV2::load(
        file_name: 'bloodline.php',
        title: 'Bloodline',
        function_name: 'bloodline',
        menu: 'conditional',
        battle_ok: false,
    ),
    'members' => RouteV2::load(
        file_name: 'members.php',
        title: 'Members',
        function_name: 'members',
        menu: RouteV2::MENU_USER,
    ),
    'send_money' => RouteV2::load(
        file_name: 'sendMoney.php',
        title: 'Send Money',
        function_name: 'sendMoney',
        
    ),
    'team' => RouteV2::load(
        file_name: 'team.php',
        title: 'Team',
        function_name: 'team',
        menu: 'conditional',
        min_rank: 3,
    ),
    'marriage' => RouteV2::load(
        file_name: 'marriage.php',
        title: 'Marriage',
        function_name: 'marriage',
        menu: RouteV2::MENU_USER,
    ),

    // Activity Menu
    'chat' => RouteV2::load(
        file_name: 'chat.php',
        title: 'Chat',
        function_name: 'chat',
        menu: RouteV2::MENU_ACTIVITY,
    ),
    'travel' => RouteV2::load(
        file_name: 'travel.php',
        title: 'Travel',
        function_name: 'travel',
        menu: RouteV2::MENU_ACTIVITY,
        battle_ok: false,
        survival_mission_ok: false,
        challenge_lock_ok: false,
    ),
    'areana' => RouteV2::load(
        file_name: 'arena.php',
        title: 'Arena',
        function_name: 'arena',
        menu: RouteV2::MENU_ACTIVITY,
        battle_type: Battle::TYPE_AI_ARENA,
        allowed_location_types: [TravelManager::LOCATION_TYPE_DEFAULT],
        challenge_lock_ok: false,
    ),
    'training' => RouteV2::load(
        file_name: 'training.php',
        title: 'Training',
        function_name: 'training',
        menu: RouteV2::MENU_ACTIVITY,
        battle_ok: false,
        allowed_location_types: [TravelManager::LOCATION_TYPE_DEFAULT],
    ),
    'missions' => RouteV2::load(
        file_name: 'missions.php',
        title: 'Missions',
        function_name: 'missions',
        menu: RouteV2::MENU_ACTIVITY,
        battle_type: Battle::TYPE_AI_MISSION,
        min_rank: 2,
        challenge_lock_ok: false,
    ),
    'special_missions' => RouteV2::load(
        file_name: 'special_missions.php',
        title: 'Special Missions',
        function_name: 'specialMissions',
        menu: RouteV2::MENU_ACTIVITY,
        min_rank: 2,
        battle_ok: false,
        challenge_lock_ok: false,
    ),
    'spar' => RouteV2::load(
        file_name: 'spar.php',
        title: 'Spar',
        function_name: 'spar',
        menu: RouteV2::MENU_ACTIVITY,
        battle_type: Battle::TYPE_SPAR,
        challenge_lock_ok: false,
    ),
    'ramen_shop' => RouteV2::load(
        file_name: 'healingShop.php',
        title: 'Ramen Shop',
        function_name: 'healingShop',
        menu: RouteV2::MENU_ACTIVITY,
        battle_ok: false,
        allowed_location_types: [TravelManager::LOCATION_TYPE_HOME_VILLAGE, TravelManager::LOCATION_TYPE_COLOSSEUM],
    ),
    'view_battles' => RouteV2::load(
        file_name: 'viewBattles.php',
        title: 'View Battles',
        function_name: 'viewBattles',
        menu: RouteV2::MENU_ACTIVITY,
        battle_ok: true,
    ),

    // Village Menu
    'shop' => RouteV2::load(
        file_name: 'store.php',
        title: 'Shop',
        function_name: 'store',
        menu: RouteV2::MENU_VILLAGE,
        allowed_location_types: [TravelManager::LOCATION_TYPE_HOME_VILLAGE],
    ),
    'village_hq' => RouteV2::load(
        file_name: 'villageHQ_v2.php',
        title: 'Village HQ',
        function_name: 'villageHQ',
        menu: RouteV2::MENU_VILLAGE,
        battle_ok: false,
    ),
    'clan' => RouteV2::load(
        file_name: 'clan.php',
        title: 'Clan',
        function_name: 'clan',
        menu: 'conditional',
    ),
    'ancient_market' => RouteV2::load(
        file_name: 'premium_shop.php',
        title: 'Ancient Market',
        function_name: 'premiumShop',
        menu: RouteV2::MENU_VILLAGE,
        challenge_lock_ok: false,
    ),
    'academy' => RouteV2::load(
        file_name: 'academy.php',
        title: 'Academy',
        function_name: 'academy',
        menu: RouteV2::MENU_VILLAGE,
    ),

    // Staff menu
    // Note: Do not make these conditional pages, handled logic in NavigatoinApiManager::getStaffMenu
    'support_panel' => RouteV2::load(
        file_name: 'supportPanel.php',
        title: 'Support Panel',
        function_name: 'supportPanel',
        menu: RouteV2::MENU_STAFF,
        user_check: function(User $u) {
            return $u->isSupportStaff();
        }
    ),
    'mod_panel' => RouteV2::load(
        file_name: 'modPanel.php',
        title: 'Mod Panel',
        function_name: 'modPanel',
        menu: RouteV2::MENU_STAFF,
        user_check: function(User $u) {
            return $u->isModerator();
        }
    ),
    'admin_panel' => RouteV2::load(
        file_name: 'adminPanel.php',
        title: 'Admin Panel',
        function_name: 'adminPanel',
        menu: RouteV2::MENU_STAFF,
        user_check: function(User $u) {
            return $u->hasAdminPanel();
        }
    ),
    // This one is okay to be conditional, not loaded into menus
    'chat_log' => RouteV2::load(
        file_name: 'chat_log.php',
        title: 'Chat Log',
        function_name: 'chatLog',
        menu: RouteV2::MENU_CONDITIONAL,
        user_check: function(User $u) {
            return $u->isModerator();
        }
    ),

    // Misc
    'settings' => RouteV2::load(
        file_name: 'settings.php',
        title: 'Settings',
        function_name: 'userSettings',
        menu: RouteV2::MENU_USER,
    ),
    'report' => RouteV2::load(
        file_name: 'report.php',
        title: 'Report',
        function_name: 'report',
        menu: RouteV2::MENU_CONDITIONAL,
    ),
    'battle' => RouteV2::load(
        file_name: 'battle.php',
        title: 'Battle',
        function_name: 'battle',
        battle_type: Battle::TYPE_FIGHT,
    ),
    'level_up' => RouteV2::load(
        file_name: 'levelUp.php',
        title: 'Rank Exam',
        function_name: 'rankUp',
        battle_type: Battle::TYPE_AI_RANKUP,
    ),
    'event' => RouteV2::load(
        file_name: 'event.php',
        title: 'Event',
        function_name: 'event',
    ),
    'news' => RouteV2::load(
        file_name: 'news.php',
        title: 'News',
        function_name: 'news',
    ),
    'account_record' => RouteV2::load(
        file_name: 'accountRecord.php',
        title: 'Account Record',
        function_name: 'accountRecord',
    ),
    'forbidden_shop' => RouteV2::load(
        file_name: 'forbiddenShop.php',
        title: "???",
        function_name: 'forbiddenShop',
    ),
    'war' => RouteV2::load(
        file_name: 'war.php',
        title: "War",
        function_name: 'war',
        battle_type: Battle::TYPE_AI_WAR,
    ),
    'challenge' => RouteV2::load(
        file_name: 'challenge.php',
        title: "Challenge",
        function_name: 'challenge',
        battle_type: Battle::TYPE_CHALLENGE,
    ),

   /// DEV ONLY
   'test' => RouteV2::load(
        file_name: 'test.php',
        title: "Test",
        function_name: 'test',
        menu: RouteV2::MENU_USER,
        user_check: function(User $u) {
            return $u->hasAdminPanel();
        },
        dev_only: true,
   ), 
];