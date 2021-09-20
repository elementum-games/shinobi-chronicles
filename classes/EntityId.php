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
}