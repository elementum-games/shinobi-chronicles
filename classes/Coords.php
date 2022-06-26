<?php

class Coords {
    public int $x;
    public int $y;

    public function __construct(int $x, int $y) {
        $this->x = $x;
        $this->y = $y;
    }

    public function toString(): string {
        return "{$this->x}:{$this->y}";
    }

    /**
     * @param string $coords_str
     * @return Coords
     * @throws Exception
     */
    public static function fromString(string $coords_str): Coords {
        $coords_arr = explode(':', $coords_str);
        if(count($coords_arr) != 2) {
            throw new Exception("Invalid coords str: {$coords_str}!");
        }

        return new Coords(x: $coords_arr[0], y: $coords_arr[1]);
    }
}