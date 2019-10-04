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
    protected $html5TagProvider;

    /** @var Html5TagProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $html5TagProviderStrict;

    /** @var string */
    private $cachePath;

    protected function setUp()
    {
        $this->cachePath = $this->getTempDir('cache_test_data');
        $this->html5TagProvider = $this->createMock(Html5TagProvider::class);
        $this->html5TagProviderStrict = $this->createMock(Html5TagProvider::class);

        $this->helper = new Html5TagHelper($this->html5TagProvider, $this->cachePath);
        $this->helper->setHtmlTagProviderStrict($this->html5TagProviderStrict);
    }

    protected function tearDown()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->cachePath);
    }

    public function testHtmlPurify()
    {
        $this->html5TagProvider->expects($this->never())
            ->method('isExtendedPurification');
        $this->html5TagProvider->expects($this->once())
            ->method('isPurificationNeeded')
            ->willReturn(true);
        $this->html5TagProvider->expects($this->once())
            ->method('getUriSchemes')
            ->willReturn(['http' => true, 'https' => true]);

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

    public function testHtmlPurifyDisabledPurification()
    {
        $this->html5TagProvider->expects($this->never())
            ->method('isExtendedPurification');
        $this->html5TagProvider->expects($this->once())
            ->method('isPurificationNeeded')
            ->willReturn(false);

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

        $this->assertEquals($testString, $this->helper->purify($testString));
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
}
