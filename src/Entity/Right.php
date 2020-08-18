<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Right
 *
 * @ORM\Table(name="user_right", indexes={@ORM\Index(name="album_id", columns={"album_id"})})
 * @ORM\Entity
* @ORM\Entity(repositoryClass="App\Repository\RightRepository")

 */
class Right
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
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn=(name="user_id", referencedColumnName"id")
     */
    private $user;

   

    /**
     * @var album
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\Album",inversedBy="rights")
     * @ORM\JoinColumn=(name="album_id", referencedColumnName"id")
     */
    private $album;

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        return $this->user = $user;

    }

    public function getAlbum() {
        return $this->album;
    }

    public function getId() {
        return $this->id;
    }

    public function setAlbum($album) {
        return $this->album=$album;
    }

}
