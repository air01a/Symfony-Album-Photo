<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends EntityRepository
{
    protected function paginate(QueryBuilder $qb, $limit = 20, $page = "")
    {
        if (0 == $limit ) {
            throw new \LogicException('$limit & $offstet must be greater than 0.');
        }
        
        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage((int) $limit);
        $currentPage = json_decode(base64_decode($page),true);
        $page=0;
        if ($currentPage)
            $page = ceil($currentPage["page"]*$currentPage["limit"] / $limit);
        if ($page<1)
            $page=1;
        $pager->setCurrentPage($page);
        
        return $pager;
    }
}