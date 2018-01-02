<?php

/**
 * @package Currency
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 */

namespace gplcart\modules\currency;

use gplcart\core\Module,
    gplcart\core\Container;

/**
 * Main class for Currency module
 */
class Main
{

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
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
        $this->updateCurrencyRates();
    }

    /**
     * Update currency rates
     */
    protected function updateCurrencyRates()
    {
        $settings = $this->module->getSettings('currency');
        if (!empty($settings['status'])) {
            $this->getCurrencyModel()->update($settings);
        }
    }

    /**
     * Returns Currency model instance
     * @return \gplcart\modules\currency\models\Currency
     */
    protected function getCurrencyModel()
    {
        return Container::get('gplcart\\modules\\currency\\models\\Currency');
    }

}
