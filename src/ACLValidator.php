<?php

namespace Miloshavlicek\DoctrineApiMapper;

use App\ACLEntity\AACL;
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
                        throw new BadRequestHttpException(sprintf('Join "%s" not supported (level: %d "%s").', $param, $level, $explode));
                    }
                    if (!$innerRepository->hasPermissionEntityJoin($explode, $level === 1 ? $acl : $innerRepository->getAcl())) {
                        throw new BadRequestHttpException(sprintf('Insufficient permissions for join "%s" (level: %d "%s").', $param, $level, $explode));
                    }

                    $innerRepository = $innerRepository->getEntityJoin($explode, $level === 1 ? $acl : $innerRepository->getAcl());
                    $innerRepository->setUser($user);
                } else { // is last child, so it is property
                    if ($access === 'read') {
                        if ($explode !== 'id' && !in_array($explode, $innerRepository->getEntityReadProperties($level === 1 ? $acl : $innerRepository->getAcl()))) {
                            // everyone has access to id if has access to join
                            throw new BadRequestHttpException(sprintf('Property "%s" not supported or insufficient read permissions.', $param));
                        }
                    } elseif ($access === 'write') {
                        if (!in_array($explode, $innerRepository->getEntityWriteProperties($level === 1 ? $acl : $innerRepository->getAcl()))) {
                            // everyone has access to id if has access to join
                            throw new BadRequestHttpException(sprintf('Property "%s" not supported or insufficient write permissions.', $param));
                        }
                    } else {
                        throw new \Exception(sprintf('Unknown access type "%s"', $access));
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
            throw new \Exception('Insufficient permissions!');
        }
    }

}