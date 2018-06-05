<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Miloshavlicek\DoctrineApiMapper\ACLValidator;
use Miloshavlicek\DoctrineApiMapper\Entity\IPropertiesListEntity;
use Miloshavlicek\DoctrineApiMapper\Exception\AccessDeniedException;
use Miloshavlicek\DoctrineApiMapper\Exception\BadRequestException;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;
use Miloshavlicek\DoctrineApiMapper\Mapper\ParamToEntityMethod;
use Miloshavlicek\DoctrineApiMapper\Params\GetParams;
use Miloshavlicek\DoctrineApiMapper\Params\IParams;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;
use Miloshavlicek\DoctrineApiMapper\Schema\DefaultSchema;
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
    /** @var bool */
    protected $userRequired = false;

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

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * GetEntityRequest constructor.
     * @param ParamFetcherInterface $paramFetcher
     * @param IParams $params
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ParamFetcherInterface $paramFetcher,
        string $params,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        $user
    )
    {
        $this->paramFetcher = $paramFetcher;
        $this->params = new $params($paramFetcher, $user);
        $this->em = $em;
        $this->translator = $translator;

        $this->setUser($user);
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

            $this->checkUserRequirement();

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
        } catch (ORMException $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.dbError', [], 'doctrine-api-mapper')];
        } catch (\Exception $e) {
            $response['status'] = false;
            $this->out['messages'][] = ['type' => 'err', 'title' => $this->translator->trans('exception.unknown', [], 'doctrine-api-mapper')];
        }

        return $this->getResponse();
    }

    protected function checkUserRequirement(): void
    {
        if ($this->userRequired && !$this->user) {
            throw new AuthenticationException($this->translator->trans('exception.userNotAuthenticated', [], 'doctrine-api-mapper'));
        }
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
     * @param bool $val
     */
    public function setUserRequired(bool $val = true): void
    {
        $this->userRequired = $val;
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
        (new ACLValidator($this->repository))->validateWrite($params, $this->getAcl(), $this->user);
        return (new ParamToEntityMethod($entity, $this->repository->getEntityWriteProperties()))->resolveSet($params);
    }

    /**
     * @param array $entities
     * @return array
     */
    private function filterEntityNamesByPrefix(array $entities): array
    {
        $out = [];

        foreach ($this->paramFetcher->all() as $entityKey => $entity) {
            if (substr($entity, 0, strlen($this->schema::ENTITY_PREFIX) - 1) === $this->schema::ENTITY_PREFIX) {
                $out[] = substr($entity, strlen($this->schema::ENTITY_PREFIX));
            }
        }

        return $out;
    }

}