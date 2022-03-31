<?php

namespace App\Repository;

use App\Entity\Participants;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Sortie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Sortie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function sortieFiltre($site)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.site = :site')
            ->setParameter(
                "site",$site
            )
            ->getQuery();
        return $query->getResult();
    }



    /**
     * Fonction permettant de traiter le formulaire de filtre du twig liste.html.twig
     * @param null $recherche_term Recherche les sorties par mot-clé
     * @param null $siteId Recherche les sorties par l'identifiant du site
     * @param null $etat Recherche les sorties par l'identifiant de l'état
     * @param null $date_debut Recherche les sorties dont la date de debut est supérieure à une date selectionnée
     * @param null $date_fin Recherche les sorties dont la date de fin est inférieurs à une date selectionnée
     * @param null $organisateur Recherche les sorties dont je suis l'organisateur.trice
     * @param null $inscrit Recherche les sorties auxquelles je suis inscrit.e
     * @param null $non_inscrit Recherche les sorties auxquelles je ne suis pas inscrit.e
     * @param null $passee Recherche les sorties passées
     * @return \Doctrine\ORM\Query
     * @throws \Exception
     */
    public function rechercheDetaillee($recherche_term = null, $siteId = null,$etat = null, $date_debut = null, $date_fin = null, $organisateur = null, $inscrit = null, $non_inscrit = null, $passee = null): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('sortie')
            ->join('sortie.site', 'site')
            ->join('sortie.organisateur', 'organisateur')
            ->join('sortie.etat' , 'etat')
            ->addSelect('site')
            ->addSelect('organisateur')
            ->addSelect('etat');

        if($recherche_term != null){
            $qb->andWhere('sortie.nom LIKE :recherche_term')
                ->setParameter('recherche_term', '%'.$recherche_term.'%');
        }
        if($siteId > 0){
            $qb->andWhere('site.id = :siteId')
                ->setParameter('siteId', $siteId);
        }
        if($etat > 0){
            $qb->andWhere('etat.id = :etat')
                ->setParameter('etat', $etat);
        }
        if($date_debut != null){
            $qb->andWhere('sortie.dateHeureDebut > :date_debut')
                ->setParameter('date_debut', new \DateTime($date_debut));
        }
        if($date_fin != null){
            $qb->andWhere('sortie.dateHeureDebut < :date_fin')
                ->setParameter('date_fin', new \DateTime($date_fin));
        }
        if($organisateur != null){
            $organisateur = $user = $this->getEntityManager()->getRepository(Participants::class)->find($organisateur);
            $qb->andWhere('sortie.organisateur = :organisateur')
                ->setParameter('organisateur', $organisateur);
        }
        if($inscrit != null){
            $user = $this->getEntityManager()->getRepository(Participants::class)->find($inscrit);
            $qb->andWhere(':inscrit MEMBER OF sortie.participants')
                ->setParameter('inscrit', $user);
        }
        if($non_inscrit != null){
            $user = $this->getEntityManager()->getRepository(Participants::class)->find($non_inscrit);
            $qb->andWhere(':inscrit NOT MEMBER OF sortie.participants')
                ->setParameter('inscrit', $user);
        }
        if($passee != null){
            $qb->andWhere('etat.libelle = :etat')
                ->setParameter('etat', 'Passée');
        }

        return $qb
            ->getQuery();
    }
}
