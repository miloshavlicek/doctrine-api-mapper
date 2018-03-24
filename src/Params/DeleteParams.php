<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Controller\Annotations\QueryParam;

class DeleteParams extends AParams implements IParams
{

    /** @var bool */
    protected $isRequest = true;

    protected function initIt(): void
    {
        $this->declareUrlParameters();
    }

    private function declareUrlParameters(): void
    {
        $dynamicParam = new QueryParam();
        $dynamicParam->name = $this->schema::ENTITY_REQUEST_ID_KEY;
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);
    }

}