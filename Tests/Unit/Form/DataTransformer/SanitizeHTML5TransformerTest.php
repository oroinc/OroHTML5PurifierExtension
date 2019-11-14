<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\Html5PurifierBundle\Form\DataTransformer\SanitizeHTML5Transformer;
use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;

class SanitizeHTML5TransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HtmlTagHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $htmlTagHelper;

    /**
     * @var Html5TagProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $htmlTagProvider;

    /**
     * @var SanitizeHTML5Transformer
     */
    private $transformer;

    protected function setUp()
    {
        $this->htmlTagHelper = $this->createMock(HtmlTagHelper::class);
        $this->htmlTagProvider = $this->createMock(Html5TagProvider::class);

        $this->transformer = new SanitizeHTML5Transformer($this->htmlTagProvider);
        $this->transformer->setHtmlTagHelper($this->htmlTagHelper);
    }

    public function testTransform(): void
    {
        $value = '<p class="classname">sometext</p>';
        $expected = 'sometext';

        $this->htmlTagHelper->expects($this->once())
            ->method('sanitizeWysiwyg')
            ->with($value)
            ->willReturn($expected);

        $this->htmlTagProvider->expects($this->once())
            ->method('isPurificationNeeded')
            ->willReturn(true);

        $this->assertEquals($expected, $this->transformer->transform($value));
    }

    public function testTransformWithoutPurification(): void
    {
        $value = '<p class="classname">sometext</p>';

        $this->htmlTagHelper->expects($this->never())
            ->method('sanitizeWysiwyg');

        $this->htmlTagProvider->expects($this->once())
            ->method('isPurificationNeeded')
            ->willReturn(false);

        $this->assertEquals($value, $this->transformer->transform($value));
    }

    public function testReverseTransform(): void
    {
        $value = '<p class="classname">sometext</p>';
        $expected = 'sometext';

        $this->htmlTagHelper->expects($this->once())
            ->method('sanitizeWysiwyg')
            ->with($value)
            ->willReturn($expected);

        $this->htmlTagProvider->expects($this->once())
            ->method('isPurificationNeeded')
            ->willReturn(true);

        $this->assertEquals($expected, $this->transformer->reverseTransform($value));
    }

    public function testSanitizeStrict()
    {
        $value = '<p class="classname">sometext</p>';
        $expected = 'sometext';

        $this->htmlTagHelper->expects($this->once())
            ->method('sanitize')
            ->with($value)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->transformer->sanitizeStrict($value));
    }
}
