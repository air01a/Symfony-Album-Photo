<?php

namespace App\Repository;
use Doctrine\ORM\Query;

class AlbumRepository extends AbstractRepository
{
    public function search($term, $order = 'desc', $limit = 20, $page = 0, int $userId, int $admin=0)
    {
        
        $qb = $this->_em->createQueryBuilder()
                         ->select('a')
                         ->from('App:Album', 'a')
                         ->join('a.rights','r');
        if (!$admin) {
            $qb
                         ->where('r.user = ?1')
                         ->setParameter(1,$userId)
                        ;
        }

        if ($term) {
            $qb
                ->where('a.name LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
            ;
        }

        $qb
            ->orderBy('a.pinned', 'DESC')
            ->addOrderBy('a.date', $order);

        $qb->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
        return $this->paginate($qb, $limit, $page);
    }


    public function getCountry(int $userId)
    {
        
        $qb = $this->_em->createQueryBuilder()
                         ->select('a.country,count(a.id) as count')
                         ->from('App:Album', 'a')
                         ->join('a.rights','r');

        $qb
                         ->where('r.user = ?1')
                         ->setParameter(1,$userId)
                         ->GroupBy('a.country');
    
        return $qb->getQuery()->getArrayResult();
    }

    public function getAlbumFromCountry($country, int $userId)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('a.id,a.name,a.date')
            ->from('App:Album', 'a')
            ->where('a.country = :country')
            ->join('a.rights','r')
            ->andWhere('r.user = :userId')
            ->setParameter('country',$country)
            ->setParameter('userId',$userId);


        return $qb->getQuery()->getResult();
    }
}