<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Translate\Adapter;

/**
 * Class PHP
 */
class PHP extends \SosApp\Translate\Adapter {

    /**
     * parseFile
     *
     * @param string $filename
     * @param string $locale
     */    
    protected function parseFile($filename, $locale) {
        $pathinfo = pathinfo($filename);

        if ( $pathinfo['dirname'] == '.' ) {
            $filename = $pathinfo['filename'];
        } else {
            $filename = $pathinfo['dirname'] . '/' . $pathinfo['filename'];
        }

        if ( $this->overwirteMode && $locale != $this->defaultLocale ) {
            
            $localePath = sprintf("%s/%s/%s.lang.php", $this->baseDir, $this->defaultLocale, $filename);

            if ( file_exists($localePath) ) {
                $_ = &$this->getLocaleObject($this->defaultLocale);
                include $localePath;
            }
        }

        $localePath = sprintf("%s/%s/%s.lang.php", $this->baseDir, $locale, $filename);

        if ( file_exists($localePath) ) {
            $_ = &$this->getLocaleObject($locale);
            include $localePath;
        }
    }
}