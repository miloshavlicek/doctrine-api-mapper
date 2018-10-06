<?php

namespace Miloshavlicek\DoctrineApiMapper\Service;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Miloshavlicek\DoctrineApiMapper\EntityFilter\IEntityFilter;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;
use Miloshavlicek\DoctrineApiMapper\Params\DeleteParams;
use Miloshavlicek\DoctrineApiMapper\Params\GetParams;
use Miloshavlicek\DoctrineApiMapper\Params\OptionsParams;
use Miloshavlicek\DoctrineApiMapper\Params\PatchParams;
use Miloshavlicek\DoctrineApiMapper\Params\PostParams;
use Miloshavlicek\DoctrineApiMapper\Params\PutParams;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;
use Miloshavlicek\DoctrineApiMapper\Request\DeleteEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\GetEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\IEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\OptionsEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\PatchEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\PostEntityRequest;
use Miloshavlicek\DoctrineApiMapper\Request\PutEntityRequest;
use Symfony\Component\Translation\TranslatorInterface;

class EntityRequestFactory
{

    /** @var ParamFetcherInterface */
    private $paramFetcher;

    /** @var EntityManagerInterface */
    private $em;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ACLValidator */
    private $aclValidator;

    /** @var Output */
    private $out;

    public function __construct(
        ParamFetcherInterface $paramFetcher,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ACLValidator $aclValidator,
        Output $out
    )
    {
        $this->paramFetcher = $paramFetcher;
        $this->em = $em;
        $this->translator = $translator;
        $this->aclValidator = $aclValidator;
        $this->out = $out;
    }

    /**
     * @param string $method
     * @param string $repository
     * @param IEntityFilter[] $filters
     * @return IEntityRequest
     * @throws InternalException
     */
    public function create(string $method, IApiRepository $repository, array $filters = [], $user = null): IEntityRequest
    {
        if (!($repository instanceof IApiRepository)) {
            throw new InternalException('Repository have to be instanceof IApiRepository');
        }

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
                throw new InternalException(sprintf('Unsupported request method "%s".', $method));
                break;
        }

        $solver = new $class($this->paramFetcher, $params, $this->em, $this->translator, $this->aclValidator, $user, $this->out);
        $solver->setRepository($repository);

        foreach ($filters as $filterKey => $filter) {
            $solver->addFilter($filterKey, $filter);
        }

        return $solver;
    }

}
