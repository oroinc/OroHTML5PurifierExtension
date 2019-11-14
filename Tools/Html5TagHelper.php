<?php

namespace Oro\Bundle\Html5PurifierBundle\Tools;

use Oro\Bundle\FormBundle\Form\Converter\TagDefinitionConverter;
use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Provides sanitization and purification methods
 */
class Html5TagHelper extends HtmlTagHelper
{
    const SUB_DIR = 'ezyang';
    const MODE = 0775;
    const HTMLPURIFIER_CONFIG_REVISION = 2019072301;

    /** @var \HtmlPurifier */
    private $htmlPurifier;

    /** \HtmlPurifier */
    private $htmlPurifierWYSIWYG;

    /** @var Html5TagProvider */
    protected $htmlTagProvider;

    /** @var Html5TagProvider */
    private $htmlTagProviderStrict;

    /** @var array */
    private $additionalAttributes = [];

    /** @var array */
    private $additionalElements = [];

    /**
     * @param Html5TagProvider $htmlTagProvider
     * @param string|null $cacheDir
     */
    public function __construct(
        Html5TagProvider $htmlTagProvider,
        $cacheDir = null
    ) {
        parent::__construct(
            $htmlTagProvider,
            $cacheDir
        );
    }

    /**
     * @param Html5TagProvider $htmlTagProviderStrict
     */
    public function setHtmlTagProviderStrict(Html5TagProvider $htmlTagProviderStrict)
    {
        $this->htmlTagProviderStrict = $htmlTagProviderStrict;
    }

    /**
     * @param string $elementName
     * @param string $attributeName
     * @param string $attributeType
     */
    public function setAttribute(string $elementName, string $attributeName, string $attributeType): void
    {
        $this->additionalAttributes[$elementName][$attributeName] = $attributeType;
    }

    /**
     * @param string $elementName
     * @param string $type
     * @param string $contents
     * @param string $attributeCollections
     * @param bool $excludeSameElement
     */
    public function setElement(
        string $elementName,
        string $type,
        string $contents,
        string $attributeCollections,
        bool $excludeSameElement = false
    ): void {
        $this->additionalElements[$elementName] = [
            'type' => $type,
            'contents' => $contents,
            'attribute_collections' => $attributeCollections,
            'excludeSameElement' => $excludeSameElement
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize($string)
    {
        if (!$this->htmlPurifier) {
            $this->htmlPurifier = $this->getPurifier(true);
        }

        return $this->htmlPurifier->purify($string);
    }

    public function sanitizeWysiwyg($string)
    {
        if (!$this->htmlPurifierWYSIWYG) {
            $this->htmlPurifierWYSIWYG = $this->getPurifier();
        }

        return $this->htmlPurifierWYSIWYG->purify($string);
    }

    /**
     * {@inheritdoc}
     */
    public function purify($string)
    {
        return trim($this->sanitize($string));
    }

    /**
     * {@inheritdoc}
     */
    public function escape($string)
    {
        $config = \HTMLPurifier_HTML5Config::createDefault();
        $config->set('Cache.SerializerPath', $this->cacheDir);
        $config->set('Cache.SerializerPermissions', 0775);
        $config->set('Attr.EnableID', true);
        $config->set('Core.EscapeInvalidTags', true);

        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($string);
    }

    /**
     * @param bool $strict
     * @return \HTMLPurifier
     */
    private function getPurifier(bool $strict = false): \HTMLPurifier
    {
        $htmlTagProvider = $this->htmlTagProvider;
        if ($strict) {
            $htmlTagProvider = $this->htmlTagProviderStrict;
        }

        $html5Config = \HTMLPurifier_HTML5Config::createDefault();
        $config = \HTMLPurifier_Config::create($html5Config);

        $config->set('HTML.DefinitionID', __CLASS__);
        $config->set('HTML.DefinitionRev', self::HTMLPURIFIER_CONFIG_REVISION);

        // add inline data support
        $config->set('URI.AllowedSchemes', $htmlTagProvider->getUriSchemes());
        $config->set('Attr.EnableID', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', $htmlTagProvider->getIframeRegexp());
        $config->set('Filter.ExtractStyleBlocks.TidyImpl', false);
        $config->set('CSS.AllowImportant', true);
        $config->set('CSS.AllowTricky', true);
        $config->set('CSS.Proprietary', true);
        $config->set('CSS.Trusted', true);

        $this->fillAllowedElementsConfig($config, $strict);
        $this->fillCacheConfig($config);

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            foreach ($this->additionalElements as $elementName => $data) {
                $element = $def->addElement(
                    $elementName,
                    $data['type'],
                    $data['contents'],
                    $data['attribute_collections']
                );
                if ($data['excludeSameElement'] === true) {
                    $element->excludes = [$elementName => true];
                }
            }
            foreach ($this->additionalAttributes as $elementName => $attributeData) {
                foreach ($attributeData as $attributeName => $attributeType) {
                    $def->addAttribute($elementName, $attributeName, $attributeType);
                }
            }
        }

        return new \HTMLPurifier($config);
    }

    /**
     * Configure allowed tags
     *
     * @param \HTMLPurifier_Config $config
     * @param bool $strict
     */
    private function fillAllowedElementsConfig($config, $strict)
    {
        $converter = new TagDefinitionConverter();
        if ($strict) {
            $allowedElements = implode(',', $this->htmlTagProviderStrict->getAllowedElements());
        } else {
            $allowedElements = implode(',', $this->htmlTagProvider->getAllowedElements());
        }
        if ($allowedElements) {
            $config->set('HTML.AllowedElements', $converter->getElements($allowedElements));
            $config->set('HTML.AllowedAttributes', $converter->getAttributes($allowedElements));
        } else {
            $config->set('HTML.Allowed', '');
        }
    }

    /**
     * Configure cache
     *
     * @param \HTMLPurifier_Config $config
     */
    private function fillCacheConfig($config)
    {
        if ($this->cacheDir) {
            $cacheDir = $this->cacheDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . self::SUB_DIR;
            $this->touchCacheDir($cacheDir);
            $config->set('Cache.SerializerPath', $cacheDir);
            $config->set('Cache.SerializerPermissions', self::MODE);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }
    }

    /**
     * Create cache dir if need
     *
     * @param string $cacheDir
     */
    protected function touchCacheDir($cacheDir)
    {
        $fs = new Filesystem();
        if (!$fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir, self::MODE);
        }
    }
}
