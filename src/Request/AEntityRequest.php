<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Miloshavlicek\DoctrineApiMapper\Entity\IPropertiesListEntity;
use Miloshavlicek\DoctrineApiMapper\EntityFilter\IEntityFilter;
use Miloshavlicek\DoctrineApiMapper\Exception\AccessDeniedException;
use Miloshavlicek\DoctrineApiMapper\Exception\BadRequestException;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;
use Miloshavlicek\DoctrineApiMapper\Mapper\ParamToEntityMethod;
use Miloshavlicek\DoctrineApiMapper\Params\GetParams;
use Miloshavlicek\DoctrineApiMapper\Params\IParams;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;
use Miloshavlicek\DoctrineApiMapper\Schema\DefaultSchema;
use Miloshavlicek\DoctrineApiMapper\Service\ACLValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AEntityRequest
{

    /** @var UserInterface|null */
    public $user;

    /** @var array */
    protected $out = [
        'messages' => [],
        'status' => true
    ];

    /** @var IParams */
    protected $params;

    /** @var string|null */
    protected $schema;

    /** @var Request */
    protected $request;

    /** @var ParamFetcherInterface */
    protected $paramFetcher;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var IApiRepository */
    protected $repository;

    /** @var IEntityFilter[] */
    protected $filters = [];

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ACLValidator */
    protected $aclValidator;

    /**
     * AEntityRequest constructor.
     * @param ParamFetcherInterface $paramFetcher
     * @param string $params
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param ACLValidator $aclValidator
     * @param $user
     */
    public function __construct(
        ParamFetcherInterface $paramFetcher,
        string $params,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ACLValidator $aclValidator,
        $user
    )
    {
        $this->paramFetcher = $paramFetcher;
        $this->params = new $params($paramFetcher, $user);
        $this->em = $em;
        $this->translator = $translator;
        $this->aclValidator = $aclValidator;

        $this->setUser($user);
    }

    /**
     * @param UserInterface|null $user
     */
    private function setUser(?UserInterface $user): void
    {
        $this->user = $user;
        if ($this->repository && method_exists($this->repository, 'setUser')) {
            $this->repository->setUser($user);
        }
    }

    /**
     * @return array
     */
    public function solve(): array
    {
        if ($this->schema === null) {
            $this->schema = DefaultSchema::class;
        }

        try {
            $this->solveLang();
            $this->solveFilters();

            $this->params->setRepository($this->repository);
            $this->params->init($this->schema);

            $response['status'] = null;

            $this->solveIt();
        } catch (AuthenticationException $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.authRequired', [], 'doctrine-api-mapper'), 'text' => $e->getMessage()];
        } catch (AccessDeniedException $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.accessDenied', [], 'doctrine-api-mapper'), 'text' => $e->getMessage()];
        } catch (InternalException $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.internalError', [], 'doctrine-api-mapper'), 'text' => $e->getMessage()];
        } catch (BadRequestException $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.badRequest', [], 'doctrine-api-mapper'), 'text' => $e->getMessage()];
        } /*catch (ORMException $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.dbError', [], 'doctrine-api-mapper')];
        } catch (\Exception $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.unknown', [], 'doctrine-api-mapper')];
        }*/

        return $this->getResponse();
    }

    private function solveLang()
    {
        $dynamicParam = new QueryParam();
        $dynamicParam->name = $this->schema::LANG_KEY;
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);

        $lang = $this->paramFetcher->get($this->schema::LANG_KEY);

        $this->translator->setLocale($lang ?: 'en');
    }

    private function solveFilters()
    {
        $dynamicParam = new QueryParam();
        $dynamicParam->name = $this->schema::FILTER_KEY;
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);

        $filters = $this->paramFetcher->get($this->schema::FILTER_KEY);
        if ($filters) {
            foreach (explode(',', $filters) as $filterName) {
                if ($filter = $this->repository->getFilter($filterName)) {
                    $this->addFilter($filterName, $filter);
                } else {
                    throw new BadRequestException('Filter not supported or insufficient rights.');
                }
            }
        }
    }

    /**
     * @param string|int $name
     * @param IEntityFilter $filter
     * @param bool $overwrite
     * @throws InternalException
     */
    public function addFilter($name, IEntityFilter $filter, bool $overwrite = false): void
    {
        if (!$overwrite && isset($this->filters[$name])) {
            throw new InternalException('Filter with the same name already set.');
        }

        $this->filters[$name] = $filter;
        $this->params->setAcl($name, $filter->getAcl());
    }

    /**
     * @return void
     */
    abstract protected function solveIt(): void;

    /**
     * @return array
     */
    protected function getResponse(): array
    {
        if ($this->params && $this->params instanceof GetParams && $this->params->isShowUser()) {
            $this->processUser();
        }
        return $this->schema::mapOutput($this->out);
    }

    private function processUser()
    {
        if ($this->user) {
            $this->out['user']['id'] = $this->user->getId();
        } else {
            $this->out['user'] = null;
        }
    }

    public function getParams(): ?IParams
    {
        return $this->params;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return IApiRepository|null
     */
    public function getRepository(): ?IApiRepository
    {
        return $this->repository;
    }

    /**
     * @param IApiRepository|null $repository
     */
    public function setRepository(?IApiRepository $repository): void
    {
        $this->repository = $repository;
        if ($this->user && method_exists($this->repository, 'setUser')) {
            $this->repository->setUser($this->user);
        }
    }

    protected function getUserRoles(): array
    {
        return $this->user ? $this->user->getRoles() : [];
    }

    /**
     * @param object $entity
     * @return mixed
     */
    protected function mapEntitySet($entity)
    {
        $params = $this->filterEntityNamesByPrefix();
        $this->aclValidator->validateWrite($this->repository, array_keys($params), $this->getAcls(), $this->user);
        return (new ParamToEntityMethod($entity, $this->repository->getEntityWriteProperties($this->getAcls()), $this->repository->getEntityJoins($this->getAcls())))->resolveSet($params);
    }

    /**
     * @param array $entities
     * @return array
     */
    private function filterEntityNamesByPrefix(): array
    {
        $out = [];
        foreach ($this->paramFetcher->all() as $entityKey => $entity) {
            if (substr($entityKey, 0, strlen($this->schema::ENTITY_PREFIX)) === $this->schema::ENTITY_PREFIX) {
                $out[substr($entityKey, strlen($this->schema::ENTITY_PREFIX))] = $this->paramFetcher->get($entityKey);
            }
        }

        return $out;
    }

    protected function getAcls(): array
    {
        $out = [$this->repository->getAcl()];

        foreach ($this->filters as $filter) {
            if ($filter->getAcl()) {
                $out[] = $filter->getAcl();
            }
        }

        return $out;
    }

}