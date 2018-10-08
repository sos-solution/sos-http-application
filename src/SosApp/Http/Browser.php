<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Http;

/**
 * Class Browser
 */
class Browser {
    /**
     * User agent
     *
     * @var string
     */
    public $userAgent    = '';

    /**
     * Is mobile ?
     *
     * @var bool
     */
    public $isMobile     = 0;
    
    /**
     * Is tabel ?
     *
     * @var bool
     */
    public $isTablet     = 0;
    
    /**
     * Is desktop ?
     *
     * @var bool
     */
    public $isDesktop    = 0;
    
    /**
     * Screen height
     *
     * @var integer
     */
    public $screenHeight = 0;
    
    /**
     * Screen width
     *
     * @var integer
     */
    public $screenWidth  = 0;

    /**
     * Application
     *
     * @var Application
     */
    public $app;

    /**
     * Parse browser
     *
     */
    public function parseBrowser() {
        $app    = $this->app;
        $cookie = $app->cookie;
        $header = $app->header;

        $this->userAgent = isset($header['USER_AGENT']) ? $header['USER_AGENT'] : '';

        if ( !isset($cookie['_B']) ) {
            $detect = new \Mobile_Detect;
            $isMobile = $detect->isMobile() ? 1 : 0;
            $isTablet =  $detect->isTablet() ? 1 : 0;

            $this->isMobile  = $isMobile;

            $this->isTablet  = $isTablet;

            $this->isDesktop = !$isMobile ? 1 : 0;

            // 31536000 = 1 YEAR
            $cookie->set('_B', sprintf("%d%d%d", $isMobile, $isTablet, !$isMobile ? 1 : 0), 31536000, '/', '', FALSE, TRUE);
        } else {
            $_B = $cookie['_B'];

            $this->isMobile  = $_B[0] == '1' ? 1 : 0;

            $this->isTablet  = $_B[1] == '1' ? 1 : 0;

            $this->isDesktop = $_B[2] == '1' ? 1 : 0;
        }
    }

    public function setScreen($height, $width) {
        $cookie->set('_SC', sprintf("%d,%d", $height, $width));
    }

    /**
     * Set Application
     *
     * @param Application $app  Application
     */
    public function setApp($app) {
        $this->app = $app;
    }

    /**
     * Get Application
     *
     * @return Application
     */
    public function getApp() {
        return $this->app;
    }
}