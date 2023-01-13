<?php

namespace App\Services;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SubpathSubscriber implements EventSubscriberInterface
{
    public function __construct(private ?string $subpath)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 3000],
            ],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->subpath) {
            $request = $event->getRequest();

            $newUri =
                $this->subpath.
                $request->server->get('REQUEST_URI');

            $event->getRequest()->server->set('REQUEST_URI', $newUri);
            $request->initialize(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
        }
    }
}
