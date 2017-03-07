<?php

namespace YahtzeeBundle\Handler;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
 
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
	public function __construct() {

	}
	
	public function onLogoutSuccess(Request $request) {
		return new JsonResponse([
			"success" => true],
			200);
	}
	
}