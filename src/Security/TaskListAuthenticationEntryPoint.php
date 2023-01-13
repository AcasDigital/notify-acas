<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class TaskListAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private RequestStack $requestStack)
    {
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        $mainRequest = $this->requestStack->getMainRequest();
        if (null === $mainRequest) {
            return new RedirectResponse($this->urlGenerator->generate('app_index'));
        }

        $uri = $mainRequest->getUri();
        if (str_contains($uri, '/idr-bst/admin')) {
            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        $session = $request->getSession();
        $session->getFlashBag()->add('info', 'You will need your save and return code and memorable word to access your form.');

        return new RedirectResponse($this->urlGenerator->generate('return_from_start'));
    }
}
