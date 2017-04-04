<?php

/**
 * @package Currency 
 * @author Iurii Makukh <gplcart.software@gmail.com> 
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com> 
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0 
 */

namespace gplcart\modules\currency\controllers;

use gplcart\core\models\Module as ModuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\modules\currency\models\Currency as CurrencyModuleCurrencyModel;

/**
 * Handles incoming requests and outputs data related to Currency module settings
 */
class Settings extends BackendController
{

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * Currency model instance
     * @var \gplcart\modules\currency\models\Currency $currency_module_model
     */
    protected $currency_module_model;

    /**
     * @param ModuleModel $module
     * @param CurrencyModuleCurrencyModel $currency
     */
    public function __construct(ModuleModel $module,
            CurrencyModuleCurrencyModel $currency)
    {
        parent::__construct();

        $this->module = $module;
        $this->currency_module_model = $currency;
    }

    /**
     * Route page callback to display the module settings page
     */
    public function editSettings()
    {
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();
        $this->setData('settings', $this->config->module('currency'));
        $this->submitSettings();
        $this->outputEditSettings();
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('save') && $this->validateSettings()) {
            $this->updateSettings();
        }
    }

    /**
     * Validates an array of submitted values
     * @return array
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');
        $this->setSubmittedBool('status');

        $interval = $this->getSubmitted('interval');
        $correction = $this->getSubmitted('correction');
        $derivation = $this->getSubmitted('derivation');

        if (filter_var($interval, FILTER_VALIDATE_INT) === false) {
            $this->setError('interval', $this->text('Interval must be integer value'));
        }

        if (!is_numeric($correction)) {
            $this->setError('correction', $this->text('Correction must be either positive or negative numeric value'));
        }

        $parts = array_map('trim', explode(',', $derivation));
        $numeric = array_filter($parts, 'is_numeric');

        if (count($parts) != count($numeric) || count($parts) != 4) {
            $this->setError('derivation', $this->text('Derivation must contain exactly 4 positive numbers separated by comma'));
        }

        if ($this->hasErrors()) {
            return false;
        }

        $this->setSubmitted('derivation', $parts);
        return true;
    }

    /**
     * Updates module settings
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('currency', $this->getSubmitted());

        if ($this->getSubmitted('update')) {
            $results = $this->updateRateSettings();
            if (!empty($results)) {
                $vars = array('@list' => implode(',', array_keys($results)));
                $message = $this->text('Updated the following currencies: @list', $vars);
                $this->setMessage($message, 'success', true);
            }
        }

        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Updates currency rates
     * @return array
     */
    protected function updateRateSettings()
    {
        $this->currency_module_model->setSettings($this->getSubmitted());
        return $this->currency_module_model->update();
    }

    /**
     * Set title on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $vars = array('%name' => $this->text('Currency'));
        $title = $this->text('Edit %name settings', $vars);
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module settings page
     */
    protected function setBreadcrumbEditSettings()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the module settings page
     */
    protected function outputEditSettings()
    {
        $this->output('currency|settings');
    }

}
