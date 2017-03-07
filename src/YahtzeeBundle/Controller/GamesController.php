<?php

namespace YahtzeeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use YahtzeeBundle\Entity\Game;
use YahtzeeBundle\Entity\Sheet;

class GamesController extends Controller
{
    public function getGamesAction() {
        $lastContact = $this->get("yahtzee.users.last_contact");
        $responseFormatter = $this->get("yahtzee.response_formatter");
        $gamesRepository = $this->getDoctrine()
            ->getManager()
            ->getRepository("YahtzeeBundle:Game");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $lastContact->setLastContact();

        $inProgressIds = $gamesRepository->getInProgressGames($user->getId());
        $nonStartedIds = $gamesRepository->getNonStartedGames();
        $closedIds = $gamesRepository->getClosedGames($user->getId());

        $games = $responseFormatter->formatGetGames($inProgressIds, $nonStartedIds, $closedIds);

        return new JsonResponse($games);
    }

    public function createGameAction(Request $request) {
        $userManager = $this->container->get('fos_user.user_manager');
        $validator = $this->get('validator');
        $em = $this->getDoctrine()->getManager();

        $name = $request->request->get("name");
        $userId = $request->request->get("user-id");
        $owner = $userManager->findUserBy(array('id' => $userId));

        // Création de la partie
        $game = new Game();
        $game->setName($name);
        $game->setGameDate(new \DateTime());
        $game->setOwner($owner);
        $game->setPlayerTurn($owner);
        $game->setStatus(0);

        $sheet = new Sheet();
        $sheet->setPlayer($owner);
        $sheet->setPlayerOrder(1);
        $game->addSheet($sheet);

        // Validations
        $validationErrors = $validator->validate($game);

        if (count($validationErrors) > 0) {     // S'il y a des erreurs de validations
            return $this->sendErrorMessage($validationErrors[0]->getMessage());
        }

        // Persist la partie
        try {
            $em->persist($game);
            $em->persist($sheet);
            $em->flush();

            return new JsonResponse([
                'success' => true
            ], 200);
        } catch(UniqueConstraintViolationException $e){     // S'il existe déjà
            return $this->sendErrorMessage("Ce nom existe déjà.");
        } catch (\Exception $e) {                           // Toute autre exception
            return $this->sendErrorMessage("Une erreur inconnue s'est produite lors de la création de la partie.");
        }
    }

    public function registerToGameAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $gameRepository = $em->getRepository("YahtzeeBundle:Game");

        $gameId = $request->request->get("game-id");
        $userId = $request->request->get("user-id");

        $user = $this->get('security.context')->getToken()->getUser();
        $game = $gameRepository->find($gameId);

		// Test si la partie existe
        if ($game == null) {
        	return $this->sendErrorMessage("Cette partie n'existe pas");
        }

        // Test si l'utilisateur en paramètre est bien l'utilisateur en cours
        if ($user->getId() != $userId) {
            return $this->sendErrorMessage("Une erreur inconnue s'est produite");
        }

        // Test si le jeu en cours est bien en status 0
        if ($game->getStatus() != 0) {
            return $this->sendErrorMessage("Cette partie est déjà commencée");
        }

        // Test si l'utilisateur n'est pas déjà inscrit à cette partie
        if ($game->containUser($user->getId())) {
            return $this->sendErrorMessage("Vous êtes déjà inscrit à cette partie");
        }
        
        // Test si le nombre maximum de joueurs est atteint
        if (count($game->getPlayers()) >= 6) {
            return $this->sendErrorMessage("Le nombre maximum de joueurs est atteint");
        }

        // Inscrit le user
        $sheet = new Sheet();
        $sheet->setPlayer($user);
        $sheet->setPlayerOrder($game->getNbPlayers() + 1);
        $game->addSheet($sheet);

        try {
            $em->persist($game);
            $em->persist($sheet);

            $em->flush();

            return new JsonResponse([
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            $this->sendErrorMessage("Une erreur inconnue s'est produite");
        }
    }

    public function beginGameAction(Request $request) {
    	$em = $this->getDoctrine()->getManager();
        $gameRepository = $em->getRepository("YahtzeeBundle:Game");

        $gameId = $request->request->get("game-id");
        $userId = $request->request->get("user-id");
        
        $user = $this->get('security.context')->getToken()->getUser();
        $game = $gameRepository->find($gameId);

        // Test si la partie existe
        if ($game == null) {
        	return $this->sendErrorMessage("Cette partie n'existe pas");
        }

        // Test si l'utilisateur en paramètre est bien l'utilisateur en cours
        if ($user->getId() != $userId) {
            return $this->sendErrorMessage("Une erreur inconnue s'est produite");
        }

        // Test si le jeu en cours est bien en status 0
        if ($game->getStatus() != 0) {
            return $this->sendErrorMessage("Cette partie n'est pas dans la catégorie des parties disponibles");
        }

        // Test si le joueur est bien le propriétaire de cette partie
        if ($game->getOwner()->getId() != $user->getId()) {
			return $this->sendErrorMessage("Vous n'êtes pas propriétaire de cette partie");
        }

        // Test si la partie compte au moins 2 joueurs
        if (count($game->getPlayers()) < 2) {
        	return $this->sendErrorMessage("Il faut au moins 2 joueurs pour commencer la partie");
        }

        // Change le status de la partie en "commencé"
        $game->setStatus(1);

        try {
            $em->flush();

            return new JsonResponse([
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            $this->sendErrorMessage("Une erreur inconnue s'est produite");
        }
    }

    private function sendErrorMessage($errorMessage) {
        return new JsonResponse([
            'success' => false,
            'error' => $errorMessage
        ], 500);
    }
}
