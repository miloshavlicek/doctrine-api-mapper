<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Miloshavlicek\DoctrineApiMapper\Mapper\ParamToEntityMethod;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;

class GetEntityRequest extends AEntityRequest implements IEntityRequest
{

    /** @var bool */
    private $singleResult = false;

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

        if ($this->params->isShowCountInResult()) {
            // TODO: optimize
            $this->out['x-total-count'] = count($qb->getQuery()->getResult());
        }

        if ($this->params->isShowPermissions()) {
            $this->processPermissions();
        }

        if (!$this->singleResult) {
            $this->params->getLimit() && $qb->setMaxResults($this->params->getLimit());
            $this->params->getOffset() && $qb->setFirstResult($this->params->getOffset());
        }

        if ($this->params->isShowResult()) {
            $this->processResult($qb);
        }
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

        $this->filter && $this->filter->appendQb($qb);

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

        foreach ($this->getAcl()->getEntityReadProperties($this->getUserRoles()) as $property) {
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

    private function processPermissions()
    {
        $acl = $this->getAcl();

        $this->out['permissions'] = [
            'default' => [
                'read' => $acl->getEntityReadProperties($this->getUserRoles()),
                'write' => $acl->getEntityWriteProperties($this->getUserRoles()),
                'delete' => $acl->getEntityDeletePermission($this->getUserRoles()),
                'joins' => $acl->getEntityJoinsPermissions($this->getUserRoles())
            ]
        ];
    }

    /**
     * @param QueryBuilder $qb
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function processResult(QueryBuilder $qb)
    {
        $q = $qb->getQuery();

        try {
            $items = $this->singleResult ? [$q->getSingleResult()] : $q->getResult();
        } catch (NoResultException $e) {
            $this->out['messages'][] = ['type' => 'warn', 'title' => $this->translator->trans('exception.noResults', [], 'doctrine-api-mapper')];
            $items = [];
        }

        $res = [];
        foreach ($items as $item) {
            $res[] = $this->mapEntityGet($item, $this->params->getFields());
        }

        $this->out['result'] = $res;
    }

    /**
     * @param $entity
     * @param array $params
     * @return mixed
     */
    private function mapEntityGet($entity, array $params)
    {
        $this->aclValidator->validateRead($this->repository, $params, $this->getAcl(), $this->user);
        return (new ParamToEntityMethod($entity, $params))->resolveGet();
    }


}