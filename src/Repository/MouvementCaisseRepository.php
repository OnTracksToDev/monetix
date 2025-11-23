<?php

namespace App\Repository;

use App\Entity\MouvementCaisse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MouvementCaisse>
 */
class MouvementCaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementCaisse::class);
    }

    /**
     * @return MouvementCaisse[] Returns an array of MouvementCaisse objects
     */
    public function findRecentMouvements(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MouvementCaisse[] Returns an array of avances non remboursées
     */
    public function findAvancesNonRemboursees(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.type = :type')
            ->andWhere('m.rembourse = false')
            ->setParameter('type', 'avance')
            ->orderBy('m.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * NOUVELLE MÉTHODE : Trouve les avances non remboursées par gestionnaire
     */
    public function findAvancesNonRembourseesParGestionnaire(string $gestionnaire): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.type = :type')
            ->andWhere('m.gestionnaire = :gestionnaire')
            ->andWhere('m.rembourse = false')
            ->setParameter('type', 'avance')
            ->setParameter('gestionnaire', $gestionnaire)
            ->orderBy('m.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * NOUVELLE MÉTHODE : Calcule le total des dettes par gestionnaire
     */
    public function getTotalDettesParGestionnaire(): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('m.gestionnaire, SUM(m.montant - COALESCE(m.montantRemboursePartiel, 0)) as total_dette')
            ->where('m.type = :type')
            ->andWhere('m.rembourse = false')
            ->setParameter('type', 'avance')
            ->groupBy('m.gestionnaire')
            ->having('total_dette > 0')
            ->getQuery()
            ->getResult();

        $dettes = [];
        foreach ($result as $row) {
            $dettes[$row['gestionnaire']] = (float) $row['total_dette'];
        }

        return $dettes;
    }

    public function getTotalByType(string $type): float
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as total')
            ->where('m.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * NOUVELLE MÉTHODE : Total des remboursements partiels
     */
    public function getTotalRemboursementsPartiels(): float
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.montantRemboursePartiel) as total')
            ->where('m.type = :type')
            ->andWhere('m.rembourse = false')
            ->andWhere('m.montantRemboursePartiel > 0')
            ->setParameter('type', 'avance')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }
}