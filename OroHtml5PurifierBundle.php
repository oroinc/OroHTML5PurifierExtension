<?php

namespace Oro\Bundle\Html5PurifierBundle;

use Oro\Bundle\Html5PurifierBundle\DependencyInjection\CompilerPass\ElementsConfigurationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle is used to add support of xemlock/htmlpurifier-html5 library for HTMLPurifier
 * as well as additional purification modes
 */
class OroHtml5PurifierBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ElementsConfigurationCompilerPass());
    }
}
