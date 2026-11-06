<?php

namespace App\Repository;

use App\Entity\Jardin;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Usuario) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    // Los usuarios que ve quien trabaja en un jardín: sólo los de ese jardín.
    // Sin jardín (el super administrador) se ven todos.
    public function createQueryBuilderVisiblesPara(?Jardin $jardin): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.jardin', 'j')
            ->addSelect('j')
            ->orderBy('u.apellido', 'ASC')
            ->addOrderBy('u.nombre', 'ASC');

        if (null !== $jardin) {
            $qb->andWhere('u.jardin = :jardin')->setParameter('jardin', $jardin);
        }

        return $qb;
    }
}
