<?php

namespace YahtzeeBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Entity\User;
use YahtzeeBundle\Entity\Game;
use YahtzeeBundle\Entity\Sheet;

class LoadBasicsDatas implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        // --------- USER 1 ------------
        $user1 = $userManager->createUser();
        $user1->setUsername("jtest1");
        $user1->setEmail("t" + rand(1, 1000000) . "@test.ch");
        $user1->setPlainPassword("1234");
        $user1->setEnabled(true);
        $user1->setRoles(array('ROLE_USER'));
        $user1->setLastContact(new \DateTime());
        $userManager->updateUser($user1, true);



        // --------- USER 2 ------------
        $user2 = $userManager->createUser();
        $user2->setUsername("jtest2");
        $user2->setEmail("t" + rand(1, 1000000) . "@test.ch");
        $user2->setPlainPassword("1234");
        $user2->setEnabled(true);
        $user2->setRoles(array('ROLE_USER'));
        $user2->setLastContact(new \DateTime());
        $userManager->updateUser($user2, true);


        

        // --------- GAME 1 ------------
        $game1 = new Game();
        $game1->setName("Partie créée");
        $game1->setGameDate(new \DateTime());
        $game1->setStatus(0);
        $game1->setOwner($user1);
        $game1->setPlayerTurn($user1);

        $sheet1 = new Sheet();
        $sheet1->setGame($game1);
        $sheet1->setPlayer($user1);
        $sheet1->setPlayerOrder(1);

        $sheet2 = new Sheet();
        $sheet2->setGame($game1);
        $sheet2->setPlayer($user2);
        $sheet2->setPlayerOrder(2);



        // --------- GAME 2 ------------
        $game2 = new Game();
        $game2->setName("Partie en cours");
        $game2->setGameDate(new \DateTime());
        $game2->setStatus(1);
        $game2->setOwner($user2);
        $game2->setPlayerTurn($user2);

        $sheet3 = new Sheet();
        $sheet3->setFives(15);
        $sheet3->setOnePair(10);
        $sheet3->setOnes(4);
        $sheet3->setFullHouse(25);
        $sheet3->setGame($game2);
        $sheet3->setPlayer($user1);
        $sheet3->setPlayerOrder(1);

        $sheet4 = new Sheet();
        $sheet4->setThrees(9);
        $sheet4->setTwoPairs(18);
        $sheet4->setTwos(60);
        $sheet4->setFullHouse(25);
        $sheet4->setGame($game2);
        $sheet4->setPlayer($user2);
        $sheet4->setPlayerOrder(2);



        // --------- GAME 3 ------------
        $game3 = new Game();
        $game3->setName("Partie terminée");
        $game3->setGameDate(new \DateTime());
        $game3->setStatus(2);
        $game3->setOwner($user1);
        $game3->setWinner($user1);

        $sheet5 = new Sheet();
        $sheet5->setOnes(4);
        $sheet5->setTwos(4);
        $sheet5->setThrees(9);
        $sheet5->setFours(16);
        $sheet5->setFives(15);
        $sheet5->setSixes(18);
        $sheet5->setOnePair(10);
        $sheet5->setTwoPairs(18);
        $sheet5->setThreeOfAKind(22);
        $sheet5->setFourOfAKind(24);
        $sheet5->setFullHouse(25);
        $sheet5->setSmallStraight(30);
        $sheet5->setLargeStraight(40);
        $sheet5->setChance(12);
        $sheet5->setYahtzee(0);
        $sheet5->setTotalScore(282);
        $sheet5->setGame($game3);
        $sheet5->setPlayer($user1);
        $sheet5->setPlayerOrder(1);

        $sheet6 = new Sheet();
        $sheet6->setOnes(2);
        $sheet6->setTwos(2);
        $sheet6->setThrees(6);
        $sheet6->setFours(12);
        $sheet6->setFives(15);
        $sheet6->setSixes(18);
        $sheet6->setOnePair(12);
        $sheet6->setTwoPairs(17);
        $sheet6->setThreeOfAKind(23);
        $sheet6->setFourOfAKind(25);
        $sheet6->setFullHouse(25);
        $sheet6->setSmallStraight(30);
        $sheet6->setLargeStraight(40);
        $sheet6->setChance(15);
        $sheet6->setYahtzee(0);
        $sheet6->setTotalScore(242);
        $sheet6->setGame($game3);
        $sheet6->setPlayer($user2);
        $sheet6->setPlayerOrder(2);




        // --------- PERSIST ------------
        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($game1);
        $manager->persist($game2);
        $manager->persist($game3);
        $manager->persist($sheet1);
        $manager->persist($sheet2);
        $manager->persist($sheet3);
        $manager->persist($sheet4);
        $manager->persist($sheet5);
        $manager->persist($sheet6);

        $manager->flush();
    }
}