<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\Tests\Configuration;

use Kreyu\Bundle\EasyAdminExportBundle\Configuration\ExportConfigPass;
use Kreyu\Bundle\EasyAdminExportBundle\DependencyInjection\KreyuEasyAdminExportExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class ExportConfigPassTest extends TestCase
{
    public function testProcessingBackendConfig()
    {
        $parameterBag = $this->getParameterBag();
        $configPass = new ExportConfigPass($parameterBag);

        $backendConfig = $this->getBackendConfig();
        $processedConfig = $configPass->process($backendConfig);

        // Template decorating
        $this->assertEquals(
            '@KreyuEasyAdminExport\default\list.html.twig',
            $processedConfig['entities']['Product']['templates']['list']
        );

        // Export fields inheritance
        $this->assertEquals(
            $backendConfig['entities']['Product']['list']['fields'],
            $processedConfig['entities']['Product']['export']['fields']
        );

        // Container parameter inheritance
        $configuration = $parameterBag->get('kreyu_easy_admin_export.config');

        $parameters = [
            'icon',
            'label',
            'timestamp',
            'timestamp_format',
        ];

        foreach ($parameters as $parameter) {
            $this->assertEquals(
                $configuration[$parameter],
                $processedConfig['entities']['Product']['export'][$parameter]
            );
        }
    }

    protected function getParameterBag()
    {
        $container = new ContainerBuilder;
        $extension = new KreyuEasyAdminExportExtension;

        $extension->load([], $container);

        return $container->getParameterBag();
    }

    protected function getBackendConfig()
    {
        return [
            'entities' => [
                'Product' => [
                    'name' => 'Product',
                    'list' => [
                        'fields' => [
                            'id' => [
                                'property' => 'id',
                                'label' => 'product.fields.id',
                            ],
                            'name' => [
                                'property' => 'name',
                                'label' => 'product.fields.name',
                            ],
                        ],
                    ],
                    'export' => null,
                    'templates' => [
                        'list' => '@EasyAdmin/default/list.html.twig',
                    ],
                ],
            ],
        ];
    }
}
