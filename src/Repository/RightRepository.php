<?php

namespace App\Repository;
use Doctrine\ORM\EntityRepository;

class RightRepository extends EntityRepository
{
    public function search($id)
    {
        $qb = $this->_em->createQueryBuilder()
                         ->select('r')
                         ->from('App:Right', 'r')
                         ->where('r.album = ?1')
                         ->setParameter(1,$id);


        return $qb->getQuery()->getResult();
    }

    public function delete($id) {
        $qb = $this->_em->createQuery('delete from App:Right r where r.album = ?1');
        
        $qb->setParameter(1,$id);

    }
    public function add($idAlbum,$idUser) {
        $qb = $this->_em->createQuery('INSERT INTO App:Right r where r.album = ?1');
        $qb->setParameter(1,$idUser);
    }
}