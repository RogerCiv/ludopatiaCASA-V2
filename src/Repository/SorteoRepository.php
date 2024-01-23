<?php

namespace App\Repository;

use App\Entity\Sorteo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;

use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sorteo>
 *
 * @method Sorteo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sorteo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sorteo[]    findAll()
 * @method Sorteo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SorteoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sorteo::class);
    }

    /**
     * Obtiene el sorteo actual basado en la fecha.
     *
     * @return Sorteo|null
     */
    public function getSorteoActual(): ?Sorteo
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('s')
            ->andWhere('s.Fecha <= :now') 
            ->andWhere('s.fecha_fin >= :now') 
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Encuentra sorteos donde la fecha_fin sea menor que la fecha actual y el state sea 0.
     *
     * @return Sorteo[] Devuelve un array de objetos Sorteo
     */
    public function findSorteosPasados(): array
    {
        $now = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $query = $this->createQueryBuilder('s')
            ->andWhere('s.fecha_fin < :now ')
            ->andWhere('s.state = 0')
            ->setParameter('now', $now)
            // ->setParameter('state', 0)
            ->getQuery();
           

            // dump([
            //     'SQL' => $query->getSQL(),
            //     'Parameters' => $query->getParameters(),
            // ]);
        
            return $query->getResult();
    }


    //    /**
    //     * @return Sorteo[] Returns an array of Sorteo objects
    //     */
    //    public function findByExampleField($value): arrayabout:blank#blocked
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sorteo
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
