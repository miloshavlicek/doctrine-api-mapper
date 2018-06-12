<?php

namespace Miloshavlicek\DoctrineApiMapper\Service;

use Miloshavlicek\DoctrineApiMapper\Exception\AccessDeniedException;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;
use Symfony\Component\Translation\TranslatorInterface;

class ACLValidator
{

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        TranslatorInterface $translator
    )
    {
        $this->translator = $translator;
    }

    public function validateRead(IApiRepository $baseRepository, array $params, array $acls = [], $user = null)
    {
        $this->validate($baseRepository, 'read', $params, $acls, $user);
    }

    private function validate(IApiRepository $baseRepository, string $access, array $params, array $acls = [], $user = null)
    {
        if ($baseRepository->getAcl()) {
            $acls['*'] = $baseRepository->getAcl();
        }

        foreach ($params as $param) {
            $explodes = explode('.', $param);

            $level = 0;

            /** @var IApiRepository $innerRepository */
            $innerRepository = $baseRepository;

            foreach ($explodes as $explode) {
                $level++;
                if ($level < count($explodes)) { // is not property, but join
                    if (!$innerRepository->hasEntityJoin($explode)) {
                        throw new AccessDeniedException($this->translator->trans('exception.joinNotSupported', ['%param%' => $param, '%level%' => $level, '%explode%' => $explode], 'doctrine-api-mapper'));
                    }
                    if (!$innerRepository->hasPermissionEntityJoin($explode, $level === 1 ? $acls : [$innerRepository->getAcl()])) {
                        throw new AccessDeniedException($this->translator->trans('exception.insufPermJoin', ['%param%' => $param, '%level%' => $level, '%explode%' => $explode], 'doctrine-api-mapper'));
                    }

                    $innerRepository = $innerRepository->getEntityJoin($explode, $level === 1 ? $acls : [$innerRepository->getAcl()]);

                    if ($innerRepository === null) {
                        throw new InternalException('Join repository not specified.');
                    }

                    $innerRepository->setUser($user);
                } else { // is last child, so it is property
                    if ($access === 'read') {
                        if ($explode !== 'id' && !in_array($explode, $innerRepository->getEntityReadProperties($level === 1 ? $acls : [$innerRepository->getAcl()]))) {
                            // everyone has access to id if has access to join
                            throw new AccessDeniedException($this->translator->trans('exception.propertyNotReadable', ['%param%' => $param], 'doctrine-api-mapper'));
                        }
                    } elseif ($access === 'write') {
                        if (!in_array($explode, $innerRepository->getEntityWriteProperties($level === 1 ? $acls : [$innerRepository->getAcl()]))) {
                            // everyone has access to id if has access to join
                            throw new AccessDeniedException($this->translator->trans('exception.propertyNotWritable', ['%param%' => $param], 'doctrine-api-mapper'));
                        }
                    } else {
                        throw new InternalException(sprintf('Unknown access type "%s"', $access));
                    }
                }
            }
        }
    }

    public function validateWrite(IApiRepository $baseRepository, array $params, array $acls = [], $user = null)
    {
        $this->validate($baseRepository, 'write', $params, $acls, $user);
    }

    public function validateCreate(IApiRepository $baseRepository, array $acls = [], $user = null)
    {
        if ($baseRepository->getAcl()) {
            $acls['*'] = $baseRepository->getAcl();
        }

        foreach ($acls as $acl) {
            if ($acl->getEntityCreatePermission($user ? $user->getRoles() : [])) {
                return;
            }
        }

        throw new AccessDeniedException($this->translator->trans('exception.insufficientPermissions', ['%param%' => $param], 'doctrine-api-mapper'));
    }

    public function validateDelete(IApiRepository $baseRepository, array $acls = [], $user = null)
    {
        if ($baseRepository->getAcl()) {
            $acls['*'] = $baseRepository->getAcl();
        }

        foreach ($acls as $acl) {
            if ($acl->getEntityDeletePermission($user ? $user->getRoles() : [])) {
                return;
            }
        }

        throw new AccessDeniedException($this->translator->trans('exception.insufficientPermissions', ['%param%' => $param], 'doctrine-api-mapper'));
    }

}