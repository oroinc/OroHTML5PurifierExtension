<?php

namespace Oro\Bundle\Html5PurifierBundle\Tools;

use Oro\Bundle\Html5PurifierBundle\Form\DataTransformer\SanitizeHTML5Transformer;
use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;

/**
 * Uses SanitizeHTML5Transformer to do sanitization and purification
 */
class Html5TagHelper extends HtmlTagHelper
{
    /** @var Html5TagProvider */
    protected $htmlTagProvider;

    /** @var Html5TagProvider */
    private $htmlTagProviderStrict;

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
     * {@inheritdoc}
     */
    public function sanitize($string)
    {
        $transformer = new SanitizeHTML5Transformer(
            $this->htmlTagProvider,
            implode(',', $this->htmlTagProviderStrict->getAllowedElements()),
            $this->cacheDir
        );
        $transformer->setHtmlTagProviderStrict($this->htmlTagProviderStrict);

        return $transformer->sanitizeStrict($string);
    }

    public function sanitizeWysiwyg($string)
    {
        $transformer = new SanitizeHTML5Transformer(
            $this->htmlTagProvider,
            implode(',', $this->htmlTagProvider->getAllowedElements()),
            $this->cacheDir
        );
        $transformer->setHtmlTagProviderStrict($this->htmlTagProviderStrict);

        return $transformer->transform($string);
    }

    /**
     * {@inheritdoc}
     */
    public function purify($string)
    {
        if (!$this->purifyTransformer) {
            $this->purifyTransformer = new SanitizeHTML5Transformer(
                $this->htmlTagProvider,
                null,
                $this->cacheDir
            );
            $this->purifyTransformer->setHtmlTagProviderStrict($this->htmlTagProviderStrict);
        }

        return trim($this->purifyTransformer->transform($string));
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
}
