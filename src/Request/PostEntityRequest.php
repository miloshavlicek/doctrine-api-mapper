<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

class PostEntityRequest extends AEntityRequest implements IEntityRequest
{

    /**
     * @return void
     */
    protected function solveIt(): void
    {
        $item = $this->repository->create();

        $item = $this->mapEntitySet($item);

        $this->em->persist($item);
        $this->em->flush($item);

        $this->out['status'] = true;
        $this->out['result']['id'] = $item->getId();
    }

}