<?php
class TimeManager {
    const SERVER_TIME_ZONE = "America/New_York";
    const DEFAULT_TIME = "now";

    const REPUTATION_RESET_DAY = "friday";
    const REPUTATION_RESET_HOUR = 20;
    const REPUTATION_RESET_MINUTE = 0;

    public function __construct(
        public DateTimeImmutable $SERVER_TIME,
        public DateTimeImmutable $REPUTATION_RESET,
        public DateTimeImmutable $UNIX_TIME,
    ) {}

    public static function loadReputationReset() {
        $rep_reset = self::setTime();
        // If current day isn't reset day, move time to that dat
        if($rep_reset->format('D') != self::REPUTATION_RESET_DAY) {
            $rep_reset->modify("next " . self::REPUTATION_RESET_DAY);
        }
        // Set time to when the job will run next - Currently this is Sat @ 0100GMT
        return $rep_reset->setTime(hour: self::REPUTATION_RESET_HOUR, minute: self::REPUTATION_RESET_MINUTE);
    }

    public static function setTime(
        string $time = self::DEFAULT_TIME,
        string $time_zone = self::SERVER_TIME_ZONE
    ): DateTimeImmutable {
        return new DateTimeImmutable($time, new DateTimeZone($time_zone));
    }

    public static function LOAD(): TimeManager {
        return new TimeManager(
            SERVER_TIME: self::setDateTimeImm(),
            REPUTATION_RESET: self::loadReputationReset(),
            UNIX_TIME: self::setDateTimeImm(
                time_zone: 'Europe/London'
            )
        );
    }
}