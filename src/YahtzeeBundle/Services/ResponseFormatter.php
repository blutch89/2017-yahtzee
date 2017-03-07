<?php

namespace YahtzeeBundle\Services;

use UserBundle\Entity\User;
use YahtzeeBundle\Entity\Game;
use YahtzeeBundle\Repository\GameRepository;

class ResponseFormatter {

    private $gameRepository;
    private $lastContact;

    public function __construct(GameRepository $gameRepository, LastContact $lastContact) {
        $this->gameRepository = $gameRepository;
        $this->lastContact = $lastContact;
    }

    public function formatGetGames($inProgressIds, $nonStartedIds, $closedIds) {
        $arr = new \ArrayObject();

        $arr["inProgress"] = $this->createGamesArray($inProgressIds);
        $arr["nonStarted"] = $this->createGamesArray($nonStartedIds);
        $arr["closed"] = $this->createGamesArray($closedIds);

        return $arr;
    }

    private function createGamesArray($ids) {
        $arr = new \ArrayObject();

        foreach ($ids as $id) {
            $game = $this->gameRepository->getGame($id["id"]);

            $arr[] = $this->createGameArray($game);
        }

        return $arr;
    }

    private function createGameArray($game) {
        $arr = new \ArrayObject();

        $arr["id"] = $game->getId();
        $arr["name"] = $game->getName();
        $arr["date"] = $game->getGameDate();
        $arr["status"] = $game->getStatus();
        $arr["owner"] = ($game->getOwner() != null) ? $game->getOwner()->getId() : 0;
        $arr["player_turn"] = ($game->getPlayerTurn() != null) ? $game->getPlayerTurn()->getId() : 0;
        $arr["winner"] = ($game->getWinner() != null) ? $game->getWinner()->getId() : 0;

        $playersArr = new \ArrayObject();

        foreach ($game->getSheets() as $index2 => $sheet) {
            $playersArr[$index2]["id"] = $sheet->getPlayer()->getId();
            $playersArr[$index2]["username"] = $sheet->getPlayer()->getUsername();
            $playersArr[$index2]["is_online"] = !$this->lastContact->isDisconnected($sheet->getPlayer());
            $playersArr[$index2]["score"] = $sheet->getTotalScore();
        }

        $arr["players"] = $playersArr;

        return $arr;
    }

    public function formatGetGame(Game $game) {
        $arr = new \ArrayObject();

        if ($game->getPlayerTurn() != null) {
            $arr["player-turn"] = $this->createPlayerTurn($game);
        }

        if ($game->getStatus() == 2 && $game->getWinner() != null) {
        	$arr["winner"] = $this->createWinner($game->getWinner());
        }

        $arr["players"] = $this->createPlayersScore($game);
        $arr["is-ended"] = ($game->getStatus() == 2) ? true : false;
        $arr["success"] = true;

        return $arr;
    }

    private function createPlayersScore(Game $game) {
        $arr = new \ArrayObject();
        $sheets = $game->getSheetsSortedByTurn();

        foreach ($sheets as $sheet) {
            $player = $sheet->getPlayer();
            $playerArr = new \ArrayObject();
            $scoreArr = new \ArrayObject();

            // Données du joueur
            $playerArr["id"] = $player->getId();
            $playerArr["username"] = $player->getUsername();
            $playerArr["is_online"] = !$this->lastContact->isDisconnected($sheet->getPlayer());

            // Données de score
            $scoreArr["ones"] = $sheet->getOnes();
            $scoreArr["twos"] = $sheet->getTwos();
            $scoreArr["threes"] = $sheet->getThrees();
            $scoreArr["fours"] = $sheet->getFours();
            $scoreArr["fives"] = $sheet->getFives();
            $scoreArr["sixes"] = $sheet->getSixes();
            $scoreArr["sum"] = $sheet->calculateSum();
            $scoreArr["bonus"] = $sheet->calculateBonus();
            $scoreArr["one-pair"] = $sheet->getOnePair();
            $scoreArr["two-pairs"] = $sheet->getTwoPairs();
            $scoreArr["three-of-a-kind"] = $sheet->getThreeOfAKind();
            $scoreArr["four-of-a-kind"] = $sheet->getFourOfAKind();
            $scoreArr["full-house"] = $sheet->getFullHouse();
            $scoreArr["small-straight"] = $sheet->getSmallStraight();
            $scoreArr["large-straight"] = $sheet->getLargeStraight();
            $scoreArr["chance"] = $sheet->getChance();
            $scoreArr["yahtzee"] = $sheet->getYahtzee();

            // Si la partie est terminée et donc que le score est déjà inscrit dans la bdd, renvoie le score total inscrit. Sinon, renvoie le calcul du score actuel
            $scoreArr["total-score"] = ($sheet->getTotalScore() != null) ? $sheet->getTotalScore() : $sheet->calculateTotalScore();

            $playerArr["score"] = $scoreArr;

            $arr[] = $playerArr;
        }

        return $arr;
    }

    private function createPlayerTurn(Game $game) {
        $arr = new \ArrayObject();

        $arr["id"] = $game->getPlayerTurn()->getId();
        $arr["username"] = $game->getPlayerTurn()->getUsername();

        return $arr;
    }

    private function createWinner(User $winner) {
    	$arr = new \ArrayObject();

        $arr["id"] = $winner->getId();
        $arr["username"] = $winner->getUsername();

        return $arr;
    }

}