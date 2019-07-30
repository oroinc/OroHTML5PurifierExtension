<?php

namespace Oro\Bundle\Html5PurifierBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Load bundle configuration and services.yml
 */
class OroHtml5PurifierExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['wysiwyg']['html_purifier_mode'])) {
            $container->setParameter('oro_html5_purifier.html_purifier_mode', $config['wysiwyg']['html_purifier_mode']);
        }

        if (isset($config['wysiwyg']['html_purifier_iframe_domains'])) {
            $container->setParameter(
                'oro_html5_purifier.html_purifier_iframe_domains',
                $config['wysiwyg']['html_purifier_iframe_domains']
            );
        }

        if (isset($config['wysiwyg']['html_purifier_uri_schemes'])) {
            $container->setParameter(
                'oro_html5_purifier.html_purifier_uri_schemes',
                $config['wysiwyg']['html_purifier_uri_schemes']
            );
        }

        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));
    }
}
