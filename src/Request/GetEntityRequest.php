<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Miloshavlicek\DoctrineApiMapper\Mapper\ParamToEntityMethod;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetEntityRequest extends AEntityRequest implements IEntityRequest
{

    /** @var array */
    private $filter = [];

    /** @var bool */
    private $showCountInResult = true;

    /** @var bool */
    private $singleResult = false;

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
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @return bool
     */
    public function isSingleResult(): bool
    {
        return $this->singleResult;
    }

    /**
     * @param bool $singleResult
     */
    public function setSingleResult(bool $singleResult): void
    {
        $this->singleResult = $singleResult;
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function solveIt(): void
    {
        $qb = $this->prepareQueryBuilder();

        if ($this->showCountInResult) {
            // TODO: optimize
            $this->out['x-total-count'] = count($qb->getQuery()->getResult());
        }

        if (!$this->singleResult) {
            $this->params->getLimit() && $qb->setMaxResults($this->params->getLimit());
            $this->params->getOffset() && $qb->setFirstResult($this->params->getOffset());
        }

        $q = $qb->getQuery();

        try {
            $items = $this->singleResult ? [$q->getSingleResult()] : $q->getResult();
        } catch (NoResultException $e) {
            $this->out['messages'][] = ['type' => 'warn', 'text' => 'No results found!'];
            $items = [];
        }

        $res = [];
        foreach ($items as $item) {
            $res[] = $this->mapEntityGet($item, $this->params->getFields());
        }

        $this->out['result'] = $res;
    }

    /**
     * @param IApiRepository $repository
     * @return QueryBuilder
     */
    private function prepareQueryBuilder(): QueryBuilder
    {
        /** @var QueryBuilder $qb */
        $qb = $this->repository->createQueryBuilder('e');

        $this->mapCriteria($qb, $this->schema::FILTER_PREFIX);

        $paramCounter = 1;
        foreach ($this->filter as $filterKey => $filterValue) {
            $qb->andWhere(
                $qb->expr()->eq('e.' . $filterKey, ':filter_' . $paramCounter)
            );
            $qb->setParameter('filter_' . $paramCounter, $filterValue);
            $paramCounter++;
        }

        for ($i = 0; isset($this->params->getSort()[$i]); $i++) {
            $qb->addOrderBy(
                'e.' . ParamToEntityMethod::translate($this->params->getSort()[$i]),
                isset($this->params->getOrder()[$i]) ? $this->params->getOrder()[$i] : 'ASC'
            );
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param string $filterPrefix
     */
    private function mapCriteria(QueryBuilder $qb, string $filterPrefix)
    {
        $criteria = [];
        foreach ($this->repository::getEntityReadProperties() as $property) {
            if ($this->paramFetcher->get($filterPrefix . $property) !== null) {
                $criteria[ParamToEntityMethod::translate($property)] = $this->paramFetcher->get($filterPrefix . $property);
            }
        }
        $paramCounter = 1;
        foreach ($criteria as $criteriaKey => $criteriaValue) {
            $qb->andWhere($qb->expr()->like('e.' . $criteriaKey, ':criteria_' . $paramCounter));
            $qb->setParameter('criteria_' . $paramCounter, $criteriaValue . '%');
            $paramCounter++;
        }
    }

    /**
     * @param $entity
     * @param array $params
     * @return mixed
     */
    private function mapEntityGet($entity, array $params)
    {
        $params && $this->checkMapEntityParamsValidity($params);
        return (new ParamToEntityMethod($entity, $params))->resolveGet();
    }

    /**
     * @param array $params
     */
    private function checkMapEntityParamsValidity(array $params)
    {
        foreach ($params as $param) {
            $explodes = explode('.', $param);

            $level = 0;
            $innerRepository = $this->repository;
            foreach ($explodes as $explode) {
                $level++;
                if ($level < count($explodes)) {
                    if (!in_array($explode . '.', $innerRepository::getEntityReadProperties())) {
                        throw new BadRequestHttpException(sprintf('Property "%s" not supported.', $param));
                    }

                    $innerRepository = $innerRepository::getEntityJoin($property);
                } else {
                    if (!in_array($explode, $innerRepository::getEntityReadProperties())) {
                        throw new BadRequestHttpException(sprintf('Property "%s" not supported.', $param));
                    }
                }
            }
        }
    }

}