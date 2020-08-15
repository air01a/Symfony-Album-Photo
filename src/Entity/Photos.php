<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photos
 *
 * @ORM\Table(name="photos", indexes={@ORM\Index(name="album_id", columns={"album_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\PhotoRepository")

 */
class Photos
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=false, options={"default"="''"})
     */
    private $path = '';

    /**
     * @var int
     *
     * @ORM\Column(name="album_id", type="integer", nullable=false)
     *      
     * @ORM\ManyToOne(targetEntity="App\Entity\Album")
     * @ORM\JoinColumn=(name="album_id", referencedColumnName"id")
     */
    
    private $albumId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", length=16777215, nullable=false)
     */
    private $commentaire;

    public function getAlbumId() {
        return $this->albumId;
    }

    public function setAlbumId($id) {
        $this->albumId=$id;
    }

    public function getPath() {
        return $this->path;
    }
    
    public function setPath($path) {
        $this->path=$path;
    }

    public function getComment() {
        return $this->commentaire;
    }
    
    public function setComment($comment) {
        $this->commentaire=$comment;
    }
}
