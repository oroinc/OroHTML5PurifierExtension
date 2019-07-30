<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Oro\Bundle\Html5PurifierBundle\DependencyInjection\CompilerPass\ElementsConfigurationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ElementsConfigurationCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);

        $tags = ['a', 'b'];
        $tagProviderDefinition = $this->createMock(Definition::class);
        $tagProviderDefinition->expects($this->once())
            ->method('getArgument')
            ->with(0)
            ->willReturn($tags);
        $decoratedDefinition = $this->createMock(Definition::class);
        $decoratedDefinition->expects($this->once())
            ->method('replaceArgument')
            ->with(0, $tags);

        $container->expects($this->exactly(2))
            ->method('getDefinition')
            ->withConsecutive(
                ['oro_form.provider.html_tag_provider'],
                ['oro_html5_purifier.provider.html_tag_provider']
            )
            ->willReturnOnConsecutiveCalls(
                $tagProviderDefinition,
                $decoratedDefinition
            );

        $compilerPass = new ElementsConfigurationCompilerPass();
        $compilerPass->process($container);
    }
}
