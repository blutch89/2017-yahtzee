<?php

namespace YahtzeeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use YahtzeeBundle\Entity\Game;
use YahtzeeBundle\Entity\Sheet;

class GameController extends Controller
{
	public function getGameAction(Game $game) {
		$lastContact = $this->get("yahtzee.users.last_contact");
		$responseFormatter = $this->get("yahtzee.response_formatter");

		$lastContact->setLastContact();

		$response = $responseFormatter->formatGetGame($game);

		return new JsonResponse($response);
	}

	public function selectCombinationAction($gameId, $userId, $entry, $value) {
		$em = $this->getDoctrine()->getManager();
		$gameRepository = $this->getDoctrine()->getRepository("YahtzeeBundle:Game");
		$game = $gameRepository->find($gameId);

		// Test si la partie existe
		if ($game == null) {
			return $this->sendErrorMessage("Cette partie n'existe pas");
		}

		$sheet = $game->getSheetByUserId($userId);

		// Test si la feuille de l'utilisateur connecté existe
		if ($sheet == null) {
			return $this->sendErrorMessage("Feuille du joueur introuvable");
		}

		// Test si la combinaison n'a pas déjà été inscrite
		if ($sheet->getEntryFromOtherSyntax($entry) != null) {
			return $this->sendErrorMessage("Cette combinaison a déjà été inscrite");
		}

		// Inscrit le combinaison
		$sheet->setEntryFromOtherSyntax($entry, $value);
		$game->setNextPlayerTurn();

		// Si la partie est terminée à l'instant
		if ($game->isCompletelyFilled()) {
			$game->setStatus(2);
			$game->saveTotalScoreForEachSheets();
			$game->setWinner($game->whoIsTheWinner());
			$game->setPlayerTurn(null);
		}

		// Enregistrement de la partie
        try {
            $em->flush();

            return new JsonResponse([
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return $this->sendErrorMessage("Une erreur inconnue s'est produite");
        }
	}

	public function finishGameAction($gameId) {
		$em = $this->getDoctrine()->getManager();
		$gameRepository = $this->getDoctrine()->getRepository("YahtzeeBundle:Game");
		$game = $gameRepository->find($gameId);
		$user = $this->get('security.token_storage')->getToken()->getUser();

		// Test si la partie existe
		if ($game == null) {
			return $this->sendErrorMessage("Cette partie n'existe pas");
		}

		// Test si l'utilisateur connecté fait partie de la partie
		if (! $game->containUser($user->getId())) {
			return $this->sendErrorMessage("Vous ne faites pas partie de cette partie");
		}

		$game->setStatus(2);
		$game->saveTotalScoreForEachSheets();
		$game->setPlayerTurn(null);

		// Enregistrement de la partie
        try {
            $em->flush();

            return new JsonResponse([
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return $this->sendErrorMessage("Une erreur inconnue s'est produite");
        }
	}

	private function sendErrorMessage($errorMessage) {
		return new JsonResponse([
			'success' => false,
			'error' => $errorMessage
			], 500);
	}
}
