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

    /** @var bool */
    private $showCountInResult = false;

    /** @var bool */
    private $showPermissions = false;

    /** @var bool */
    private $showUser = false;

    /** @var bool */
    private $showResult = true;

    /**
     * @return bool
     */
    public function isShowCountInResult(): bool
    {
        return $this->showCountInResult;
    }

    /**
     * @param bool $showCountInResult
     */
    public function setShowCountInResult(bool $showCountInResult): void
    {
        $this->showCountInResult = $showCountInResult;
    }

    /**
     * @return bool
     */
    public function isShowPermissions(): bool
    {
        return $this->showPermissions;
    }

    /**
     * @param bool $showPermissions
     */
    public function setShowPermissions(bool $showPermissions): void
    {
        $this->showPermissions = $showPermissions;
    }

    /**
     * @return bool
     */
    public function isShowUser(): bool
    {
        return $this->showUser;
    }

    /**
     * @param bool $showUser
     */
    public function setShowUser(bool $showUser): void
    {
        $this->showUser = $showUser;
    }

    /**
     * @return bool
     */
    public function isShowResult(): bool
    {
        return $this->showResult;
    }

    /**
     * @param bool $showResult
     */
    public function setShowResult(bool $showResult): void
    {
        $this->showResult = $showResult;
    }

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
        in_array($paramFetcher->get($schema::I_COUNT_KEY), ['0', '1']) && $this->showCountInResult = $paramFetcher->get($schema::I_COUNT_KEY) === "1";
        in_array($paramFetcher->get($schema::I_PERM_KEY), ['0', '1']) && $this->showPermissions = $paramFetcher->get($schema::I_PERM_KEY) === "1";
        in_array($paramFetcher->get($schema::I_RESULT_KEY), ['0', '1']) && $this->showResult = $paramFetcher->get($schema::I_RESULT_KEY) === "1";
        in_array($paramFetcher->get($schema::I_USER_KEY), ['0', '1']) && $this->showUser = $paramFetcher->get($schema::I_USER_KEY) === "1";

        if ($this->page) {
            $this->offset = $this->limit * ($this->page - 1);
        }

        $this->check();
    }

    private function declareUrlParameters(): void
    {
        $schema = $this->schema;

        $this->attachAllRepositoryReadPropertiesToUrl($schema::FILTER_PREFIX);

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

        // Show rows count
        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::I_COUNT_KEY;
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);

        // Show entity permissions info
        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::I_PERM_KEY;
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);


        // Show result
        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::I_RESULT_KEY;
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);


        // Show info about user
        $dynamicParam = new QueryParam();
        $dynamicParam->name = $schema::I_USER_KEY;
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);
    }

    private function check(): void
    {
        $schema = $this->schema;
        $paramFetcher = $this->paramFetcher;

        if ($paramFetcher->get($schema::OFFSET_KEY) && $paramFetcher->get($schema::PAGE_KEY)) {
            throw new BadRequestHttpException($this->translator->trans('exception.badRequest_offsetOrPage'), [], 'doctrine-api-mapper'));
        }

        if ($paramFetcher->get($schema::PAGE_KEY) && !$paramFetcher->get($schema::LIMIT_KEY)) {
            throw new BadRequestHttpException($this->translator->trans('exception.badRequest_limitReqIfPage'), [], 'doctrine-api-mapper'));
        }
    }

}