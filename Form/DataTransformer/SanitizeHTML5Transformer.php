<?php

namespace Oro\Bundle\Html5PurifierBundle\Form\DataTransformer;

use Oro\Bundle\FormBundle\Form\DataTransformer\SanitizeHTMLTransformer;
use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;

/**
 * Sanitizes passed value using html purifier with support for HTML5 tags
 * and additional purification modes
 */
class SanitizeHTML5Transformer extends SanitizeHTMLTransformer
{
    const HTMLPURIFIER_CONFIG_REVISION = 2019072301;

    /** @var Html5TagProvider */
    private $htmlTagProvider;

    /** @var Html5TagProvider */
    private $htmlTagProviderStrict;

    /**
     * @param Html5TagProvider $htmlTagProvider
     * @param string|null $allowedElements
     * @param string|null $cacheDir
     */
    public function __construct(Html5TagProvider $htmlTagProvider, $allowedElements = null, $cacheDir = null)
    {
        $this->htmlTagProvider = $htmlTagProvider;

        parent::__construct($allowedElements, $cacheDir);
    }

    /**
     * @param Html5TagProvider $htmlTagProviderStrict
     */
    public function setHtmlTagProviderStrict(Html5TagProvider $htmlTagProviderStrict)
    {
        $this->htmlTagProviderStrict = $htmlTagProviderStrict;
    }

    /**
     * {@inheritdoc}
     */
    protected function sanitize($value)
    {
        if (!$value || !$this->htmlTagProvider->isPurificationNeeded()) {
            return $value;
        }

        return $this->htmlTagHelper->sanitizeWysiwyg($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitizeStrict($value)
    {
        if (!$value) {
            return $value;
        }

        return $this->htmlTagHelper->sanitize($value);
    }
}
