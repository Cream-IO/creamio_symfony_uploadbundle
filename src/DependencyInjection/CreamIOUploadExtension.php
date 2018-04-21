<?php

namespace CreamIO\UploadBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CreamIOUploadExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('cream_io_upload.uploaderservice');
        $definition->setArgument(0, $config['upload_directory']);
        $definition->setArgument(1, $config['default_upload_file_class']);
        $definition->setArgument(2, $config['default_upload_file_field']);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'creamio_upload';
    }
}
