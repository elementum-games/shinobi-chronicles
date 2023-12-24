<?php
// The _autoload file should contain only files not relative to System
// The remaining files in this are specific to loading and running the System
// The aim here is that the System should load and operate without any additional files

require_once __DIR__ . '/_autoload.php'; // Ensure game requires are loaded
// System requirements
require_once __DIR__ . '/../secure/vars.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/EntityId.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/MarkdownParser.php';
require_once __DIR__ . '/API.php';
require_once __DIR__ . '/Layout.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/TimeManager.php';

// Event requirements - TEMPORARY
require_once __DIR__ . '/../classes/event/DoubleExpEvent.php';
require_once __DIR__ . '/../classes/event/DoubleReputationEvent.php';
require_once __DIR__ . '/../classes/event/BonusExpWeekend.php';
