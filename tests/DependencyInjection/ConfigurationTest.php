<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\Tests\DependencyInjection;

use Kreyu\Bundle\EasyAdminExportBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class ConfigurationTest extends TestCase
{
    public function testNodePath()
    {
        $configuration = new Configuration();
        $node = $configuration->getConfigTreeBuilder()->buildTree();

        $this->assertEquals('kreyu_easy_admin_export', $node->getPath());
    }

    public function testInputConfiguration()
    {
        $configuration = new Configuration();
        $node = $configuration->getConfigTreeBuilder()->buildTree();

        $input = $this->getInputConfiguration();

        $normalized = $node->normalize($input);
        $finalized = $node->finalize($normalized);

        $this->assertEquals($input, $finalized);
    }

    public function testDefaultConfiguration()
    {
        $configuration = new Configuration();
        $node = $configuration->getConfigTreeBuilder()->buildTree();

        $normalized = $node->normalize([]);
        $finalized = $node->finalize($normalized);

        $this->assertEquals($this->getDefaultConfiguration(), $finalized);
    }

    protected function getInputConfiguration()
    {
        return [
            'label' => 'Export data',
            'icon' => 'file',
            'timestamp' => false,
            'timestamp_prefix' => '_',
            'timestamp_format' => null,
            'formats' => [
                'xls',
            ],
            'filename' => null,
            'override_template' => true,
            'use_headers' => true,
            'fields' => [],
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

    protected function getDefaultConfiguration()
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
            'fields' => [],
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
}
