<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\Tests\DependencyInjection;

use Kreyu\Bundle\EasyAdminExportBundle\DependencyInjection\EasyAdminExportExtension;
use Kreyu\Bundle\EasyAdminExportBundle\DependencyInjection\KreyuEasyAdminExportExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class EasyAdminExportExtensionTest extends TestCase
{
    private $alias = 'kreyu_easy_admin_export';

    public function testExtensionLoadingWithDefaultValues()
    {
        $container = $this->getContainer();
        $extension = $this->getExtension();

        $extension->load([], $container);

        $this->assertTrue($container->hasParameter($this->alias . '.config'));
        $this->assertEquals(
            $this->getDefaultConfigValues(),
            $container->getParameter($this->alias . '.config')
        );
    }

    public function testExtensionLoadingWithOverridenValues()
    {
        $container = $this->getContainer();
        $extension = $this->getExtension();
        $configs = $this->getConfigs();

        $extension->load($configs, $container);

        $this->assertTrue($container->hasParameter($this->alias . '.config'));
        $this->assertEquals($configs[0], $container->getParameter($this->alias . '.config'));
    }

    protected function getContainer()
    {
        return new ContainerBuilder;
    }

    protected function getExtension()
    {
        return new KreyuEasyAdminExportExtension;
    }

    protected function getDefaultConfigValues()
    {
        return [
            'label' => 'Export',
            'icon' => 'table',
            'filename' => null,
            'timestamp' => true,
            'timestamp_prefix' => '_',
            'timestamp_format' => 'd-m-Y_H-i-s',
            'override_template' => true,
            'formats' => [
                'csv',
                'xls',
                'xlsx',
            ],
            'use_headers' => true,
            'metadata' => [
                'creator' => null,
                'last_modified_by' => null,
                'title' => null,
                'subject' => null,
                'description' => null,
                'keywords' => null,
                'category' => null,
            ],
            'headers' => [
                'content_type' => 'application/vnd.ms-excel',
                'cache_control' => 'max-age=0',
            ],
        ];
    }

    protected function getConfigs()
    {
        return [
            [
                'label' => 'Export',
                'icon' => 'table',
                'filename' => 'fancy_export',
                'timestamp' => true,
                'timestamp_prefix' => '-',
                'timestamp_format' => 'd-m-Y_H-i-s',
                'override_template' => false,
                'formats' => [
                    'csv',
                    'xls',
                    'xlsx',
                ],
                'use_headers' => true,
                'metadata' => [
                    'creator' => 'Author',
                    'last_modified_by' => 'Last monday',
                    'title' => 'Cool stuff',
                    'subject' => 'Also cool',
                    'description' => 'About this',
                    'keywords' => 'a, b, c',
                    'category' => 'products',
                ],
                'headers' => [
                    'content_type' => 'application/vnd.ms-excel',
                    'cache_control' => 'max-age=0',
                ],
            ]
        ];
    }
}
