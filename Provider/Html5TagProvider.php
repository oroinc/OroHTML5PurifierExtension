<?php

namespace Oro\Bundle\Html5PurifierBundle\Provider;

use Oro\Bundle\FormBundle\Provider\HtmlTagProvider;

/**
 * Provides additional purification modes
 */
class Html5TagProvider extends HtmlTagProvider
{
    const HTML_PURIFIER_MODE_STRICT = 'strict';
    const HTML_PURIFIER_MODE_EXTENDED = 'extended';
    const HTML_PURIFIER_MODE_DISABLED = 'disabled';

    /** elements forbidden in strict mode */
    const STRICT_ELEMENTS = ['iframe', 'style'];

    /** @var string */
    private $htmlPurifierMode = self::HTML_PURIFIER_MODE_STRICT;

    /** @var array */
    private $iframeDomains;

    /** @var array */
    private $uriSchemes;

    /**
     * @param array $elements
     * @param string $htmlPurifierMode
     * @param array $iframeDomains
     * @param array $uriSchemes
     */
    public function __construct(
        array $elements,
        $htmlPurifierMode = self::HTML_PURIFIER_MODE_STRICT,
        $iframeDomains = [],
        $uriSchemes = []
    ) {
        if (\in_array(
            $htmlPurifierMode,
            [self::HTML_PURIFIER_MODE_STRICT, self::HTML_PURIFIER_MODE_EXTENDED, self::HTML_PURIFIER_MODE_DISABLED],
            true
        )) {
            $this->htmlPurifierMode = $htmlPurifierMode;
        }
        $this->iframeDomains = $iframeDomains;
        $this->uriSchemes = $uriSchemes;

        parent::__construct($this->filterElementsForStrictMode($elements));
    }

    /**
     * @param array $elements
     * @return array
     */
    private function filterElementsForStrictMode(array $elements): array
    {
        if ($this->htmlPurifierMode === self::HTML_PURIFIER_MODE_STRICT) {
            return array_filter(
                $elements,
                function ($allowedElement) {
                    return !\in_array($allowedElement, self::STRICT_ELEMENTS, true);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $elements;
    }

    /**
     * @return bool
     */
    public function isPurificationNeeded(): bool
    {
        return $this->htmlPurifierMode !== self::HTML_PURIFIER_MODE_DISABLED;
    }

    /**
     * @return bool
     */
    public function isExtendedPurification(): bool
    {
        return $this->htmlPurifierMode === self::HTML_PURIFIER_MODE_EXTENDED;
    }

    /**
     * @return string
     */
    public function getIframeRegexp(): string
    {
        if (!$this->iframeDomains) {
            return '';
        }

        return sprintf('<^https?://(www.)?(%s)>', implode('|', $this->iframeDomains));
    }

    /**
     * @return array
     */
    public function getUriSchemes(): array
    {
        $result = [];

        foreach ($this->uriSchemes as $scheme) {
            $result[$scheme] = true;
        }

        return $result;
    }
}
