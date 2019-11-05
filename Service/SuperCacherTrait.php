<?php

namespace Statamic\Addons\SuperStaticCache\Service;

trait SuperCacherTrait
{
    private function prependDebugString($content)
    {
        if (!$this->getConfigBool('debug_enabled')) {
            return $content;
        }

        $string = $this->getConfig('debug_string', '');

        return $string . "\n" . $content;
    }
}
