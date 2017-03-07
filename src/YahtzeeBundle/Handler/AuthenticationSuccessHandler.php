<?php

namespace YahtzeeBundle\Handler;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * class AuthenticationSuccessHandler
 *
 * @author Nicolas Macherey <nicolas.macherey@gmail.com>
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess( Request $request, TokenInterface $token )
    {
        $response = new JsonResponse(['success' => true, 'user-id' => $token->getUser()->getId(), 'username' => $token->getUser()->getUsername()], 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}