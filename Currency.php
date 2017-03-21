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
     * Module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Currency',
            'version' => '1.0.0-dev',
            'description' => 'Allows to update rates of store currencies using Yahoo Finance feed',
            'author' => 'Iurii Makukh <gplcart.software@gmail.com>',
            'core' => '1.x',
            'license' => 'GNU General Public License 3.0',
            'configure' => 'admin/module/settings/currency',
            'settings' => array(
                'status' => true,
                'interval' => 86400,
                'correction' => 0,
                'currencies' => array(),
                'derivation' => array(10, 1, 1, 10)
            )
        );
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
