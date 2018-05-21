<?php

namespace Miloshavlicek\DoctrineApiMapper\Request;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Miloshavlicek\DoctrineApiMapper\Entity\IPropertiesListEntity;
use Miloshavlicek\DoctrineApiMapper\Mapper\ParamToEntityMethod;
use Miloshavlicek\DoctrineApiMapper\Params\IParams;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;
use Miloshavlicek\DoctrineApiMapper\Schema\DefaultSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AEntityRequest
{

    /** @var array */
    protected $out = [
        'messages' => [],
        'status' => true
    ];

    /** @var UserInterface */
    protected $user;

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
    private $translator;

    /**
     * GetEntityRequest constructor.
     * @param ParamFetcherInterface $paramFetcher
     * @param IParams $params
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ParamFetcherInterface $paramFetcher,
        IParams $params,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    )
    {
        $this->paramFetcher = $paramFetcher;
        $this->params = $params;
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function solve(): array
    {
        if ($this->schema === null) {
            $this->schema = DefaultSchema::class;
        }
        $this->checkUserRequirement();
        $this->params->setRepository($this->repository);
        $this->params->init($this->schema);
        $this->solveIt();
        return $this->getResponse();
    }

    protected function checkUserRequirement(): void
    {
        if ($this->userRequired && !$this->user) {
            throw new AuthenticationException('User not authenticated.');
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
        if ($this->params->isShowUser()) {
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
     * @param UserInterface|null $user
     */
    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
        if ($this->repository && method_exists($this->repository, 'setUser')) {
            $this->repository->setUser($user);
        }
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

    /**
     * @param object $entity
     * @return mixed
     */
    protected function mapEntitySet($entity)
    {
        $params = [];
        foreach ($this->paramFetcher->all() as $parameterKey => $parameter) {
            if ($parameterKey === 'id') {
                continue;
            }
            if (in_array($parameterKey,
                array_map(
                    function ($data) {
                        return $this->schema::ENTITY_PREFIX . $data;
                    },
                    $this->repository->getEntityWriteProperties()
                )
            )) {
                $params[ParamToEntityMethod::untranslate($parameterKey, $this->schema::ENTITY_PREFIX)] = $parameter;
            }
        };

        return (new ParamToEntityMethod($entity, $this->repository->getEntityWriteProperties()))->resolveSet($params);
    }

}