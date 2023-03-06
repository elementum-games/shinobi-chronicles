<?php

class TravelCoords {

    public int $x;
    public int $y;
    public int $z;


    public function __construct($location_string) {
        $loc = array_map('trim', explode('.', $location_string));
        $this->x = (int) $loc[0];
        $this->y = (int) $loc[1];
        $this->z = (int) $loc[2];
    }

    public function fetchString(): string {
        return $this->x . '.' . $this->y . '.' . $this->z;
    }

}