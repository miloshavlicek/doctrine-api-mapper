<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

class PutEntityRequest extends AEntityRequest implements IEntityRequest
{

    /**
     * @return void
     */
    protected function solveIt(): void
    {
        $item = $this->repository->find($this->paramFetcher->get($this->schema::ENTITY_REQUEST_ID_KEY));

        if ($item) {
            $item = $this->mapEntitySet($item);
            $this->em->persist($item);
            $this->em->flush($item);
            $this->out['status'] = true;
        } else {
            $this->out['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.itemNotFound', ['%id%' => $this->paramFetcher->get('id')], 'doctrine-api-mapper'))];
        }
    }

}