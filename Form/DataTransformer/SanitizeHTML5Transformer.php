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
     * {@inheritdoc}
     */
    protected function sanitize($value)
    {
        if (!$value) {
            return $value;
        }

        if (!$this->htmlTagProvider->isPurificationNeeded()) {
            return $value;
        }

        if (!$this->htmlPurifier) {
            $html5Config = \HTMLPurifier_HTML5Config::createDefault();
            $config = \HTMLPurifier_Config::create($html5Config);

            $config->set('HTML.DefinitionID', __CLASS__);
            $config->set('HTML.DefinitionRev', self::HTMLPURIFIER_CONFIG_REVISION);

            // add inline data support
            $config->set('URI.AllowedSchemes', $this->htmlTagProvider->getUriSchemes());
            $config->set('Attr.EnableID', true);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', $this->htmlTagProvider->getIframeRegexp());
            $config->set('Filter.ExtractStyleBlocks.TidyImpl', false);
            $config->set('CSS.AllowImportant', true);
            $config->set('CSS.AllowTricky', true);
            $config->set('CSS.Proprietary', true);
            $config->set('CSS.Trusted', true);

            $this->fillAllowedElementsConfig($config);
            $this->fillCacheConfig($config);

            if ($def = $config->maybeGetRawHTMLDefinition()) {
                $def->addElement(
                    'style',
                    'Block',
                    'Flow',
                    'Common',
                    [
                        'type' => 'Enum#text/css',
                        'media' => 'CDATA',
                    ]
                );
            }

            $this->htmlPurifier = new \HTMLPurifier($config);
        }

        return $this->htmlPurifier->purify($value);
    }
}