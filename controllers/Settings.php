<?php

/**
 * @package Currency
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 */

namespace gplcart\modules\currency\controllers;

use gplcart\core\controllers\backend\Controller;
use gplcart\modules\currency\models\Currency;

/**
 * Handles incoming requests and outputs data related to Currency module settings
 */
class Settings extends Controller
{

    /**
     * Currency model instance
     * @var \gplcart\modules\currency\models\Currency $currency_model
     */
    protected $currency_model;

    /**
     * @param Currency $currency
     */
    public function __construct(Currency $currency)
    {
        parent::__construct();

        $this->currency_model = $currency;
    }

    /**
     * Route page callback to display the module settings page
     */
    public function editSettings()
    {
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();
        $this->setData('settings', $this->module->getSettings('currency'));
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
     * @return bool
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');
        $this->setSubmittedBool('status');

        $this->validateElement('interval', 'integer');
        $this->validateElement('correction', 'numeric');

        $parts = array_map('trim', explode(',', $this->getSubmitted('derivation')));

        if (count($parts) != count(array_filter($parts, 'is_numeric')) || count($parts) != 4) {
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
            if (empty($results)) {
                $severity = 'warning';
                $message = $this->text('Currencies have not been updated');
            } else {
                $severity = 'success';
                $vars = array('@list' => implode(',', array_keys($results)));
                $message = $this->text('Updated the following currencies: @list', $vars);
            }

            $this->setMessage($message, $severity, true);
        }

        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Updates currency rates
     * @return array
     */
    protected function updateRateSettings()
    {
        $this->controlAccess('currency_edit');

        return $this->currency_model->update($this->getSubmitted());
    }

    /**
     * Set title on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $title = $this->text('Edit %name settings', array('%name' => $this->text('Currency')));
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
