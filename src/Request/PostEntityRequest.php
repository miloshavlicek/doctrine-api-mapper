<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

class PostEntityRequest extends AEntityRequest implements IEntityRequest
{

    /**
     * @return void
     */
    protected function solveIt(): void
    {
        $this->aclValidator->validateCreate($this->repository, [], $this->user);

        $item = $this->repository->create();

        $item = $this->mapEntitySet($item, false);

        $this->em->persist($item);
        $this->em->flush($item);

        $this->out->addResult('id', $item->getId());
    }

}