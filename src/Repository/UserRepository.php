<?php

namespace App\Repository;

use App\Entity\Apuesta;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
* @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

//     public function hasUserWonAnySorteo(User $user): array
//     {
//         $queryBuilder = $this->createQueryBuilder('u');
//         $queryBuilder
//             ->select('COUNT(a.id)')
//             ->from(Apuesta::class, 'a')
//             ->join('a.sorteo', 's')
//             ->join('a.numeroLoteria', 'nl')  // Agrega la relación con NumerosLoteria
//             ->andWhere('a.user = :user')
//             ->andWhere('s.state = 1')
//             ->andWhere('s.winner = nl.numero')  // Compara con el ID del número
//             ->setParameter('user', $user);
    
//         $count = $queryBuilder->getQuery()->getSingleScalarResult();
//         $query = $queryBuilder->getQuery();
// // Aún no has ganado ningún sorteo.
//         // return $count > 0;
//         // dump($queryBuilder->getQuery()->getResult());
//         return $queryBuilder->getQuery()->getResult();
//     }
public function hasUserWonAnySorteo(User $user): array
{
    $queryBuilder = $this->createQueryBuilder('u');
    $queryBuilder
        ->select('a')  // Selecciona la entidad Apuesta completa
        ->from(Apuesta::class, 'a')
        ->join('a.sorteo', 's')
        ->join('a.numeroLoteria', 'nl')  // Agrega la relación con NumerosLoteria
        ->andWhere('a.user = :user')
        ->andWhere('s.state = 1')
        ->andWhere('s.winner = nl.numero')  // Compara con el ID del número
        ->setParameter('user', $user);

    return $queryBuilder->getQuery()->getResult();
}

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
