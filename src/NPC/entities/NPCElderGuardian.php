<?php

declare(strict_types=1);

namespace NPC\entities;

class NPCElderGuardian extends NPCEntity {

    const TYPE_ID = 50;
    const HEIGHT = 1.9975;

    public function prepareMetadata(): void {
        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ELDER, true);
        parent::prepareMetadata();
    }

}
