<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Controller\Annotations\QueryParam;

class GetParams extends AParams implements IParams
{

    /** @var bool */
    protected $isRequest = false;

    /** @var int|null */
    private $limit;

    /** @var int|null */
    private $offset;

    /** @var int|null */
    private $page;

    /** @var array */
    private $sort = [];

    /** @var array */
    private $order = [];

    /** @var array */
    private $fields = ['id'];


    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    protected function initIt(): void
    {
        $this->declareUrlParameters();

        $paramFetcher = $this->paramFetcher;
        $schema = $this->schema;

        $this->limit = $paramFetcher->get($schema::LIMIT_KEY);
        $this->offset = $paramFetcher->get($schema::OFFSET_KEY);
        $this->page = $paramFetcher->get($schema::PAGE_KEY);
        $paramFetcher->get($schema::SORT_KEY) && $this->sort = explode(',', $paramFetcher->get($schema::SORT_KEY));
        $paramFetcher->get($schema::ORDER_KEY) && $this->order = explode(',', $paramFetcher->get($schema::ORDER_KEY));
        $paramFetcher->get($schema::FIELDS_KEY) && $this->fields = explode(',', $paramFetcher->get($schema::FIELDS_KEY));

        if ($this->page) {
            $this->offset = $this->limit * ($this->page - 1);
        }

        $this->check();
    }

    private function declareUrlParameters(): void
    {
        $schema = $this->schema;

        $this->attachAllEntityReadPropertiesToUrl($schema::FILTER_PREFIX);

        $paramFetcher = $this->paramFetcher;

        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::FIELDS_KEY;
        $dynamicParam->nullable = true;
        $paramFetcher->addParam($dynamicParam);

        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::PAGE_KEY;
        $dynamicParam->requirements = '\d+';
        $dynamicParam->nullable = true;
        $paramFetcher->addParam($dynamicParam);

        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::LIMIT_KEY;
        $dynamicParam->requirements = '\d+';
        $dynamicParam->nullable = true;
        $paramFetcher->addParam($dynamicParam);

        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::OFFSET_KEY;
        $dynamicParam->requirements = '\d+';
        $dynamicParam->nullable = true;
        $paramFetcher->addParam($dynamicParam);

        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::SORT_KEY;
        $dynamicParam->nullable = true;
        $paramFetcher->addParam($dynamicParam);

        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::ORDER_KEY; // ASC or DESC
        $dynamicParam->nullable = true;
        $paramFetcher->addParam($dynamicParam);
    }

    private function check(): void
    {
        $schema = $this->schema;
        $paramFetcher = $this->paramFetcher;

        if ($paramFetcher->get($schema::OFFSET_KEY) && $paramFetcher->get($schema::PAGE_KEY)) {
            throw new BadRequestHttpException('You have to choose offset or page param, not both.');
        }

        if ($paramFetcher->get($schema::PAGE_KEY) && !$paramFetcher->get($schema::LIMIT_KEY)) {
            throw new BadRequestHttpException('When using page param, limit param is required.');
        }
    }

}