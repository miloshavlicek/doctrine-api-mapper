<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

class PostParams extends AParams implements IParams
{

    /** @var bool */
    protected $isRequest = true;

    protected function initIt(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->declareUrlParameters();

        $this->initialized = true;
    }

    private function declareUrlParameters()
    {
        $this->attachAllRepositoryWritePropertiesToUrl($this->schema::ENTITY_PREFIX);
    }

}