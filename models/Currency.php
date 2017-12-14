<?php

/**
 * @package Currency
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\currency\models;

use gplcart\core\Logger;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\helpers\SocketClient as SocketHelper;

/**
 * Methods to update currencies with data from Yahoo Finance feed
 */
class Currency
{

    /**
     * Yahoo API endpoint
     */
    const URL = 'https://query.yahooapis.com/v1/public/yql';

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
     * Socket client class instance
     * @var \gplcart\core\helpers\SocketClient $socket
     */
    protected $socket;

    /**
     * @param Logger $logger
     * @param CurrencyModel $currency
     * @param SocketHelper $socket
     */
    public function __construct(Logger $logger, CurrencyModel $currency, SocketHelper $socket)
    {
        $this->logger = $logger;
        $this->socket = $socket;
        $this->currency = $currency;
    }

    /**
     * Performs GET request to Yahoo API
     * @param array $query
     * @return array
     */
    protected function request(array $query)
    {
        try {
            $response = $this->socket->request(static::URL, array('query' => $query));
            $data = json_decode($response['data'], true);
        } catch (\Exception $ex) {
            $this->logger->log('module_currency', $ex->getMessage(), 'warning');
            return array();
        }

        if (empty($data['query']['results']['rate'])) {
            $this->logger->log('module_currency', 'Failed to get currency rates from Yahoo Finance', 'warning');
            return array();
        }

        return $data['query']['results']['rate'];
    }

    /**
     * Updated store currencies
     * @param array $settings
     * @return array
     */
    public function update(array $settings)
    {
        $codes = $this->getCandidates($settings);

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

            if ($this->shouldUpdateRate($result['Rate'], $list[$code], $settings)) {
                $rate = $this->prepareRate($result['Rate'], $settings);
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
     * @param array $settings
     * @return float
     */
    protected function prepareRate($rate, array $settings)
    {
        if (!empty($settings['correction'])) {
            $rate *= (1 + (float) $settings['correction'] / 100);
        }

        return $rate;
    }

    /**
     * Whether the currency should be updated
     * @param float $value
     * @param array $currency
     * @param array $settings
     * @return bool
     */
    protected function shouldUpdateRate($value, $currency, array $settings)
    {
        if (!empty($settings['update'])) {
            return true;
        }

        list($min_max, $min_min, $max_min, $max_max) = $settings['derivation'];

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
     * @param array $settings
     * @return array
     */
    public function getCandidates(array $settings)
    {
        $list = $this->currency->getList();

        if (!empty($settings['currencies'])) {
            $list = array_intersect_key(array_flip($settings['currencies']), $list);
        }

        foreach ($list as $code => $currency) {

            if (!empty($currency['default']) || empty($currency['status'])) {
                unset($list[$code]);
                continue;
            }

            if (!empty($settings['update'])) {
                continue; // Force updating
            }

            if (!empty($settings['interval']) && (GC_TIME - $currency['modified']) < (int) $settings['interval']) {
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
            $code = "\"$code$base\"";
        });

        $list = implode(',', $codes);

        return array(
            'format' => 'json',
            'env' => 'store://datatables.org/alltableswithkeys',
            'q' => "select * from yahoo.finance.xchange where pair in($list)"
        );
    }

}
