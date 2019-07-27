<?php

/*
 * This file is part of the EasyAdminExportBundle package.
 *
 * (c) Sebastian Wróblewski <kontakt@swroblewski.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
