<?php

class EntityId {
    public string $entity_type;
    public int $id;

    /**
     * EntityId constructor.
     * @param string $entity_type
     * @param int    $id
     */
    public function __construct(string $entity_type, int $id) {
        $this->entity_type = $entity_type;
        $this->id = $id;
    }

    public function toString(): string {
        return "{$this->entity_type}:{$this->id}";
    }

    public static function fromString(string $entity_id_str): EntityId {
        $parts = explode(':', $entity_id_str);

        return new EntityId($parts[0], $parts[1]);
    }
}