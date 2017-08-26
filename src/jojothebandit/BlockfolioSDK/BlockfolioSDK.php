<?php

namespace jojothebandit\BlockfolioSDK;

use Exception;
use InvalidArgumentException;

class BlockfolioSDK extends BlockfolioSDKBase
{
     /**
     * @param $amount
     * @param null $destinationCrypto
     * @param null $sourceCrypto
     * @return bool|mixed
     */
    public function buy($amount, $destinationCrypto = null, $sourceCrypto = null)
    {
        $this->setAction('buy');
        $this->setDestinationCrypto($destinationCrypto);
        $this->setSourceCrypto($sourceCrypto);
        $this->setAmount($amount);
        $this->getExchangeRate();
        return $this->apiRequest();
    }

    /**
     * @param $amount
     * @param null $destinationCrypto
     * @param null $sourceCrypto
     * @return bool|mixed
     */
    public function sell($amount, $destinationCrypto = null, $sourceCrypto = null)
    {
        $this->setAction('sell');
        $this->setDestinationCrypto($destinationCrypto);
        $this->setSourceCrypto($sourceCrypto);
        $this->setAmount($amount * -1);
        $this->getExchangeRate();
        return $this->apiRequest();
    }

    /**
     * @param $amountDestinationCrypto
     * @param $destinationCrypto
     * @param $amountSourceCrypto
     * @param $sourceCrypto
     * @return array
     */
    public function trade($amountSourceCrypto, $sourceCrypto, $amountDestinationCrypto, $destinationCrypto)
    {
        $sale = new self();
        $got = new self();

        $sale->setClientId($this->getClientId())->sell($amountSourceCrypto, $sourceCrypto);
        $got->setClientId($this->getClientId())->buy($amountDestinationCrypto, $destinationCrypto);

        return [
            'message' => 'Traded ' . $amountSourceCrypto . ' ' . $sourceCrypto . ' for ' . $amountDestinationCrypto . ' ' . $destinationCrypto
        ];
    }

    /**
     * @param $crypto
     * @return array
     * @throws Exception
     */
    public function sellAll($crypto)
    {
        $amount = $this->getQuantity($crypto);
        if ($amount === null && $amount <= 0) {
            throw new Exception('Sell amount invalid: ' . $amount);
        }

        $this->sell($amount, $crypto);
        return [
            'message' => 'Sold all of ' . $crypto . ' - ' . $amount
        ];
    }

    /**
     * @return bool|mixed
     */
    public function getPortfolio()
    {
        if ($this->portfolio !== null){
            return $this->portfolio;
        }
        $this->setAction('portfolio');
        return $this->apiRequest();
    }

    /**
     * @param string $currency
     * @return mixed
     */
    public function getPortfolioValue($currency = 'usd')
    {
        $portfolio = $this->getPortfolio();
        $wanted = strtolower($currency) . 'Value';

        if ( isset($portfolio['portfolio']) && isset($portfolio['portfolio'][$wanted]) ){
            return $portfolio['portfolio'][$wanted];
        } else {
            throw new InvalidArgumentException('Value for currency ' . $currency . ' not found');
        }
    }

    /**
     * @param $crypto
     * @return float
     */
    public function getQuantity($crypto)
    {
        $portfolio = $this->getPortfolio();

        if ( isset($portfolio['positionList']) ){
            foreach ((array)$portfolio['positionList'] as $position) {
                if ($position['coin'] == $crypto ){
                    return $position['quantity'];
                }
            }
        }

        return null;
    }
}