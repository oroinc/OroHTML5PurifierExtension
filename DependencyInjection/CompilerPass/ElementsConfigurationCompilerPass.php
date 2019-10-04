<?php

namespace Oro\Bundle\Html5PurifierBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Move elements configuration from oro_form.provider.html_tag_provider to oro_html5_purifier.provider.html_tag_provider
 */
class ElementsConfigurationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $tagProviderDefinition = $container->getDefinition('oro_form.provider.html_tag_provider');

        $elements = $tagProviderDefinition->getArgument(0);

        $decoratedDefinition = $container->getDefinition('oro_html5_purifier.provider.html_tag_provider');
        $decoratedDefinition->replaceArgument(0, $elements);

        $decoratedStrictDefinition = $container->getDefinition('oro_html5_purifier.provider.html_tag_provider_strict');
        $decoratedStrictDefinition->replaceArgument(0, $elements);
    }
}
