<?php

/*
 * This file is part of the EasyAdminExportBundle package.
 *
 * (c) Sebastian Wróblewski <kontakt@swroblewski.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreyu\Bundle\EasyAdminExportBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Kreyu\Bundle\EasyAdminExportBundle\DependencyInjection\Configuration;
use RuntimeException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function sprintf;

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
     * @param array $entityConfig
     * @return bool
     */
    private function supports(array $entityConfig)
    {
        return array_key_exists('export', $entityConfig);
    }

    /**
     * Decorate entity configuration by modifying its values.
     *
     * @param array $entityConfig
     * @return array
     */
    private function decorateEntityConfig(array $entityConfig)
    {
        $entityConfig = $this->processConfiguration($entityConfig);
        $entityConfig = $this->processFields($entityConfig);

        // If filename is not given, use the lowercase entity name.
        if (null === $entityConfig['export']['filename']) {
            $entityConfig['export']['filename'] = mb_strtolower($entityConfig['name']);
        }

        // Override the template if allowed in the configuration (by default).
        if ($entityConfig['export']['override_template']) {
            $entityConfig['templates']['list'] = '@KreyuEasyAdminExport\default\list.html.twig';
        }

        return $entityConfig;
    }

    /**
     * Process the entity configuration, merging it with the global configuration.
     *
     * @param array $entityConfig
     * @return array
     */
    private function processConfiguration(array $entityConfig)
    {
        $processor = new Processor;

        $processed = $processor->processConfiguration(new Configuration, [
            $this->parameterBag->get('kreyu_easy_admin_export.config')
        ]);

        $entityConfig['export'] = array_merge($processed, $entityConfig['export'] ?? []);

        return $entityConfig;
    }

    /**
     * Process the entity fields defined in the configuration.
     *
     * @param  array $entityConfig
     * @return array
     */
    private function processFields(array $entityConfig)
    {
        // If the exportable fields are not defined in the configuration, inherit fields from the list action.
        if (empty($entityConfig['export']['fields'])) {
            $entityConfig['export']['fields'] = $entityConfig['list']['fields'];

            // List fields are already processed, so we're done here.
            return $entityConfig;
        }

        // Create a temporary array to hold the processed fields.
        $fields = [];

        // Loop through the defined in the configuration export fields.
        foreach ($entityConfig['export']['fields'] as $field) {

            // If field is defined without any extended configuration, e.g. fields: ['id', 'name']
            if (!is_array($field)) {

                // Find the field in the entity properties. Throw the exception if the field cannot be found.
                if (!array_key_exists($field, $entityConfig['properties'])) {
                    throw new RuntimeException(sprintf('One of the properties of the "fields" option for the "export" action of the "%s" entity does not exist.', $entityConfig['class']));
                }

                // Push the processed field to the temporary array and move/continue to the next field.
                $fields[$field] = $entityConfig['properties'][$field];

                // Set the transformer to null because it is not given.
                $fields[$field]['transformer'] = null;

                continue;
            }

            // If defined field does not contain the "property" value, throw an exception.
            if (!array_key_exists('property', $field)) {
                throw new RuntimeException(sprintf('One of the values of the "fields" option for the "export" view of the "%s" entity does not define the mandatory option "property".', $entityConfig['class']));
            }

            // Find the field in the entity properties. Throw the exception if the field cannot be found.
            if (!array_key_exists($property = $field['property'], $entityConfig['properties'])) {
                throw new RuntimeException(sprintf('One of the properties of the "fields" option for the "export" action of the "%s" entity does not exist.', $entityConfig['class']));
            }

            // Push the processed field to the temporary array.
            $fields[$property] = array_merge($entityConfig['properties'][$property], $field);

            // Set the transformer to null if not given.
            if (!array_key_exists('transformer', $fields[$property])) {
                $fields[$property]['transformer'] = null;
            }
        }

        // Replace the defined in the configuration export fields with the processed ones.
        $entityConfig['export']['fields'] = $fields;

        return $entityConfig;
    }
}
