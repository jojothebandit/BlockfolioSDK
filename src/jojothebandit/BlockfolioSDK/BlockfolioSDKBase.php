<?php

namespace jojothebandit\BlockfolioSDK;


use Exception;
use InvalidArgumentException;

class BlockfolioSDKBase
{
    const API_VERSION = 'api-v0';
    const API_HOST = 'blockfolio.com';
    const BTC = 'BTC';
    const SUCCESS_STRING = 'success';
    const EXCHANGE_RATE_ACTION = 'exchangeRate';
    const USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_0 like Mac OS X) AppleWebKit/602.1.38 (KHTML, like Gecko) Version/10.0 Mobile/14A5297c Safari/602.1';


    protected $apiUrlFormats = [
        'buy' => 'https://%apiVersion$s.%apiHost$s/rest/%restMethod$s/%clientId$s/%unknownNumber$s/%destinationCrypto$s/%sourceCrypto$s/%exchangeName$s/%exchangeRate$s/%amount$s/%time$s/0?platform=%platform$s&note=%note$s',
        'sell' => 'https://%apiVersion$s.%apiHost$s/rest/%restMethod$s/%clientId$s/%unknownNumber$s/%destinationCrypto$s/%sourceCrypto$s/%exchangeName$s/%exchangeRate$s/%amount$s/%time$s/0?platform=%platform$s&note=%note$s',
        self::EXCHANGE_RATE_ACTION => 'https://%apiVersion$s.%apiHost$s/rest/%restMethod$s/%exchangeName$s/%sourceCrypto$s-%destinationCrypto$s?locale=%locale$s',
        'portfolio' => 'https://%apiVersion$s.%apiHost$s/rest/%restMethod$s/%clientId$s?fiat_currency=USD&locale=en-HR',
        'coinList' => 'https://%s.%s/rest/%s',
        'exchangeList' => 'https://%apiVersion$s.%apiHost$s/rest/%restMethod$s/%sourceCrypto$s-%destinationCrypto$s',
    ];
    protected $restMethods = [
        'buy' => 'add_position_v2',
        'sell' => 'add_position_v2',
        'exchangeRate' => 'lastprice',
        'exchangeList' => 'exchangelist',
        'coinList' => 'coinlist',
        'portfolio' => 'get_all_positions',
    ];

    protected $curlOptions = [
        CURLOPT_RETURNTRANSFER  => true,                // return web page
        CURLOPT_HEADER          => false,               // don't return headers
        CURLOPT_FOLLOWLOCATION  => true,                // follow redirects
        CURLOPT_ENCODING        => "utf-8",             // handle all encodings
        CURLOPT_CONNECTTIMEOUT  => 120,                 // timeout on connect
        CURLOPT_TIMEOUT         => 120,                 // timeout on response
        CURLOPT_POST            => 0,                   // i am sending post data
        CURLOPT_SSL_VERIFYHOST  => 0,                   // don't verify ssl
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_USERAGENT       => self::USER_AGENT
    ];

    protected $apiVersion = 'v0';
    protected $platform = 'ios';
    protected $action = null;
    protected $method = 'get';
    protected $sourceCrypto = self::BTC;
    protected $destinationCrypto = null;
    protected $clientId = null;
    protected $unknownNumber = 1;
    protected $exchangeName = null;
    protected $amount = null;
    protected $note = null;
    protected $requestUrl = null;
    protected $exchangeRate = null;
    protected $time = null;
    protected $locale = 'en-HR';

    protected $portfolio = null;

    protected $nonJsonResponses = [
        'buy' => true,
        'sell'  => true,
    ];
    protected $exchangeRateNeeded = [
        'buy' => true,
        'sell'  => true,
    ];
    protected $exchangeNameNeeded = [
        'buy' => true,
        'sell'  => true,
        self::EXCHANGE_RATE_ACTION  => true,
    ];

    /**
     * @param $str
     * @param $args
     * @return string
     */
    function vksprintf($str, $args)
    {
        $map = array_flip(array_keys($args));
        $newStr = preg_replace_callback('/(^|[^%])%([a-zA-Z0-9_-]+)\$/',
            function($m) use ($map) {
                return $m[1] . '%' . ($map[$m[2]] + 1). '$';
            },
            $str);

        return vsprintf($newStr, $args);
    }

    /**
     * @return string
     */
    protected function getApiVersion() {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     * @return BlockfolioSDKBase
     */
    public function setApiVersion($apiVersion) {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatform() {
        return $this->platform;
    }

    /**
     * @param string $platform
     * @return BlockfolioSDKBase
     */
    public function setPlatform($platform) {
        $this->platform = $platform;
        return $this;
    }

    /**
     * @return null
     */
    public function getSourceCrypto() {
        return $this->sourceCrypto;
    }

    /**
     * @param null $sourceCrypto
     * @return BlockfolioSDKBase
     */
    public function setSourceCrypto($sourceCrypto) {
        $this->sourceCrypto = $sourceCrypto;
        if ($this->sourceCrypto === null){
            $this->sourceCrypto = self::BTC;
        }

        return $this;
    }

    /**
     * @return null
     */
    public function getDestinationCrypto() {
        return $this->destinationCrypto;
    }

    /**
     * @param string $destinationCrypto
     * @return BlockfolioSDKBase
     */
    public function setDestinationCrypto($destinationCrypto) {
        if ($destinationCrypto !== null){
            $this->destinationCrypto = $destinationCrypto;
        }

        if ($this->destinationCrypto === null){
            throw new InvalidArgumentException('Destination Crypto not set.');
        }

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getClientId() {
        if ($this->clientId === null){
            throw new Exception('Invalid clientId');
        }
        return $this->clientId;
    }

    /**
     * @param $clientId
     * @return $this
     */
    public function setClientId($clientId) {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string
     */
    protected function getExchangeName() {
        if ($this->exchangeName === null){
            $blockfolio = new self;
            $blockfolio->setSourceCrypto($this->getSourceCrypto());
            $blockfolio->setDestinationCrypto($this->getDestinationCrypto());
            $blockfolio->setClientID($this->getClientId());
            $blockfolio->setAction('exchangeList');
            $result = $blockfolio->apiRequest();
            if (empty($result['exchange'])){
                throw new Exception('Couldn\'t find an exchange for ' . $this->getSourceCrypto() . '-' .  $this->getDestinationCrypto() . ', try to swap your parameters');
            } else {
                $this->exchangeName = $result['exchange'][0];
            }
        }
        return $this->exchangeName;
    }

    /**
     * @param string $exchangeName
     * @return BlockfolioSDKBase
     */
    public function setExchangeName($exchangeName = null) {
        $this->exchangeName = $exchangeName;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return BlockfolioSDKBase
     */
    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote() {
        return $this->note;
    }

    /**
     * @param string $note
     * @return BlockfolioSDKBase
     */
    public function setNote($note) {
        $this->note = $note;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getRestMethod(){
        return $this->restMethods[$this->getAction()];
    }

    /**
     * @return array
     */
    protected function getParamsForUrl()
    {
        $params = [
            'apiVersion' => self::API_VERSION,
            'apiHost' => self::API_HOST,
            'restMethod' => $this->getRestMethod(),
            'clientId' => $this->getClientId(),
            'unknownNumber' => $this->getUnknownNumber(),
            'destinationCrypto' => $this->getDestinationCrypto(),
            'sourceCrypto' => $this->getSourceCrypto(),
            'amount' => $this->getAmount(),
            'time' => $this->getTime(),
            'platform' => $this->getPlatform(),
            'locale' => $this->getLocale(),
            'note' => $this->getNote(),
        ];

        if (isset($this->exchangeRateNeeded[$this->getAction()])){
            $params['exchangeRate'] = $this->getExchangeRate();
        }
        if (isset($this->exchangeNameNeeded[$this->getAction()])){
            $params['exchangeName'] = $this->getExchangeName();
        }

        return $params;
    }

    /**
     *
     */
    protected function generateRequestUrl()
    {
        $this->requestUrl =
            $this->vksprintf(
                $this->getApiUrl($this->getAction()),
                $this->getParamsForUrl()
            );
    }

    /**
     * @param $response
     * @return bool|mixed
     * @throws Exception
     */
    protected function unpackRequest($response)
    {
        $jsonResponse = json_decode($response, true);

        if (is_array($jsonResponse)){
            return $jsonResponse;
        } else {
            if (isset($this->nonJsonResponses[$this->getAction()]) && $response == self::SUCCESS_STRING){
                return true;
            } else {
                throw new Exception('Unable to parse response for url: "' . $this->getRequestUrl() . '"');
            }
        }
    }

    /**
     * @return bool|mixed
     */
    protected function apiRequest()
    {
        $this->generateRequestUrl();
        $ch = curl_init($this->getRequestUrl());
        curl_setopt_array($ch, $this->curlOptions);
        $content = curl_exec($ch);
        curl_close($ch);
        return $this->unpackRequest($content);
    }

    /**
     * @param float $exchangeRate
     */
    protected function setExchangeRate($exchangeRate) {
        $this->exchangeRate = $exchangeRate;
    }


    /**
     * @return string
     */
    protected function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * @return null
     */
    public function getRequestUrl() {
        return $this->requestUrl;
    }


    /**
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return BlockfolioSDKBase
     */
    public function setLocale($locale) {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime() {
        return round(microtime(true) * 1000);
    }

    /**
     * @param $action
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function getApiUrl($action) {
        if (isset($this->apiUrlFormats[$action])){
            return $this->apiUrlFormats[$action];
        }
        throw new InvalidArgumentException('Unknown action specified: ' . $action);
    }

    /**
     * @return int
     */
    protected function getUnknownNumber() {
        return $this->unknownNumber;
    }

    /**
     * @return string
     */
    public function getExchangeRate($source = null, $destination = null)
    {
        if ($this->exchangeRate === null){
            $this->setSourceCrypto($source);
            $this->setDestinationCrypto($destination);

            $this->calculateExchangeRate();
        }
        return $this->exchangeRate;
    }

    /**
     *
     */
    protected function calculateExchangeRate() {
        $blockfolio = new self();
        $blockfolio->setSourceCrypto($this->getSourceCrypto());
        $blockfolio->setDestinationCrypto($this->getDestinationCrypto());
        $blockfolio->setClientID($this->getClientId());
        $blockfolio->setAction('exchangeRate');

        $result = $blockfolio->apiRequest();
        $result = (string)$result['last'];

        $this->setExchangeRate($result);
    }
}