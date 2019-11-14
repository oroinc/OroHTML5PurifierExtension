<?php

namespace Oro\Bundle\Html5PurifierBundle\Form\Type\Extension;

use Oro\Bundle\FormBundle\Form\DataTransformer\SanitizeHTMLTransformer;
use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\Html5PurifierBundle\Form\DataTransformer\SanitizeHTML5Transformer;
use Oro\Bundle\Html5PurifierBundle\Provider\Html5TagProvider;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Provides WYSIWYG editor functionality with additional settings for HTML purification modes
 */
class OroRichTextHtml5Extension extends AbstractTypeExtension
{
    /** @var Html5TagProvider */
    protected $htmlTagProvider;

    /** @var string */
    protected $cacheDir;

    /** @var HtmlTagHelper */
    private $htmlTagHelper;

    /**
     * @param Html5TagProvider $htmlTagProvider
     * @param string $cacheDir
     */
    public function __construct(
        Html5TagProvider $htmlTagProvider,
        $cacheDir = null
    ) {
        $this->htmlTagProvider = $htmlTagProvider;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param HtmlTagHelper $htmlTagHelper
     */
    public function setHtmlTagHelper(HtmlTagHelper $htmlTagHelper): void
    {
        $this->htmlTagHelper = $htmlTagHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OroRichTextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options['wysiwyg_options']['valid_elements']) {
            $transformer = new SanitizeHTML5Transformer(
                $this->htmlTagProvider,
                $options['wysiwyg_options']['valid_elements'],
                $this->cacheDir
            );
            $transformer->setHtmlTagHelper($this->htmlTagHelper);

            FormUtils::replaceTransformer(
                $builder,
                $transformer,
                'model',
                function ($transformer, $key) {
                    return is_a($transformer, SanitizeHTMLTransformer::class);
                }
            );
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $pageComponentOptions = json_decode($view->vars['attr']['data-page-component-options'], true);

        if ($this->htmlTagProvider->isExtendedPurification()) {
            $this->addExtendedModeParameters($pageComponentOptions);
        }

        if (!$this->htmlTagProvider->isPurificationNeeded()) {
            $this->addDisabledModeParameters($pageComponentOptions);
        }

        $view->vars['attr']['data-page-component-options'] = json_encode($pageComponentOptions);
    }

    /**
     * @param array $options
     */
    private function addExtendedModeParameters(array &$options)
    {
        $options = array_merge($options, [
            'valid_children' => '+body[style]',
            'inline_styles' => true,
        ]);
    }

    /**
     * @param array $options
     */
    private function addDisabledModeParameters(array &$options)
    {
        $options = array_merge($options, [
            'verify_html' => false,
            'cleanup_on_startup' => false,
            'trim_span_elements' => false,
            'cleanup' => false,
            'convert_urls' => false,
            'force_br_newlines' => false,
            'force_p_newlines' => false,
            'forced_root_block' => '',
            'valid_children' => '+body[style]',
            'inline_styles' => true,
        ]);
    }
}
