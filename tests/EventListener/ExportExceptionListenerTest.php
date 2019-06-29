<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\Tests\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Kreyu\Bundle\EasyAdminExportBundle\EventListener\ExportExceptionListener;
use Kreyu\Bundle\EasyAdminExportBundle\Exception\UnsupportedFormatException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class ExportExceptionListenerTest extends TestCase
{
    public function testOnKernelExceptionWithReferer()
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->createMock(ConfigManager::class);

        /** @var RouterInterface $router */
        $router = $this->createMock(RouterInterface::class);

        /** @var KernelInterface $kernel */
        $kernel = $this->createMock(KernelInterface::class);

        /** @var Request $request */
        $request = $this->createMock(Request::class);

        $request->query = new ParameterBag(['referer' => urlencode('https://google.com')]);

        $listener = new ExportExceptionListener($configManager, $router);

        $event = new GetResponseForExceptionEvent($kernel, $request, 0, new UnsupportedFormatException);

        $listener->onKernelException($event);

        /** @var RedirectResponse $response */
        $response = $event->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('https://google.com', $response->getTargetUrl());
    }

    public function testOnKernelExceptionWithoutReferer()
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->createMock(ConfigManager::class);

        /** @var RouterInterface|MockObject $router */
        $router = $this->createMock(RouterInterface::class);

        $router->method('generate')->willReturn('https://google.com');

        /** @var KernelInterface $kernel */
        $kernel = $this->createMock(KernelInterface::class);

        /** @var Request $request */
        $request = $this->createMock(Request::class);

        $request->query = new ParameterBag();

        $listener = new ExportExceptionListener($configManager, $router);

        $event = new GetResponseForExceptionEvent($kernel, $request, 0, new UnsupportedFormatException);

        $listener->onKernelException($event);

        /** @var RedirectResponse $response */
        $response = $event->getResponse();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertEquals('https://google.com', $response->getTargetUrl());
    }
}
