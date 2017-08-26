# Blockfolio unofficial PHP SDK
<br />
A php wrapper around some Blockfolio requests
<br>
Now you can edit your crypto values in the app through this little SDK
<br>
DISCLAIMER: Since this is totally unofficial it can stop working at any time
<br>
I will try to keep up and fix stuff if it breaks, but I am not promising anything
<br>

# How to install (Composer)

    composer require "jojothebandit/blockfolio-sdk":"dev-master"
    
or add to your project's composer.json

    "require": {
        "jojothebandit/blockfolio-sdk": "~1.0"
    }

# How to use
Find your client id in the settings part of Blockfolio
<br>
Instantiate the SDK with a client id
```php
    use jojothebandit\BlockfolioSDK\BlockfolioSDK;

    $blockfolio = new BlockfolioSDK();
    $blockfolio->setClientId('YOUR_CLIENT_ID');

```

<br>

Get your whole portfolio in JSON
```php
    $blockfolio->getPortfolio()
```
<br>
Get value of your portfolio(default currency is USD)

```php
    $blockfolio->getPortfolioValue('USD')
```
<br>
Get quantity of coins of a specific crypto

```php
    $blockfolio->getQuantity('ETH')
```

<br>
Add a "sell" transaction

```php
    $blockfolio->sell(0.12, 'BTC');
```
    
<br>
Add a "buy" transaction

```php
    $blockfolio->buy(0.67, 'ETH');
```
        
<br>
Add a sell transaction, with the amount being the amount you have currently in the app for a specific crypto

```php
    $blockfolio->sellAll('ETH');
```
<br>
Make a trade, does two transactions, removes x of the first crypto, and adds y of the second

```php
    $blockfolio->trade(5, 'LTC', 3, 'BTC'));
```

<br>
Find the exchange rate between two cryptos, mostly goes one way, BTC being the source crypto

```php
    $blockfolio->getExchangeRate('BTC', 'ETH');
```

<br>
You can set the exchange with

```php
    $blockfolio->setExchangeName('poloniex');
```

<br>
You can internally set the source and destination crypto

```php
    $blockfolio->setSourceCrypto('BTC');
    $blockfolio->setDestinationCrypto('BCC');
```

# Contribute

Contributions are more than welcome :) <br />

# Questions, problems?

I'll do my best to answer all issues

# License
[MIT License](LICENSE)
