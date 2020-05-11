<?php

declare(strict_types=1);

namespace npc\events;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityEvent;

class npcDeletionEvent extends EntityEvent {

    public function __construct(Entity $entity) {
        $this->entity = $entity;
    }

}
