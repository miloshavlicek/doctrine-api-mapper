<?php

namespace Miloshavlicek\DoctrineApiMapper\Factory;

use Miloshavlicek\DoctrineApiMapper\Params\DeleteParams;
use Miloshavlicek\DoctrineApiMapper\Params\GetParams;
use Miloshavlicek\DoctrineApiMapper\Params\OptionsParams;
use Miloshavlicek\DoctrineApiMapper\Params\PatchParams;
use Miloshavlicek\DoctrineApiMapper\Params\PostParams;
use Miloshavlicek\DoctrineApiMapper\Params\PutParams;
use Miloshavlicek\DoctrineApiMapper\Request\DeleteEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\GetEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\IEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\OptionsEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\PatchEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\PostEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\PutEntityRequest;
use Miloshavlicek\DoctrineApiMapper\IApiRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EntityRequestFactory
{

    /** @var ParamFetcherInterface */
    private $paramFetcher;

    /** @var EntityManagerInterface */
    private $em;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ParamFetcherInterface $paramFetcher,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    )
    {
        $this->paramFetcher = $paramFetcher;
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @param string $method
     * @param string $entity
     * @param array $filter
     * @return IEntityRequest
     * @throws \Exception
     */
    public function create(string $method, string $entity, array $filter = []): IEntityRequest
    {
        switch ($method) {
            case 'GET':
                $class = GetEntityRequest::class;
                $params = GetParams::class;
                break;
            case 'POST':
                $class = PostEntityRequest::class;
                $params = PostParams::class;
                break;
            case 'DELETE':
                $class = DeleteEntityRequest::class;
                $params = DeleteParams::class;
                break;
            case 'PUT':
                $class = PutEntityRequest::class;
                $params = PutParams::class;
                break;
            case 'PATCH':
                $class = PatchEntityRequest::class;
                $params = PatchParams::class;
                break;
            case 'OPTIONS':
                $class = OptionsEntityRequest::class;
                $params = OptionsParams::class;
                break;
            default:
                throw new \Exception(sprintf('Unsupported request method "%s".', $method));
                break;
        }

        $solver = new $class($this->paramFetcher, new $params($this->paramFetcher), $this->em, $this->translator);

        $solver->setEntity($entity);

        $repository = $this->em->getRepository($entity);

        if ($repository instanceof IApiRepository) {
            $solver->setRepository($repository);
        }

        if (count($filter) && $method === 'GET') {
            $solver->setFilter($filter);
        } elseif (count($filter)) {
            throw new \Exception('Filter appliable only for GET queries.');
        }

        return $solver;
    }

}
