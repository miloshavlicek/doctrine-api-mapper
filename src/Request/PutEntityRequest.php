<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

class PutEntityRequest extends AEntityRequest implements IEntityRequest
{

    /**
     * @return void
     */
    protected function solveIt(): void
    {
        $item = $this->em->getRepository($this->entity)
            ->find($this->paramFetcher->get($this->schema::ENTITY_REQUEST_ID_KEY));

        $response = [];
        if ($item) {
            try {
                $item = $this->mapEntitySet($item);
                $this->em->persist($item);
                $this->em->flush($item);
                $response['status'] = true;
            } catch (\Exception $e) {
                $response['status'] = false;
                $this->out['messages'][] = ['type' => 'err', 'Database exception'];
            }
        } else {
            $this->out['status'] = false;
            $this->out['messages'][] = ['type' => 'err', sprintf('Item by ID "%d" not found!', $this->paramFetcher->get('id'))];
        }
    }

}