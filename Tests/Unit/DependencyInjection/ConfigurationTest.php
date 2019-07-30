<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\Html5PurifierBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ArrayNode;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);

        /** @var $root ArrayNode */
        $root = $builder->buildTree();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $root);
        $this->assertEquals('oro_html5_purifier', $root->getName());
    }
}
