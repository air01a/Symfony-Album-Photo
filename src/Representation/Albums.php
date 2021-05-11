<?php

namespace App\Representation;

use Pagerfanta\Pagerfanta;
use JMS\Serializer\Annotation\Type;

class Albums
{
    /**
     * @Type("array<App\Entity\Album>")
     */
    public $data;

    public $meta;

    public function __construct(Pagerfanta $data)
    {

        $this->data = $data->getCurrentPageResults();

        $this->addMeta('limit', $data->getMaxPerPage());
        $this->addMeta('current_items', count($data->getCurrentPageResults()));
        $this->addMeta('total_items', $data->getNbResults());
        $this->addMeta('index', $data->getCurrentPageOffsetStart());
        $this->addMeta('total_pages', $data->getNbPages());
        
        if ($data->hasNextPage()) {
            $page = array("page"=>$data->getNextPage(),"limit"=>$data->getMaxPerPage());
            $nextPage=base64_encode(json_encode($page));
            $this->addMeta('next_page',$nextPage);

        }
        if ($data->hasPreviousPage()) {
            $page = array("page"=>$data->getPreviousPage(),"limit"=>$data->getMaxPerPage());
            $previousPage=base64_encode(json_encode($page));
            $this->addMeta('previous_page',$previousPage);
        }

    }

    public function addMeta($name, $value)
    {

        if (isset($this->meta[$name])) {

            throw new \LogicException(sprintf('This meta already exists. You are trying to override this meta, use the setMeta method instead for the %s meta.', $name));
        }

        $this->setMeta($name, $value);
    }

    public function setMeta($name, $value)
    {

        $this->meta[$name] = $value;

    }
    

}