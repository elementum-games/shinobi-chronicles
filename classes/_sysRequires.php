<?php
/**
 * This file should contain all the dependencies needed to initialize
 *          the System without requiring any other files.
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/EntityId.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/MarkdownParser.php';
require_once __DIR__ . '/API.php';
require_once __DIR__ . '/Layout.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Route.php';

require_once __DIR__ . '/../classes/event/DoubleExpEvent.php';
require_once __DIR__ . '/../classes/event/DoubleReputationEvent.php';
require_once __DIR__ . '/../classes/event/BonusExpWeekend.php';
require_once __DIR__ . '/../classes/event/HolidayBonusEvent.php';