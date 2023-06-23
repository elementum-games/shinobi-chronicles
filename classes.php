<?php

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/DatabaseDeadlockException.php';
require_once __DIR__ . "/classes/EntityId.php";
require_once __DIR__ . "/classes/System.php";
require_once __DIR__ . "/classes/Router.php";
require_once __DIR__ . "/classes/Auth.php";
require_once __DIR__ . "/classes/API.php";
require_once __DIR__ . "/classes/ActionResult.php";
require_once __DIR__ . "/classes/ReportManager.php";

require_once __DIR__ . "/classes/RankManager.php";

require_once __DIR__ . "/classes/Jutsu.php";
require_once __DIR__ . "/classes/Item.php";
require_once __DIR__ . "/classes/Bloodline.php";

require_once __DIR__ . "/classes/battle/Fighter.php";
require_once __DIR__ . "/classes/User.php";
require_once __DIR__ . "/classes/NPC.php";

require_once __DIR__ . "/classes/SupportManager.php";

require_once __DIR__ . "/classes/Mission.php";
require_once __DIR__ . "/classes/SpecialMissions.php";

require_once __DIR__ . "/classes/battle/Battle.php";
require_once __DIR__ . "/classes/battle/BattleManager.php";

require_once __DIR__ . "/classes/battle/BattleV2.php";
require_once __DIR__ . "/classes/battle/BattleManagerV2.php";
require_once __DIR__ . "/classes/battle/BattlePageAPIResponse.php";

require_once __DIR__ . "/classes/Notifications.php";
require_once __DIR__ . "/classes/NearbyPlayers.php";

require_once __DIR__ . "/classes/Inbox.php";
require_once __DIR__ . "/classes/InboxUser.php";
require_once __DIR__ . "/classes/InboxManager.php";
require_once __DIR__ . "/classes/InboxAPIResponse.php";

require_once __DIR__ . "/classes/chat/ChatManager.php";
require_once __DIR__ . "/classes/chat/ChatAPIPresenter.php";

require_once __DIR__ . "/classes/travel/Travel.php";
require_once __DIR__ . "/classes/travel/TravelManager.php";
require_once __DIR__ . "/classes/travel/TravelApiPresenter.php";
require_once __DIR__ . "/classes/travel/TravelAPIResponse.php";
require_once __DIR__ . "/classes/travel/TravelCoords.php";
require_once __DIR__ . "/classes/travel/MapLocation.php";

require_once __DIR__ . "/classes/Village.php";

require_once __DIR__ . "/classes/SenseiManager.php";

require_once __DIR__ . "/classes/user/UserAPIResponse.php";
require_once __DIR__ . "/classes/user/UserAPIPresenter.php";
require_once __DIR__ . "/classes/user/UserAPIManager.php";

require_once __DIR__ . "/classes/navigation/NavigationAPIResponse.php";
require_once __DIR__ . "/classes/navigation/NavigationAPIPresenter.php";
require_once __DIR__ . "/classes/navigation/NavigationAPIManager.php";

require_once __DIR__ . "/classes/notification/NotificationAPIResponse.php";
require_once __DIR__ . "/classes/notification/NotificationAPIPresenter.php";
require_once __DIR__ . "/classes/notification/NotificationAPIManager.php";
require_once __DIR__ . "/classes/notification/NotificationManager.php";

require_once __DIR__ . "/classes/training/TrainingManager.php";

require_once __DIR__ . "/classes/news/NewsAPIResponse.php";
require_once __DIR__ . "/classes/news/NewsAPIPresenter.php";
require_once __DIR__ . "/classes/news/NewsAPIManager.php";