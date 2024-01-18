<?php

namespace App\Repository;

use App\Entity\Sorteo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->andWhere('s.Fecha <= :now') // Aquí ajusta a tu lógica específica
            ->andWhere('s.fecha_fin >= :now') // Ajusta esto según tus necesidades
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }



    //    /**
    //     * @return Sorteo[] Returns an array of Sorteo objects
    //     */
    //    public function findByExampleField($value): array
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