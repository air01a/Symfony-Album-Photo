<?php

namespace App\Repository;
use Doctrine\ORM\EntityRepository;

class PhotoRepository extends EntityRepository
{
    public function search($id,$orderBy='path')
    {
        $userId=0;

        $qb = $this->_em->createQueryBuilder()
                         ->select('p')
                         ->from('App:Photos', 'p')
                         ->where('p.albumId = ?1')
                         ->setParameter(1,$id)
                         ->addOrderBy('p.orderInAlbum', 'ASC');

        
        
        switch($orderBy) {
            case 'date_time':
                $qb->addOrderBy('p.dateTime','ASC');
                break;
            case 'date_time_desc':
                $qb->addOrderBy('p.dateTime','DESC');
                break;
            case 'path_desc':
                $qb->addOrderBy('p.dateTime','DESC');
                break;
            default:
                $qb->addOrderBy('p.path', 'ASC'); 
        }
       
              
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

        
        return $qb->getQuery()->getOneOrNullResult();
    }
}