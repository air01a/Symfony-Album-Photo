<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Right
 *
 * @ORM\Table(name="user_right", indexes={@ORM\Index(name="user_id", columns={"user_id"}), @ORM\Index(name="album_id", columns={"album_id"})})
 * @ORM\Entity
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
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId = '0';

   

    /**
     * @var album
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\Album", inversedBy="rights")
     * @ORM\JoinColumn=(name="album_id", referencedColumnName"id")
     */
    private $album;
    public function getUserId() {
        return $this->userId;
    }

}
