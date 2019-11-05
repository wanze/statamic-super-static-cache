<?php

namespace Statamic\Addons\SuperStaticCache;

use Statamic\Extend\Extensible;
use Statamic\Extend\Tags;
use Statamic\View\Antlers\Template;

class SuperStaticCacheTags extends Tags
{
    use Extensible;

    /**
     * The {{ super_static_cache:pouplate_csrf_tokens }} tag.
     *
     * Calls the token API and injects the CSRF tokens in each _token form input field.
     * You may optionally pass a "form_selector" parameter which is used to find the
     * Statamic forms in the DOM, e.g. form_selector="[data-statamic-form]".
     *
     * @return string|null
     */
    public function populateCsrfTokens()
    {
        if (!$this->getConfigBool('dynamic_csrf_enabled')) {
            return null;
        }

        $formSelector = $this->getParam('form_selector', 'form');

        $template = $this->getDirectory() . '/templates/dynamic_csrf_tokens.antlers.html';

        return Template::parse(file_get_contents($template), [
            'form_selector' => $formSelector,
        ]);
    }
}
