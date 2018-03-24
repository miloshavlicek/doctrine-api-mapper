<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;

class PostEntityRequest extends AEntityRequest implements IEntityRequest
{

    /**
     * @return void
     */
    protected function solveIt(): void
    {
        $repository = $this->em->getRepository($this->entity);

        if ($repository instanceof IApiRepository) {
            $item = $repository->create();
        } else {
            $item = new $this->entity;
        }

        $item = $this->mapEntitySet($item);

        try {
            $this->em->persist($item);
            $this->em->flush($item);
            $this->out['result']['id'] = $item->getId();
        } catch (\Exception $e) {
            $this->out['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'text' => 'Database error.'];
        }
    }

}