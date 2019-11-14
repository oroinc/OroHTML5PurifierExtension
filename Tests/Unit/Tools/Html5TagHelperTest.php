<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Unit\Tools;

use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;
use Oro\Bundle\Html5PurifierBundle\Tools\Html5TagHelper;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Filesystem\Filesystem;

class Html5TagHelperTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    /** @var Html5TagHelper */
    protected $helper;

    /** @var Html5TagProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $htmlTagProvider;

    /** @var Html5TagProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $htmlTagProviderStrict;

    /** @var string */
    private $cachePath;

    protected function setUp()
    {
        $this->cachePath = $this->getTempDir('cache_test_data');
        $this->htmlTagProvider = $this->createMock(Html5TagProvider::class);
        $this->htmlTagProviderStrict = $this->createMock(Html5TagProvider::class);

        $this->helper = new Html5TagHelper($this->htmlTagProvider, $this->cachePath);
        $this->helper->setHtmlTagProviderStrict($this->htmlTagProviderStrict);

        $this->helper->setAttribute('img', 'usemap', 'CDATA');
        $this->helper->setAttribute('img', 'ismap', 'Bool');
        $this->helper->setAttribute('img', 'src', 'URI');

        $this->helper->setElement('map', 'Block', 'Flow', 'Common', true);
        $this->helper->setAttribute('map', 'id', 'ID');
        $this->helper->setAttribute('map', 'name', 'CDATA');

        $this->helper->setElement('area', 'Inline', 'Empty', 'Common', true);
        $this->helper->setAttribute('area', 'id', 'ID');
        $this->helper->setAttribute('area', 'name', 'CDATA');
        $this->helper->setAttribute('area', 'title', 'Text');
        $this->helper->setAttribute('area', 'alt', 'Text');
        $this->helper->setAttribute('area', 'coords', 'CDATA');
        $this->helper->setAttribute('area', 'accesskey', 'Character');
        $this->helper->setAttribute('area', 'nohref', 'Bool');
        $this->helper->setAttribute('area', 'href', 'URI');
        $this->helper->setAttribute('area', 'shape', 'Enum#rect,circle,poly,default');
        $this->helper->setAttribute('area', 'target', 'Enum#_blank,_self,_target,_top');
        $this->helper->setAttribute('area', 'tabindex', 'Text');
    }

    protected function tearDown()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->cachePath);
    }

    public function testHtmlPurify()
    {
        $this->htmlTagProviderStrict->expects($this->once())
            ->method('getUriSchemes')
            ->willReturn(['http' => true, 'https' => true]);

        $this->htmlTagProviderStrict->expects($this->once())
            ->method('getAllowedElements')
            ->willReturn([]);

        $testString = <<<STR
<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="GENERATOR" content="MSHTML 10.00.9200.17228">
<style id="owaParaStyle">P {
	MARGIN-BOTTOM: 0px; MARGIN-TOP: 0px
}
</style>
</head>
<body fPStyle="1" ocsi="0">
<div style="direction: ltr;font-family: Tahoma;color: #000000;font-size: 10pt;">no subject</div>
<div style="direction: ltr;font-family: Tahoma;color: #000000;font-size: 10pt;">no subject2</div>
<span>same line</span><span>same line2</span>
<p>same line</p><p>same line2</p>
</body>
</html>
STR;

        $expected = <<<STR
no subject
no subject2
same linesame line2
same linesame line2
STR;

        $this->assertEquals($expected, $this->helper->purify($testString));
    }

    public function testEscape()
    {
        $testString = <<<HTML
<span>same line</span><span>same line2</span>
<p>same line</p><p>same line2</p>
<script type="text/javascript">alert("test");</script>
HTML;

        $expected = <<<HTML
<span>same line</span><span>same line2</span>
<p>same line</p><p>same line2</p>
&lt;script type="text/javascript"&gt;alert("test");&lt;/script&gt;
HTML;

        $this->assertEquals($expected, $this->helper->escape($testString));
    }

    /**
     * @param string $value
     * @param string $allowableTags
     * @param string $expected
     *
     * @dataProvider dataProvider
     */
    public function testSanitize($value, $allowableTags, $expected): void
    {
        $this->htmlTagProviderStrict->expects($this->any())
            ->method('getIframeRegexp');
        $this->htmlTagProviderStrict->expects($this->once())
            ->method('getUriSchemes')
            ->willReturn(['http' => true, 'https' => true]);
        $this->htmlTagProviderStrict->expects($this->any())
            ->method('getAllowedElements')
            ->willReturn($allowableTags);

        $this->assertEquals($expected, $this->helper->sanitize($value));
    }

    /**
     * @param string $value
     * @param string $allowableTags
     * @param string $expected
     *
     * @dataProvider dataProvider
     */
    public function testSanitizeWysiwyg($value, $allowableTags, $expected): void
    {
        $this->htmlTagProvider->expects($this->any())
            ->method('getIframeRegexp');
        $this->htmlTagProvider->expects($this->once())
            ->method('getUriSchemes')
            ->willReturn(['http' => true, 'https' => true]);
        $this->htmlTagProvider->expects($this->any())
            ->method('getAllowedElements')
            ->willReturn($allowableTags);

        $this->assertEquals($expected, $this->helper->sanitizeWysiwyg($value));
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return array_merge($this->sanitizeDataProvider(), $this->xssDataProvider());
    }

    /**
     * @link https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet
     *
     * @return array
     */
    protected function xssDataProvider(): array
    {
        $str = '<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&' .
            '#x58&#x53&#x53&#x27&#x29>';

        return [
            'image' => ['<IMG SRC="javascript:alert(\'XSS\');">', [], ''],
            'script' => ['<script>alert(\'xss\');</script>', [], ''],
            'coded' => [$str, [], ''],
            'css expr' => ['<IMG STYLE="xss:expression(alert(\'XSS\'))">', [], '']
        ];
    }

    /**
     * @return array
     */
    protected function sanitizeDataProvider(): array
    {
        $mapHtml = '<img src="planets.gif" width="145" height="126" alt="Planets" usemap="#planetmap">'.
            '<map name="planetmap">'.
            '<area shape="rect" coords="0,0,82,126" href="sun.htm" alt="Sun" tabindex="-1">'.
            '<area shape="circle" coords="90,58,3" href="mercur.htm" alt="Mercury" tabindex="0">'.
            '<area shape="circle" coords="124,58,8" href="venus.htm" alt="Venus" tabindex="1">'.
            '</map>';

        return [
            'default' => ['sometext', [], 'sometext'],
            'not allowed tag' => ['<p>sometext</p>', ['a'], 'sometext'],
            'allowed tag' => ['<p>sometext</p>', ['p'], '<p>sometext</p>'],
            'mixed' => ['<p>sometext</p></br>', ['p'], '<p>sometext</p>'],
            'attribute' => ['<p class="class">sometext</p>', ['p[class]'], '<p class="class">sometext</p>'],
            'mixed attribute' => [
                '<p class="class">sometext</p><span data-attr="mixed">',
                ['p[class]'],
                '<p class="class">sometext</p>'
            ],
            'prepare allowed' => ['<a>first text</a><c>second text</c>', ['a', 'b'], '<a>first text</a>second text'],
            'prepare not allowed' => ['<p>sometext</p>', ['a[class]'], 'sometext'],
            'prepare with allowed' => ['<p>sometext</p>', ['a', 'p[class]'], '<p>sometext</p>'],
            'prepare attribute' => ['<p>sometext</p>', ['a[class]', 'p'], '<p>sometext</p>'],
            'prepare attributes' => ['<p>sometext</p>', ['p[class|style]'], '<p>sometext</p>'],
            'prepare or condition' => ['<p>sometext</p>', ['a[href|target=_blank]', 'b/p'], '<p>sometext</p>'],
            'prepare empty' => ['<p>sometext</p>', ['[href|target=_blank],/'], 'sometext'],
            'default attributes set' => ['<p>sometext</p>', ['@[style]', 'a'], 'sometext'],
            'default attributes set with allowed' => ['<p>sometext</p>', ['@[style]', 'p'], '<p>sometext</p>'],
            'id attribute' => [
                '<div id="test" data-id="test2">sometext</div>',
                ['div[id]'],
                '<div id="test">sometext</div>'
            ],
            'map element' => [
                $mapHtml,
                [
                    'img[src|width|height|alt|usemap]',
                    'map[name]',
                    'area[shape|coords|href|alt|tabindex]'
                ],
                $mapHtml
            ]
        ];
    }
}
