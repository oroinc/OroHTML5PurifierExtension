<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\Html5PurifierBundle\Form\DataTransformer\SanitizeHTML5Transformer;
use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;

class SanitizeHTML5TransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $value
     * @param string $allowableTags
     * @param string $expected
     *
     * @dataProvider dataProvider
     */
    public function testTransform($value, $allowableTags, $expected)
    {
        /** @var Html5TagProvider|\PHPUnit\Framework\MockObject\MockObject $htmlTagProvider */
        $htmlTagProvider = $this->createMock(Html5TagProvider::class);
        $htmlTagProvider->expects($this->exactly(2))
            ->method('isPurificationNeeded')
            ->willReturn(true);
        $htmlTagProvider->expects($this->once())
            ->method('getIframeRegexp')
            ->willReturn('<^https?://(www.)?(youtube.com/embed/|player.vimeo.com/video/)>');
        $htmlTagProvider->expects($this->once())
            ->method('getUriSchemes')
            ->willReturn(['http' => true, 'https' => true]);

        $transformer = new SanitizeHTML5Transformer($htmlTagProvider, $allowableTags);

        $this->assertEquals(
            $expected,
            $transformer->transform($value)
        );

        $this->assertEquals(
            $expected,
            $transformer->reverseTransform($value)
        );
    }

    public function testTransformPurifierDisabled()
    {
        /** @var Html5TagProvider|\PHPUnit\Framework\MockObject\MockObject $htmlTagProvider */
        $htmlTagProvider = $this->createMock(Html5TagProvider::class);
        $htmlTagProvider->expects($this->exactly(2))
            ->method('isPurificationNeeded')
            ->willReturn(false);

        $allowableTags = 'a';
        $transformer = new SanitizeHTML5Transformer($htmlTagProvider, $allowableTags);

        $this->assertEquals(
            '<p>sometext</p>',
            $transformer->transform('<p>sometext</p>')
        );

        $this->assertEquals(
            '<p>sometext</p>',
            $transformer->reverseTransform('<p>sometext</p>')
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return array_merge($this->transformDataProvider(), $this->xssDataProvider());
    }

    /**
     * @link https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet
     *
     * @return array
     */
    protected function xssDataProvider()
    {
        $str = '<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&' .
            '#x58&#x53&#x53&#x27&#x29>';

        return [
            'image' => ['<IMG SRC="javascript:alert(\'XSS\');">', null, ''],
            'script' => ['<script>alert(\'xss\');</script>', null, ''],
            'coded' => [$str, null, ''],
            'css expr' => ['<IMG STYLE="xss:expression(alert(\'XSS\'))">', null, '']
        ];
    }

    /**
     * @return array
     */
    protected function transformDataProvider()
    {
        return [
            'default' => ['sometext', null, 'sometext'],
            'not allowed tag' => ['<p>sometext</p>', 'a', 'sometext'],
            'allowed tag' => ['<p>sometext</p>', 'p', '<p>sometext</p>'],
            'mixed' => ['<p>sometext</p></br>', 'p', '<p>sometext</p>'],
            'attribute' => ['<p class="class">sometext</p>', 'p[class]', '<p class="class">sometext</p>'],
            'mixed attribute' => [
                '<p class="class">sometext</p><span data-attr="mixed">',
                'p[class]',
                '<p class="class">sometext</p>'
            ],
            'prepare allowed' => ['<a>first text</a><c>second text</c>', 'a, b', '<a>first text</a>second text'],
            'prepare not allowed' => ['<p>sometext</p>', 'a[class]', 'sometext'],
            'prepare with allowed' => ['<p>sometext</p>', 'a, p[class]', '<p>sometext</p>'],
            'prepare attribute' => ['<p>sometext</p>', 'a[class], p', '<p>sometext</p>'],
            'prepare attributes' => ['<p>sometext</p>', 'p[class|style]', '<p>sometext</p>'],
            'prepare or condition' => ['<p>sometext</p>', 'a[href|target=_blank], b/p', '<p>sometext</p>'],
            'prepare empty' => ['<p>sometext</p>', '[href|target=_blank],/', 'sometext'],
            'default attributes set' => ['<p>sometext</p>', '@[style],a', 'sometext'],
            'default attributes set with allowed' => ['<p>sometext</p>', '@[style],p', '<p>sometext</p>'],
            'id attribute' => [
                '<div id="test" data-id="test2">sometext</div>',
                'div[id]',
                '<div id="test">sometext</div>'
            ],
            'iframe allowed' => [
                '<iframe id="video-iframe" allowfullscreen="" src="https://www.youtube.com/embed/XWyzuVHRe0A?' .
                'rel=0&amp;iv_load_policy=3&amp;modestbranding=1"></iframe>',
                'iframe[id|allowfullscreen|src]',
                '<iframe id="video-iframe" allowfullscreen src="https://www.youtube.com/embed/XWyzuVHRe0A?'.
                'rel=0&amp;iv_load_policy=3&amp;modestbranding=1"></iframe>'
            ],
            'iframe invalid src' => [
                '<iframe id="video-iframe" allowfullscreen="" src="https://www.scam.com/embed/XWyzuVHRe0A?' .
                'rel=0&amp;iv_load_policy=3&amp;modestbranding=1"></iframe>',
                'iframe[id|allowfullscreen|src]',
                '<iframe id="video-iframe" allowfullscreen></iframe>'
            ],
            'iframe bypass src' => [
                '<iframe id="video-iframe" allowfullscreen="" src="https://www.scam.com/embed/XWyzuVHRe0A' .
                '?bypass=https://www.youtube.com/embed/XWyzuVHRe0A' .
                'rel=0&amp;iv_load_policy=3&amp;modestbranding=1"></iframe>',
                'iframe[id|allowfullscreen|src]',
                '<iframe id="video-iframe" allowfullscreen></iframe>'
            ],
        ];
    }
}
