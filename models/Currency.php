<?php

/**
 * @package Currency
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\currency\models;

use gplcart\core\Model,
    gplcart\core\Logger;
use gplcart\core\helpers\Curl as CurlHelper;
use gplcart\core\models\Currency as CurrencyModel;

/**
 * Methods to update currencies with data from Yahoo Finance feed
 */
class Currency extends Model
{

    /**
     * Yahoo API endpoint
     */
    const API_ENDPOINT = 'https://query.yahooapis.com/v1/public/yql';

    /**
     * Curl helper class instance
     * @var \gplcart\core\helpers\Curl $curl
     */
    protected $curl;

    /**
     * Currency model class instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * The module settings
     * @var array
     */
    protected $settings = array();

    /**
     * @param Logger $logger
     * @param CurrencyModel $currency
     * @param CurlHelper $curl
     */
    public function __construct(Logger $logger, CurrencyModel $currency,
            CurlHelper $curl)
    {
        parent::__construct();

        $this->curl = $curl;
        $this->logger = $logger;
        $this->currency = $currency;
    }

    /**
     * Set module settings
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Performs GET request to Yahoo API
     * @param array $query
     * @return array
     */
    protected function request(array $query)
    {
        try {
            $response = $this->curl->get(static::API_ENDPOINT, array('query' => $query));
            $data = json_decode($response, true);
        } catch (\Exception $ex) {
            return array();
        }

        $error = $this->curl->getError();

        if (!empty($error)) {
            $this->logger->log('module_currency', $error, 'warning');
            return array();
        }

        if (empty($data['query']['results']['rate'])) {
            $this->logger->log('module_currency', 'Wrong format of Yahoo Finance API response', 'warning');
            return array();
        }

        return $data['query']['results']['rate'];
    }

    /**
     * Updated store currencies
     * @return array
     */
    public function update()
    {
        $codes = $this->getCandidates();

        if (empty($codes)) {
            return array();
        }

        $list = $this->currency->getList();
        $base = $this->currency->getDefault();
        $results = $this->request($this->buildQuery($codes));

        $updated = array();
        foreach ($results as $result) {

            $code = preg_replace("/$base$/", '', $result['id']);
            if ($code == $base || empty($list[$code]) || empty($result['Rate']) || $result['Rate'] == 0) {
                continue;
            }

            if ($this->shouldUpdateRate($result['Rate'], $list[$code], $this->settings['derivation'])) {
                $rate = $this->prepareRate($result['Rate'], $list[$code]);
                $updated[$code] = $rate;
                $this->currency->update($code, array('conversion_rate' => $rate));
            }
        }

        if (empty($updated)) {
            return array();
        }

        $log = array(
            'message' => 'Updated the following currencies: @list',
            'variables' => array('@list' => implode(',', array_keys($updated)))
        );

        $this->logger->log('module_currency', $log);
        return $updated;
    }

    /**
     * Prepares rate value before updating
     * @param float $rate
     * @param array $currency
     * @return float
     */
    protected function prepareRate($rate, array $currency)
    {
        if (!empty($this->settings['correction'])) {
            $rate *= (1 + (float) $this->settings['correction'] / 100);
        }
        return $rate;
    }

    /**
     * Whether the currency should be updated
     * @param float $value
     * @param array $currency
     * @return bool
     */
    protected function shouldUpdateRate($value, $currency, array $derivation)
    {
        if (!empty($this->settings['update'])) {
            return true;
        }

        list($min_max, $min_min, $max_min, $max_max) = $derivation;

        $diff = (1 - ($currency['conversion_rate'] / $value)) * 100;
        $diffabs = abs($diff);

        if ($diff > 0) {
            return ($max_min <= $diffabs) && ($diffabs <= $max_max);
        }
        if ($diff < 0) {
            return ($min_min <= $diffabs) && ($diffabs <= $min_max);
        }
        return false;
    }

    /**
     * Returns an array of currency codes to be updated
     * @return array
     */
    public function getCandidates()
    {
        $list = $this->currency->getList();

        if (!empty($this->settings['currencies'])) {
            $list = array_intersect_key(array_flip($this->settings['currencies']), $list);
        }

        foreach ($list as $code => $currency) {

            if (!empty($currency['default'])) {
                unset($list[$code]);
                continue;
            }

            if (!empty($this->settings['update'])) {
                continue; // Force updating
            }

            if (!empty($this->settings['interval'])//
                    && (GC_TIME - $currency['modified']) < (int) $this->settings['interval']) {
                unset($list[$code]);
            }
        }

        return array_keys($list);
    }

    /**
     * Returns an array of query data
     * @param array $codes
     * @return array
     */
    protected function buildQuery(array $codes)
    {
        $base = $this->currency->getDefault();

        array_walk($codes, function(&$code) use($base) {
            $code = "$code$base";
        });

        return array(
            'format' => 'json',
            'env' => 'store://datatables.org/alltableswithkeys',
            'q' => 'SELECT * FROM yahoo.finance.xchange WHERE pair="' . implode(',', $codes) . '"'
        );
    }

}
