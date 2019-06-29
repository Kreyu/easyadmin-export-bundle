<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function method_exists;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('kreyu_easy_admin_export');

        $this->getRootNode($treeBuilder, 'kreyu_easy_admin_export')
            ->children()
                ->scalarNode('label')
                    ->defaultValue('Export')
                    ->info('Label visible on the export button.')
                ->end()
                ->scalarNode('icon')
                    ->defaultValue('table')
                    ->info('Icon visible on the export button.')
                ->end()
                ->scalarNode('filename')
                    ->defaultNull()
                    ->info('Base filename, if null (by default) the lowercase entity name is used.')
                ->end()
                ->booleanNode('use_headers')
                    ->defaultTrue()
                    ->info('If true (by default), the headers will be included in the spreadsheets.')
                ->end()
                ->booleanNode('timestamp')
                    ->defaultTrue()
                    ->info('If true (by default), the current datetime will be included in the filename.')
                ->end()
                ->scalarNode('timestamp_format')
                    ->defaultValue('d-m-Y_H-i-s')
                    ->info('Format of the filename timestamp.')
                ->end()
                ->scalarNode('timestamp_prefix')
                    ->defaultValue('_')
                    ->info('String prefix, which separates the base filename from timestamp in filename.')
                ->end()
                ->booleanNode('override_template')
                    ->defaultTrue()
                    ->info('If true (by default), the list template gets overriden with the custom one, adding the export button.')
                ->end()
                ->arrayNode('formats')
                    ->prototype('scalar')->end()
                    ->defaultValue(['csv', 'xls', 'xlsx'])
                    ->info('An array of enabled export formats.')
                ->end()
                ->arrayNode('fields')
                    ->prototype('variable')->end()
                    ->defaultValue([])
                    ->info('Entity exportable fields. Inherits from entity list fields by default.')
                ->end()
                ->arrayNode('metadata')
                    ->addDefaultsIfNotSet()
                    ->info('Metadata properties applied to the generated spreadsheets.')
                    ->children()
                        ->scalarNode('creator')->defaultNull()->end()
                        ->scalarNode('last_modified_by')->defaultNull()->end()
                        ->scalarNode('title')->defaultNull()->end()
                        ->scalarNode('subject')->defaultNull()->end()
                        ->scalarNode('description')->defaultNull()->end()
                        ->scalarNode('keywords')->defaultNull()->end()
                        ->scalarNode('category')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('headers')
                    ->addDefaultsIfNotSet()
                    ->info('Headers applied to the streamed responses with the generated spreadsheets.')
                    ->children()
                        ->scalarNode('content_type')->defaultValue('application/vnd.ms-excel')->end()
                        ->scalarNode('cache_control')->defaultValue('max-age=0')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * Retrieve the root node of given tree builder.
     * Works as a BC layer for symfony/config 4.1 and older (copied from EasyCorp/EasyAdminBundle).
     *
     * @param  TreeBuilder $treeBuilder
     * @param  string $name
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function getRootNode(TreeBuilder $treeBuilder, $name)
    {
        if (!method_exists($treeBuilder, 'getRootNode')) {
            return $treeBuilder->root($name);
        }

        return $treeBuilder->getRootNode();
    }
}
