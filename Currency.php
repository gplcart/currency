<?php

/**
 * @package Currency
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 */

namespace gplcart\modules\currency;

use gplcart\core\Module;

/**
 * Main class for Currency module
 */
class Currency extends Module
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Implements hook "module.install.before"
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!function_exists('curl_init')) {
            $result = 'CURL library is not enabled';
        }
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        // Module settings page
        $routes['admin/module/settings/currency'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\currency\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook "cron"
     */
    public function hookCron()
    {
        $settings = $this->config->module('currency');

        if (!empty($settings['status'])) {
            /* @var $model \gplcart\modules\currency\models\Currency */
            $currency = $this->getInstance('gplcart\\modules\\currency\\models\\Currency');
            $currency->setSettings($settings);
            $currency->update();
        }
    }

}
