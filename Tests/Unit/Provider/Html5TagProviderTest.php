<?php

namespace Oro\Bundle\Html5PurifierBundle\Tests\Unit\Provider;

use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;

class Html5TagProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var Html5TagProvider */
    protected $html5TagProvider;

    /** @var array */
    private $elements;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->elements = [
            'p' => [],
            'span' => [
                'attributes' => ['id']
            ],
            'br' => [
                'hasClosingTag' => false
            ],
            'style' => [
                'attributes' => ['media', 'type']
            ],
            'iframe' => [
                'attributes' => ['allowfullscreen']
            ],
        ];

        $this->html5TagProvider = new Html5TagProvider($this->elements);
    }

    public function testGetAllowedElementsDefault()
    {
        $allowedElements = $this->html5TagProvider->getAllowedElements();
        $this->assertEquals(['@[style|class]', 'p', 'span[id]', 'br'], $allowedElements);
    }

    public function testGetAllowedExtended()
    {
        $htmlTagProvider = new Html5TagProvider($this->elements, Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED);
        $allowedElements = $htmlTagProvider->getAllowedElements();
        $this->assertEquals(
            ['@[style|class]', 'p', 'span[id]', 'br', 'style[media|type]', 'iframe[allowfullscreen]'],
            $allowedElements
        );
    }

    public function testGetAllowedTags()
    {
        $allowedTags = $this->html5TagProvider->getAllowedTags();
        $this->assertEquals('<p></p><span></span><br>', $allowedTags);
    }

    public function testIsPurificationNeededDefault()
    {
        $this->assertTrue($this->html5TagProvider->isPurificationNeeded());
    }

    public function testIsPurificationNeededStrict()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_STRICT);
        $this->assertTrue($htmlTagProvider->isPurificationNeeded());
    }

    public function testIsPurificationNeededExtended()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED);
        $this->assertTrue($htmlTagProvider->isPurificationNeeded());
    }

    public function testIsPurificationNeededDisabled()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_DISABLED);
        $this->assertFalse($htmlTagProvider->isPurificationNeeded());
    }

    public function testIsExtendedPurificationDefault()
    {
        $this->assertFalse($this->html5TagProvider->isExtendedPurification());
    }

    public function testIsExtendedPurificationExtended()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED);
        $this->assertTrue($htmlTagProvider->isExtendedPurification());
    }

    public function testGetIframeRegexp()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED, [
            'youtube.com/embed/',
            'player.vimeo.com/video/',
        ]);

        $this->assertEquals(
            '<^https?://(www.)?(youtube.com/embed/|player.vimeo.com/video/)>',
            $htmlTagProvider->getIframeRegexp()
        );
    }

    public function testGetIframeRegexpBypass()
    {
        $scamUri = 'https://www.scam.com/embed/XWyzuVHRe0A?bypass=https://www.youtube.com/embed/XWyzuVHRe0A';
        $allowedUri = 'https://www.youtube.com/embed/XWyzuVHRe0A';

        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED, [
            'youtube.com/embed/',
            'player.vimeo.com/video/',
        ]);

        $this->assertSame(0, preg_match($htmlTagProvider->getIframeRegexp(), $scamUri));
        $this->assertSame(1, preg_match($htmlTagProvider->getIframeRegexp(), $allowedUri));
    }

    public function testGetIframeRegexpEmpty()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED);

        $this->assertEquals('', $htmlTagProvider->getIframeRegexp());
    }

    public function testGetUriSchemes()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED, [], [
            'http',
            'https',
            'ftp',
        ]);

        $this->assertEquals(
            [
                'http' => true,
                'https' => true,
                'ftp' => true,
            ],
            $htmlTagProvider->getUriSchemes()
        );
    }

    public function testGetUriSchemesEmpty()
    {
        $htmlTagProvider = new Html5TagProvider([], Html5TagProvider::HTML_PURIFIER_MODE_EXTENDED);

        $this->assertEquals([], $htmlTagProvider->getUriSchemes());
    }
}
