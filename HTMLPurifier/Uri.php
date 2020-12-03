<?php

namespace Oro\Bundle\Html5PurifierBundle\HTMLPurifier;

/**
 * Rewrite HTMLPurifier_URI validate to skip escaping of data scheme.
 */
class Uri extends \HTMLPurifier_URI
{
    /**
     * {@inheritDoc}
     */
    public function validate($config, $context)
    {
        if ($this->scheme === 'data') {
            $dataScheme = $this->getSchemeObj($config, $context);

            if ($dataScheme) {
                return $dataScheme->doValidate($this, $config, $context);
            }
        }

        return parent::validate($config, $context);
    }
}
