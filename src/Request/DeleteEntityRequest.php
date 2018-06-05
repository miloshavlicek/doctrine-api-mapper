<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

use Miloshavlicek\DoctrineApiMapper\ACLValidator;

class DeleteEntityRequest extends AEntityRequest implements IEntityRequest
{

    /**
     * @return void
     */
    protected function solveIt(): void
    {
        (new ACLValidator($this->repository))->validateDelete(null, $this->user);

        $item = $this->repository->find($this->paramFetcher->get($this->schema::ENTITY_REQUEST_ID_KEY));

        $response = [];
        if ($item) {
            $this->em->remove($item);
            $this->em->flush($item);
        } else {
            $this->out['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.itemNotFound', ['%id%' => $this->paramFetcher->get('id')], 'doctrine-api-mapper')];
        }
    }

}