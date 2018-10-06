<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

class PatchEntityRequest extends AEntityRequest implements IEntityRequest
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
        } else {
            $this->out->addError($this->translator->trans('exception.itemNotFound', ['%id%' => $this->paramFetcher->get('id')], 'doctrine-api-mapper'));
        }
    }

}