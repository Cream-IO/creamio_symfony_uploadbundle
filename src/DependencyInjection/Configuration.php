<?php

namespace CreamIO\UploadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('creamio_upload');
        $rootNode
            ->children()
                ->scalarNode('upload_directory')
                    ->isRequired(true)
                ->end()
                ->scalarNode('default_upload_file_class')
                    ->isRequired(true)
                ->end()
                ->scalarNode('default_upload_file_field')
                    ->isRequired(true)
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}