<?php

namespace Miloshavlicek\DoctrineApiMapper;

use App\ACLEntity\AACL;
use Miloshavlicek\DoctrineApiMapper\Exception\AccessDeniedException;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;

class ACLValidator
{

    /** @var IApiRepository */
    private $baseRepository;

    public function __construct(IApiRepository $baseRepository)
    {
        $this->baseRepository = $baseRepository;
    }

    public function validateRead(array $params, ?AACL $acl = null, $user = null)
    {
        $this->validate('read', $params, $acl, $user);
    }

    private function validate(string $access, array $params, ?AACL $acl = null, $user = null)
    {
        if ($acl === null) {
            $acl = $this->baseRepository->getAcl();
        }

        foreach ($params as $param) {
            $explodes = explode('.', $param);

            $level = 0;

            /** @var IApiRepository $innerRepository */
            $innerRepository = $this->baseRepository;

            foreach ($explodes as $explode) {
                $level++;
                if ($level < count($explodes)) { // is not property, but join
                    if (!$innerRepository->hasEntityJoin($explode)) {
                        throw new AccessDeniedException($this->translator->trans('exception.joinNotSupported', ['%param%' => $param, '%level%' => $level, '%explode%' => $explode], 'doctrine-api-mapper'));
                    }
                    if (!$innerRepository->hasPermissionEntityJoin($explode, $level === 1 ? $acl : $innerRepository->getAcl())) {
                        throw new AccessDeniedException($this->translator->trans('exception.insufPermJoin', ['%param%' => $param, '%level%' => $level, '%explode%' => $explode], 'doctrine-api-mapper'));
                    }

                    $innerRepository = $innerRepository->getEntityJoin($explode, $level === 1 ? $acl : $innerRepository->getAcl());
                    $innerRepository->setUser($user);
                } else { // is last child, so it is property
                    if ($access === 'read') {
                        if ($explode !== 'id' && !in_array($explode, $innerRepository->getEntityReadProperties($level === 1 ? $acl : $innerRepository->getAcl()))) {
                            // everyone has access to id if has access to join
                            throw new AccessDeniedException($this->translator->trans('exception.propertyNotReadable', ['%param%' => $param], 'doctrine-api-mapper'));
                        }
                    } elseif ($access === 'write') {
                        if (!in_array($explode, $innerRepository->getEntityWriteProperties($level === 1 ? $acl : $innerRepository->getAcl()))) {
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

    public function validateWrite(array $params, ?AACL $acl = null, $user = null)
    {
        $this->validate('write', $params, $acl, $user);
    }

    public function validateDelete(?AACL $acl = null, $user = null)
    {
        if ($acl === null) {
            $acl = $this->baseRepository->getAcl();
        }

        if (!$acl->getEntityDeletePermission($user ? $user->getRoles() : [])) {
            throw new AccessDeniedException($this->translator->trans('exception.insufficientPermissions', ['%param%' => $param], 'doctrine-api-mapper'));
        }
    }

}