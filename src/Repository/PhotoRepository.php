<?php

namespace App\Repository;
use Doctrine\ORM\EntityRepository;

class PhotoRepository extends EntityRepository
{

    public function getOrder($id,$value,$orderBy){
        switch($orderBy) {
            case 'path':
                $field='p.path';
                $direction='ASC';
                $equality='>';
                break;
            case 'date_time':
                $field="p.dateTime";
                $direction='ASC';
                $equality='>';
                break;
            case 'path_desc':
                $field='p.path';
                $direction='DESC';
                $equality='<';
                break;
            case 'date_time_desc':
                $field="p.dateTime";
                $direction='DESC';
                $equality='<';
                break;
            default:
                return NULL;
        }

        $qb = $this->_em->createQueryBuilder()
                ->select('p')
                ->from('App:Photos','p')
                ->where('p.albumId = :id')
                ->andWhere($field.' '.$equality.' :value')
                ->addOrderBy($field, $direction)
                ->setMaxResults( 1 )
                ->setParameter(':id',$id)
                ->setParameter(':value',$value);
        
        $result=$qb->getQuery()->getOneOrNullResult();
        if ($result)
            return $result->getOrderInAlbum();
        return $result;

    }

    public function resetOrder($album){
        $query = $this->_em->createQuery(
            "UPDATE App:Photos p
             SET p.orderInAlbum = 1000
             WHERE p.albumId=$album"
        );

        $query->execute();
    }

    public function updateOrder($id,$value){

        $query = $this->_em->createQuery(
            "UPDATE App:Photos p
             SET p.orderInAlbum = p.orderInAlbum + 1
             WHERE p.albumId=$id AND p.orderInAlbum >= $value AND p.orderInAlbum!=1000"
        );

        $query->execute();


    }

    public function isPhotoUniq($album,$photo) {
        $qb = $this->_em->createQueryBuilder()
                         ->select('count(p.id)')
                         ->from('App:Photos', 'p')
                         ->where('p.albumId = ?1')
                         ->setParameter(1,$album->getId())
                         ->andWhere('p.path = ?2')
                         ->setParameter(2,$photo->getPath());
        
        return $qb->getQuery()->getSingleScalarResult();
    }


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

    public function getAlbumCount()
    {


        return $this->_em->createQueryBuilder('p')
                        ->select('COUNT(p.id)')
                        ->from('App:Photos', 'p')
                        ->getQuery()
                        ->getSingleScalarResult();
    }
}