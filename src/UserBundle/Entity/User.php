<?php

namespace UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use YahtzeeBundle\Entity\Sheet;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="last_contact", type="datetime", nullable=true)
     */
    private $lastContact;

    /**
     * @ORM\OneToMany(targetEntity="YahtzeeBundle\Entity\Sheet", mappedBy="player")
     */
    private $sheets;

    public function __construct() {
        parent::__construct ();

        $this->sheets = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getLastContact()
    {
        return $this->lastContact;
    }

    /**
     * @param mixed $lastContact
     */
    public function setLastContact($lastContact)
    {
        $this->lastContact = $lastContact;
    }

    public function getSheets() {
        return $this->sheets;
    }

    public function addSheet(Sheet $sheet) {
        $this->sheets[] = $sheet;
        $sheet->setPlayer($this);

        return $this;
    }

    public function removeSheet(Sheet $sheet) {
        $this->sheets->removeElement($sheet);

        return $this;
    }

}