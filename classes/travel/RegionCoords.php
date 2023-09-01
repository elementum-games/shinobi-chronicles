<?php

class RegionCoords extends TravelCoords {
    public int $region_id = 0;
    public bool $border_top = false;
    public bool $border_bottom = false;
    public bool $border_left = false;
    public bool $border_right = false;
    public string $color = "rgba(0, 0, 0, 1)";
}