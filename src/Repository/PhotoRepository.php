<?php

namespace App\Repository;
use Doctrine\ORM\EntityRepository;

class PhotoRepository extends EntityRepository
{
    public function search($id)
    {
        $userId=0;

        $qb = $this->_em->createQueryBuilder()
                         ->select('p')
                         ->from('App:Photos', 'p')
                         ->where('p.albumId = ?1')
                         ->setParameter(1,$id)
                         ->orderBy('p.path','asc');   
        return $qb->getQuery()->getResult();
    }


    public function getRandomPhoto($id)
    {
        $userId=0;

        $qb = $this->_em->createQueryBuilder()
                         ->select('p')
                         ->from('App:Photos', 'p')
                         ->where('p.albumId = ?1')
                         ->setParameter(1,$id)
                         ->orderBy('RAND()')
                         ->setMaxResults( 1 );;


        return $qb->getQuery()->getSingleResult();
    }
}