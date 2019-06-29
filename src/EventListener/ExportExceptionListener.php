<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Kreyu\Bundle\EasyAdminExportBundle\Exception\UnsupportedFormatException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class ExportExceptionListener
{
    private $router;
    private $configManager;

    public function __construct(ConfigManager $configManager, RouterInterface $router)
    {
        $this->router = $router;
        $this->configManager = $configManager;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof UnsupportedFormatException) {
            if ($referer = $event->getRequest()->query->get('referer')) {
                $url = urldecode($referer);
            } else {
                $url = $this->router->generate($this->configManager->getBackendConfig('homepage.router'));
            }

            $event->setResponse(new RedirectResponse($url));
        }
    }
}
