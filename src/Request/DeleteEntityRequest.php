<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

class DeleteEntityRequest extends AEntityRequest implements IEntityRequest
{

    /**
     * @return void
     */
    protected function solveIt(): void
    {
        $this->aclValidator->validateDelete($this->repository, [], $this->user);

        $item = $this->repository->find($this->paramFetcher->get($this->schema::ENTITY_REQUEST_ID_KEY));

        $response = [];
        if ($item) {
            $this->em->remove($item);
            $this->em->flush($item);
        } else {
            $this->out->addError($this->translator->trans('exception.itemNotFound', ['%id%' => $this->paramFetcher->get('id')], 'doctrine-api-mapper'));
        }
    }

}