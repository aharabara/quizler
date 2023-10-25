<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        return $this
            ->httpUtils
            ->createRedirectResponse($request, $this->determineTargetUrl($request), Response::HTTP_SEE_OTHER);
    }

}
