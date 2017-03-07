<?php

namespace YahtzeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sheet
 *
 * @ORM\Table(name="sheets")
 * @ORM\Entity(repositoryClass="YahtzeeBundle\Repository\SheetRepository")
 */
class Sheet
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
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="sheets")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", nullable=false)
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="sheets")
     * @ORM\JoinColumn(name="player_id", referencedColumnName="id", nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(name="player_order", type="smallint", nullable=false)
     */
    private $playerOrder;

    /**
     * @var int
     *
     * @ORM\Column(name="ones", type="smallint", nullable=true)
     */
    private $ones;

    /**
     * @var int
     *
     * @ORM\Column(name="twos", type="smallint", nullable=true)
     */
    private $twos;

    /**
     * @var int
     *
     * @ORM\Column(name="threes", type="smallint", nullable=true)
     */
    private $threes;

    /**
     * @var int
     *
     * @ORM\Column(name="fours", type="smallint", nullable=true)
     */
    private $fours;

    /**
     * @var int
     *
     * @ORM\Column(name="fives", type="smallint", nullable=true)
     */
    private $fives;

    /**
     * @var int
     *
     * @ORM\Column(name="sixes", type="smallint", nullable=true)
     */
    private $sixes;

    /**
     * @var int
     *
     * @ORM\Column(name="one_pair", type="smallint", nullable=true)
     */
    private $onePair;

    /**
     * @var int
     *
     * @ORM\Column(name="two_pairs", type="smallint", nullable=true)
     */
    private $twoPairs;

    /**
     * @var int
     *
     * @ORM\Column(name="three_of_a_kind", type="smallint", nullable=true)
     */
    private $threeOfAKind;

    /**
     * @var int
     *
     * @ORM\Column(name="four_of_a_kind", type="smallint", nullable=true)
     */
    private $fourOfAKind;

    /**
     * @var int
     *
     * @ORM\Column(name="full_house", type="smallint", nullable=true)
     */
    private $fullHouse;

    /**
     * @var int
     *
     * @ORM\Column(name="small_straight", type="smallint", nullable=true)
     */
    private $smallStraight;

    /**
     * @var int
     *
     * @ORM\Column(name="large_straight", type="smallint", nullable=true)
     */
    private $largeStraight;

    /**
     * @var int
     *
     * @ORM\Column(name="chance", type="smallint", nullable=true)
     */
    private $chance;

    /**
     * @var int
     *
     * @ORM\Column(name="yahtzee", type="smallint", nullable=true)
     */
    private $yahtzee;

    /**
     * @var int
     *
     * @ORM\Column(name="total_score", type="smallint", nullable=true)
     */
    private $totalScore;

    // Permet de comparer 2 feuilles en fonction de leur numéro de tour
    public static function sheetsComparaisonByTurn($s1, $s2) {
        if ($s1->getId() == $s2->getId()) {
            return 0;
        }

        return ($s1->getPlayerOrder() < $s2->getPlayerOrder()) ? -1 : 1;
    }

    // Permet de calculer le score de la première section
    public function calculateSum() {
        $score = $this->ones;
        $score += $this->twos;
        $score += $this->threes;
        $score += $this->fours;
        $score += $this->fives;
        $score += $this->sixes;

        return $score;
    }

    // Retourne le bonus que l'utilisateur a droit
    public function calculateBonus() {
        if ($this->calculateSum() >= 63) {
            return 35;
        }

        return 0;
    }

    // Permet de calculer le score total
    public function calculateTotalScore() {
        $score = $this->calculateSum();
        $score += $this->calculateBonus();
        $score += $this->onePair;
        $score += $this->twoPairs;
        $score += $this->threeOfAKind;
        $score += $this->fourOfAKind;
        $score += $this->fullHouse;
        $score += $this->smallStraight;
        $score += $this->largeStraight;
        $score += $this->chance;
        $score += $this->yahtzee;

        return $score;
    }

    // Exemple: si on fournit "one-pair" comme paramètre, la méthode nous retournera "onePair"
    private function convertEntryName($entry) {
        $nbHyphen = substr_count($entry, "-");

        for ($i = 0; $i < $nbHyphen; $i++) {
            $position = strpos($entry, "-");
            $character = substr($entry, $position + 1);
            $entry[$position + 1] = strtoupper($character);
            $entry = substr($entry, 0, $position) . substr($entry, $position + 1, strlen($entry));
        }

        return $entry;
    }

    // Appelle un getter avec un paramètre du genre "one-pair" au lieu de "onePair"
    public function getEntryFromOtherSyntax($entry) {
        $convertedEntry = $this->convertEntryName($entry);

        return $this->$convertedEntry;
    }


    public function setEntryFromOtherSyntax($entry, $value) {
        $convertedEntry = $this->convertEntryName($entry);

        $this->$convertedEntry = $value;
    }

    // Test si cette feuille est complètement remplie
    public function isCompletelyFilled() {
    	if ($this->ones === null
    		|| $this->twos === null
    		|| $this->threes === null
    		|| $this->fours === null
    		|| $this->fives === null
    		|| $this->sixes === null
    		|| $this->onePair === null
    		|| $this->twoPairs === null
    		|| $this->threeOfAKind === null
    		|| $this->fourOfAKind === null
    		|| $this->fullHouse === null
    		|| $this->smallStraight === null
    		|| $this->largeStraight === null
    		|| $this->chance === null
    		|| $this->yahtzee === null) {

    		return false;
    	}

    	return true;
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
     * Set ones
     *
     * @param integer $ones
     * @return Sheet
     */
    public function setOnes($ones)
    {
        $this->ones = $ones;

        return $this;
    }

    /**
     * Get ones
     *
     * @return integer 
     */
    public function getOnes()
    {
        return $this->ones;
    }

    /**
     * Set twos
     *
     * @param integer $twos
     * @return Sheet
     */
    public function setTwos($twos)
    {
        $this->twos = $twos;

        return $this;
    }

    /**
     * Get twos
     *
     * @return integer 
     */
    public function getTwos()
    {
        return $this->twos;
    }

    /**
     * Set threes
     *
     * @param integer $threes
     * @return Sheet
     */
    public function setThrees($threes)
    {
        $this->threes = $threes;

        return $this;
    }

    /**
     * Get threes
     *
     * @return integer 
     */
    public function getThrees()
    {
        return $this->threes;
    }

    /**
     * Set fours
     *
     * @param integer $fours
     * @return Sheet
     */
    public function setFours($fours)
    {
        $this->fours = $fours;

        return $this;
    }

    /**
     * Get fours
     *
     * @return integer 
     */
    public function getFours()
    {
        return $this->fours;
    }

    /**
     * Set fives
     *
     * @param integer $fives
     * @return Sheet
     */
    public function setFives($fives)
    {
        $this->fives = $fives;

        return $this;
    }

    /**
     * Get fives
     *
     * @return integer 
     */
    public function getFives()
    {
        return $this->fives;
    }

    /**
     * Set sixes
     *
     * @param integer $sixes
     * @return Sheet
     */
    public function setSixes($sixes)
    {
        $this->sixes = $sixes;

        return $this;
    }

    /**
     * Get sixes
     *
     * @return integer 
     */
    public function getSixes()
    {
        return $this->sixes;
    }

    /**
     * Set onePair
     *
     * @param integer $onePair
     * @return Sheet
     */
    public function setOnePair($onePair)
    {
        $this->onePair = $onePair;

        return $this;
    }

    /**
     * Get onePair
     *
     * @return integer 
     */
    public function getOnePair()
    {
        return $this->onePair;
    }

    /**
     * Set twoPairs
     *
     * @param integer $twoPairs
     * @return Sheet
     */
    public function setTwoPairs($twoPairs)
    {
        $this->twoPairs = $twoPairs;

        return $this;
    }

    /**
     * Get twoPairs
     *
     * @return integer 
     */
    public function getTwoPairs()
    {
        return $this->twoPairs;
    }

    /**
     * Set threeOfAKind
     *
     * @param integer $threeOfAKind
     * @return Sheet
     */
    public function setThreeOfAKind($threeOfAKind)
    {
        $this->threeOfAKind = $threeOfAKind;

        return $this;
    }

    /**
     * Get threeOfAKind
     *
     * @return integer 
     */
    public function getThreeOfAKind()
    {
        return $this->threeOfAKind;
    }

    /**
     * Set fourOfAKind
     *
     * @param integer $fourOfAKind
     * @return Sheet
     */
    public function setFourOfAKind($fourOfAKind)
    {
        $this->fourOfAKind = $fourOfAKind;

        return $this;
    }

    /**
     * Get fourOfAKind
     *
     * @return integer 
     */
    public function getFourOfAKind()
    {
        return $this->fourOfAKind;
    }

    /**
     * Set fullHouse
     *
     * @param integer $fullHouse
     * @return Sheet
     */
    public function setFullHouse($fullHouse)
    {
        $this->fullHouse = $fullHouse;

        return $this;
    }

    /**
     * Get fullHouse
     *
     * @return integer 
     */
    public function getFullHouse()
    {
        return $this->fullHouse;
    }

    /**
     * Set smallStraight
     *
     * @param integer $smallStraight
     * @return Sheet
     */
    public function setSmallStraight($smallStraight)
    {
        $this->smallStraight = $smallStraight;

        return $this;
    }

    /**
     * Get smallStraight
     *
     * @return integer 
     */
    public function getSmallStraight()
    {
        return $this->smallStraight;
    }

    /**
     * Set largeStraight
     *
     * @param integer $largeStraight
     * @return Sheet
     */
    public function setLargeStraight($largeStraight)
    {
        $this->largeStraight = $largeStraight;

        return $this;
    }

    /**
     * Get largeStraight
     *
     * @return integer 
     */
    public function getLargeStraight()
    {
        return $this->largeStraight;
    }

    /**
     * Set chance
     *
     * @param integer $chance
     * @return Sheet
     */
    public function setChance($chance)
    {
        $this->chance = $chance;

        return $this;
    }

    /**
     * Get chance
     *
     * @return integer 
     */
    public function getChance()
    {
        return $this->chance;
    }

    /**
     * Set yahtzee
     *
     * @param integer $yahtzee
     * @return Sheet
     */
    public function setYahtzee($yahtzee)
    {
        $this->yahtzee = $yahtzee;

        return $this;
    }

    /**
     * Get yahtzee
     *
     * @return integer 
     */
    public function getYahtzee()
    {
        return $this->yahtzee;
    }

    /**
     * Set totalScore
     *
     * @param integer $totalScore
     * @return Sheet
     */
    public function setTotalScore($totalScore)
    {
        $this->totalScore = $totalScore;

        return $this;
    }

    /**
     * Get totalScore
     *
     * @return integer 
     */
    public function getTotalScore()
    {
        return $this->totalScore;
    }

    /**
     * @return mixed
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param mixed $game
     */
    public function setGame($game)
    {
        $this->game = $game;
    }

    /**
     * @return mixed
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param mixed $player
     */
    public function setPlayer($player)
    {
        $this->player = $player;
    }

    /**
     * @return mixed
     */
    public function getPlayerOrder()
    {
        return $this->playerOrder;
    }

    /**
     * @param mixed $playerOrder
     */
    public function setPlayerOrder($playerOrder)
    {
        $this->playerOrder = $playerOrder;
    }
}
