<?php

/**
 * @package Currency
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 */

namespace gplcart\modules\currency;

use gplcart\core\Module,
    gplcart\core\Config;

/**
 * Main class for Currency module
 */
class Currency extends Module
{

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * Implements hook "module.install.before"
     * @param string|null $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!function_exists('curl_init')) {
            $result = $this->getLanguage()->text('CURL library is not enabled');
        }
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
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
        $settings = $this->config->getFromModule('currency');

        if (!empty($settings['status'])) {
            /* @var $currency \gplcart\modules\currency\models\Currency */
            $currency = $this->getModel('Currency', 'currency');
            $currency->update($settings);
        }
    }

}
