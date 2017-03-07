<?php

namespace YahtzeeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use YahtzeeBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Game
 *
 * @ORM\Table(name="games")
 * @ORM\Entity(repositoryClass="YahtzeeBundle\Repository\GameRepository")
 * @UniqueEntity(fields = "name", message = "Ce nom existe déjà")
 */
class Game
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     * @Assert\NotBlank(message = "Le nom de la partie doit être rempli et compris entre 3 et 20 caractères")
     * @Assert\Length(
     *      min = 3,
     *      max = 20,
     *      minMessage = "Le nom de la partie doit être rempli et compris entre 3 et 20 caractères",
     *      maxMessage = "Le nom de la partie doit être rempli et compris entre 3 et 20 caractères"
     * )
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="game_date", type="datetime")
     */
    private $gameDate;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner", referencedColumnName="id", nullable=true)
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="winner", referencedColumnName="id", nullable=true)
     */
    private $winner;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="player_turn", referencedColumnName="id", nullable=true)
     */
    private $playerTurn;

    /**
     * @ORM\OneToMany(targetEntity="Sheet", mappedBy="game", cascade={"persist"})
     */
    private $sheets;


    public function __construct() {
        $this->gameDate = new \DateTime();
        $this->sheets = new ArrayCollection();
    }

    // Test si l'utilisateur est présent à la partie
    public function containUser($userId) {
        foreach ($this->getSheets() as $sheet) {
            if ($sheet->getPlayer()->getId() == $userId) {
                return true;
            }
        }

        return false;
    }

    // Retourne la liste des players sortés par leur no de tour
    public function getPlayers() {
        $players = array();

        foreach ($this->getSheets() as $sheet) {
            $players[] = $sheet->getPlayer();
        }

        return $players;
    }

    // Retourne le nombre de players
    public function getNbPlayers() {
        return count($this->getPlayers());
    }

    // Retourne les feuilles triées par leur numéro de tour
    public function getSheetsSortedByTurn() {
        $sheets = $this->getSheets()->getValues();

        usort($sheets, array("\\YahtzeeBundle\\Entity\\Sheet", "sheetsComparaisonByTurn"));

        return $this->sheets;
    }

    // Définit quel joueur jouera au prochain tour
    public function setNextPlayerTurn() {
        $nextNoTurn = 0;
        $userSheet = $this->getSheetByUserId($this->getPlayerTurn()->getId());

        if ($userSheet == null) {
            return ;
        }

        $currentNoTurn = $userSheet->getPlayerOrder();

        // Incrémente le numéro de tour
        if ($currentNoTurn + 1 > $this->getNbPlayers()) {
            $nextNoTurn = 1;
        } else {
            $nextNoTurn = $currentNoTurn + 1;
        }

        $playerTurn = $this->getUserByNoTurn($nextNoTurn);

        $this->setPlayerTurn($playerTurn);
    }

    // Retourne la feuille qui correspond au user id
    public function getSheetByUserId($userId) {
        foreach ($this->sheets as $sheet) {
            if ($sheet->getPlayer()->getId() == $userId) {
                return $sheet;
            }
        }

        return null;
    }

    // Retourne le user qui correspond au numéro de tour
    private function getUserByNoTurn($noTurn) {
        foreach ($this->sheets as $sheet) {
            if ($sheet->getPlayerOrder() == $noTurn) {
                return $sheet->getPlayer();
            }
        }

        return null;
    }

    // Test si les feuilles de la partie sont toutes complètement remplies
    public function isCompletelyFilled() {
    	$isItFilled = true;

    	foreach ($this->getSheets() as $sheet) {
    		if (! $sheet->isCompletelyFilled()) {
    			$isItFilled = false;
    		}
    	}

    	return $isItFilled;
    }

    // Inscrit le score total pour chaque feuilles
    public function saveTotalScoreForEachSheets() {
    	foreach ($this->getSheets() as $sheet) {
    		$sheet->setTotalScore($sheet->calculateTotalScore());
    	}
    }

    // Détermine qui est le gagnant
    public function whoIsTheWinner() {
    	$bestSheet = null;

    	foreach ($this->getSheets() as $sheet) {
    		if ($bestSheet == null) {
    			$bestSheet = $sheet;
    			continue;
    		}

    		if ($bestSheet->getTotalScore() <= $sheet->getTotalScore()) {
    			$bestSheet = $sheet;
    		}
    	}

    	return $bestSheet->getPlayer();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set gameDate
     *
     * @param \DateTime $gameDate
     * @return Game
     */
    public function setGameDate($gameDate)
    {
        $this->gameDate = $gameDate;

        return $this;
    }

    /**
     * Get gameDate
     *
     * @return \DateTime 
     */
    public function getGameDate()
    {
        return $this->gameDate;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Game
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set winner
     *
     * @param \stdClass $winner
     * @return Game
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * Get winner
     *
     * @return \stdClass 
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * Retourne les feuilles de manière ordrées par leur numéro de tour
     *
     * @return mixed
     */
    public function getSheets()
    {
        return $this->sheets;
    }

    public function addSheet(Sheet $sheet) {
        $this->sheets[] = $sheet;
        $sheet->setGame($this);

        return $this;
    }

    public function removeSheet(Sheet $sheet) {
        $this->sheets->removeElement($sheet);

        return $this;
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Game
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set owner
     *
     * @param \UserBundle\Entity\User $owner
     *
     * @return Game
     */
    public function setOwner(\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set playerTurn
     *
     * @param \UserBundle\Entity\User $playerTurn
     *
     * @return Game
     */
    public function setPlayerTurn(\UserBundle\Entity\User $playerTurn = null)
    {
        $this->playerTurn = $playerTurn;

        return $this;
    }

    /**
     * Get playerTurn
     *
     * @return \UserBundle\Entity\User
     */
    public function getPlayerTurn()
    {
        return $this->playerTurn;
    }
}
