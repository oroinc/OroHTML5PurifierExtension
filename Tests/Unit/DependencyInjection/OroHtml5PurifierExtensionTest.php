<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\Html5PurifierBundle\DependencyInjection\OroHtml5PurifierExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroHtml5PurifierExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroHtml5PurifierExtension());

        $expectedDefinitions = [
            'oro_html5_purifier.provider.html_tag_provider',
            'oro_html5_purifier.type.extension.rich_text',
            'oro_html5_purifier.html_tag_helper',
        ];

        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
