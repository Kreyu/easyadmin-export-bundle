<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Kreyu\Bundle\EasyAdminExportBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class ExportConfigPass implements ConfigPassInterface
{
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $alias => $entityConfig) {
            if ($this->supports($entityConfig)) {
                $backendConfig['entities'][$alias] = $this->decorateEntityConfig($entityConfig);
            }
        }

        return $backendConfig;
    }

    /**
     * Determine if entity with given configuration supports the export feature.
     *
     * @param  array $entityConfig
     * @return bool
     */
    private function supports(array $entityConfig)
    {
        return array_key_exists('export', $entityConfig);
    }

    /**
     * Decorate entity configuration by modifying its values.
     *
     * @param  array $entityConfig
     * @return array
     */
    private function decorateEntityConfig(array $entityConfig)
    {
        $entityConfig = $this->processConfiguration($entityConfig);

        if (empty($entityConfig['export']['fields'])) {
            $entityConfig['export']['fields'] = $entityConfig['list']['fields'];
        }

        if (null === $entityConfig['export']['filename']) {
            $entityConfig['export']['filename'] = mb_strtolower($entityConfig['name']);
        }

        if ($entityConfig['export']['override_template']) {
            $entityConfig['templates']['list'] = '@KreyuEasyAdminExport\default\list.html.twig';
        }

        return $entityConfig;
    }

    /**
     * Process the entity configuration.
     *
     * @param  array $entityConfig
     * @return array
     */
    private function processConfiguration(array $entityConfig)
    {
        $processor = new Processor;

        $processed = $processor->processConfiguration(new Configuration, [
            $this->parameterBag->get('kreyu_easy_admin_export.config')
        ]);

        $entityConfig['export'] = array_merge($entityConfig['export'] ?? [], $processed);

        return $entityConfig;
    }
}
