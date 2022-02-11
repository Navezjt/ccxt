<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception; // a common import
use \ccxt\ExchangeError;

class zaif extends Exchange {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'zaif',
            'name' => 'Zaif',
            'countries' => array( 'JP' ),
            'rateLimit' => 2000,
            'version' => '1',
            'has' => array(
                'CORS' => null,
                'spot' => true,
                'margin' => null, // has but unimplemented
                'swap' => false,
                'future' => false,
                'option' => false,
                'cancelOrder' => true,
                'createMarketOrder' => null,
                'createOrder' => true,
                'fetchBalance' => true,
                'fetchClosedOrders' => true,
                'fetchFundingHistory' => false,
                'fetchFundingRate' => false,
                'fetchFundingRateHistory' => false,
                'fetchFundingRates' => false,
                'fetchIndexOHLCV' => false,
                'fetchMarkets' => true,
                'fetchMarkOHLCV' => false,
                'fetchOpenOrders' => true,
                'fetchOrderBook' => true,
                'fetchPremiumIndexOHLCV' => false,
                'fetchTicker' => true,
                'fetchTrades' => true,
                'withdraw' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766927-39ca2ada-5eeb-11e7-972f-1b4199518ca6.jpg',
                'api' => 'https://api.zaif.jp',
                'www' => 'https://zaif.jp',
                'doc' => array(
                    'https://techbureau-api-document.readthedocs.io/ja/latest/index.html',
                    'https://corp.zaif.jp/api-docs',
                    'https://corp.zaif.jp/api-docs/api_links',
                    'https://www.npmjs.com/package/zaif.jp',
                    'https://github.com/you21979/node-zaif',
                ),
                'fees' => 'https://zaif.jp/fee?lang=en',
            ),
            'fees' => array(
                'trading' => array(
                    'percentage' => true,
                    'taker' => $this->parse_number('0.001'),
                    'maker' => $this->parse_number('0'),
                ),
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'depth/{pair}',
                        'currencies/{pair}',
                        'currencies/all',
                        'currency_pairs/{pair}',
                        'currency_pairs/all',
                        'last_price/{pair}',
                        'ticker/{pair}',
                        'trades/{pair}',
                    ),
                ),
                'private' => array(
                    'post' => array(
                        'active_orders',
                        'cancel_order',
                        'deposit_history',
                        'get_id_info',
                        'get_info',
                        'get_info2',
                        'get_personal_info',
                        'trade',
                        'trade_history',
                        'withdraw',
                        'withdraw_history',
                    ),
                ),
                'ecapi' => array(
                    'post' => array(
                        'createInvoice',
                        'getInvoice',
                        'getInvoiceIdsByOrderNumber',
                        'cancelInvoice',
                    ),
                ),
                'tlapi' => array(
                    'post' => array(
                        'get_positions',
                        'position_history',
                        'active_positions',
                        'create_position',
                        'change_position',
                        'cancel_position',
                    ),
                ),
                'fapi' => array(
                    'get' => array(
                        'groups/{group_id}',
                        'last_price/{group_id}/{pair}',
                        'ticker/{group_id}/{pair}',
                        'trades/{group_id}/{pair}',
                        'depth/{group_id}/{pair}',
                    ),
                ),
            ),
            'options' => array(
                // zaif schedule defines several market-specific fees
                'fees' => array(
                    'BTC/JPY' => array( 'maker' => 0, 'taker' => 0 ),
                    'BCH/JPY' => array( 'maker' => 0, 'taker' => 0.3 / 100 ),
                    'BCH/BTC' => array( 'maker' => 0, 'taker' => 0.3 / 100 ),
                    'PEPECASH/JPY' => array( 'maker' => 0, 'taker' => 0.01 / 100 ),
                    'PEPECASH/BT' => array( 'maker' => 0, 'taker' => 0.01 / 100 ),
                ),
            ),
            'exceptions' => array(
                'exact' => array(
                    'unsupported currency_pair' => '\\ccxt\\BadRequest', // array("error" => "unsupported currency_pair")
                ),
                'broad' => array(
                ),
            ),
        ));
    }

    public function fetch_markets($params = array ()) {
        $markets = $this->publicGetCurrencyPairsAll ($params);
        //
        //     array(
        //         {
        //             "aux_unit_point" => 0,
        //             "item_japanese" => "\u30d3\u30c3\u30c8\u30b3\u30a4\u30f3",
        //             "aux_unit_step" => 5.0,
        //             "description" => "\u30d3\u30c3\u30c8\u30b3\u30a4\u30f3\u30fb\u65e5\u672c\u5186\u306e\u53d6\u5f15\u3092\u884c\u3046\u3053\u3068\u304c\u3067\u304d\u307e\u3059",
        //             "item_unit_min" => 0.001,
        //             "event_number" => 0,
        //             "currency_pair" => "btc_jpy",
        //             "is_token" => false,
        //             "aux_unit_min" => 5.0,
        //             "aux_japanese" => "\u65e5\u672c\u5186",
        //             "id" => 1,
        //             "item_unit_step" => 0.0001,
        //             "name" => "BTC/JPY",
        //             "seq" => 0,
        //             "title" => "BTC/JPY"
        //         }
        //     )
        //
        $result = array();
        for ($i = 0; $i < count($markets); $i++) {
            $market = $markets[$i];
            $id = $this->safe_string($market, 'currency_pair');
            $name = $this->safe_string($market, 'name');
            list($baseId, $quoteId) = explode('/', $name);
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $fees = $this->safe_value($this->options['fees'], $symbol, $this->fees['trading']);
            $itemUnitStep = $this->safe_string($market, 'item_unit_step');
            $result[] = array(
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'settle' => null,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'settleId' => null,
                'type' => 'spot',
                'spot' => true,
                'margin' => null,
                'swap' => false,
                'future' => false,
                'option' => false,
                'active' => null, // can trade or not
                'contract' => false,
                'linear' => null,
                'inverse' => null,
                'taker' => $fees['taker'],
                'maker' => $fees['maker'],
                'contractSize' => null,
                'expiry' => null,
                'expiryDatetime' => null,
                'strike' => null,
                'optionType' => null,
                'precision' => array(
                    'amount' => Precise::string_mul($itemUnitStep, '-1e10'),
                    'price' => $this->safe_integer($market, 'aux_unit_point'),
                ),
                'limits' => array(
                    'leverage' => array(
                        'min' => null,
                        'max' => null,
                    ),
                    'amount' => array(
                        'min' => $this->safe_number($market, 'item_unit_min'),
                        'max' => null,
                    ),
                    'price' => array(
                        'min' => $this->safe_number($market, 'aux_unit_min'),
                        'max' => null,
                    ),
                    'cost' => array(
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function parse_balance($response) {
        $balances = $this->safe_value($response, 'return', array());
        $deposit = $this->safe_value($balances, 'deposit');
        $result = array(
            'info' => $response,
            'timestamp' => null,
            'datetime' => null,
        );
        $funds = $this->safe_value($balances, 'funds', array());
        $currencyIds = is_array($funds) ? array_keys($funds) : array();
        for ($i = 0; $i < count($currencyIds); $i++) {
            $currencyId = $currencyIds[$i];
            $code = $this->safe_currency_code($currencyId);
            $balance = $this->safe_string($funds, $currencyId);
            $account = $this->account();
            $account['free'] = $balance;
            $account['total'] = $balance;
            if ($deposit !== null) {
                if (is_array($deposit) && array_key_exists($currencyId, $deposit)) {
                    $account['total'] = $this->safe_string($deposit, $currencyId);
                }
            }
            $result[$code] = $account;
        }
        return $this->safe_balance($result);
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetInfo ($params);
        return $this->parse_balance($response);
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'pair' => $this->market_id($symbol),
        );
        $response = $this->publicGetDepthPair (array_merge($request, $params));
        return $this->parse_order_book($response, $symbol);
    }

    public function parse_ticker($ticker, $market = null) {
        //
        // {
        //     "last" => 9e-08,
        //     "high" => 1e-07,
        //     "low" => 9e-08,
        //     "vwap" => 0.0,
        //     "volume" => 135250.0,
        //     "bid" => 9e-08,
        //     "ask" => 1e-07
        // }
        //
        $symbol = $this->safe_symbol(null, $market);
        $timestamp = $this->milliseconds();
        $vwap = $this->safe_string($ticker, 'vwap');
        $baseVolume = $this->safe_string($ticker, 'volume');
        $quoteVolume = Precise::string_mul($baseVolume, $vwap);
        $last = $this->safe_string($ticker, 'last');
        return $this->safe_ticker(array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => $this->safe_string($ticker, 'high'),
            'low' => $this->safe_string($ticker, 'low'),
            'bid' => $this->safe_string($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_string($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        ), $market, false);
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
        );
        $ticker = $this->publicGetTickerPair (array_merge($request, $params));
        //
        // {
        //     "last" => 9e-08,
        //     "high" => 1e-07,
        //     "low" => 9e-08,
        //     "vwap" => 0.0,
        //     "volume" => 135250.0,
        //     "bid" => 9e-08,
        //     "ask" => 1e-07
        // }
        //
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade($trade, $market = null) {
        $side = $this->safe_string($trade, 'trade_type');
        $side = ($side === 'bid') ? 'buy' : 'sell';
        $timestamp = $this->safe_timestamp($trade, 'date');
        $id = $this->safe_string_2($trade, 'id', 'tid');
        $priceString = $this->safe_string($trade, 'price');
        $amountString = $this->safe_string($trade, 'amount');
        $price = $this->parse_number($priceString);
        $amount = $this->parse_number($amountString);
        $cost = $this->parse_number(Precise::string_mul($priceString, $amountString));
        $marketId = $this->safe_string($trade, 'currency_pair');
        $symbol = $this->safe_symbol($marketId, $market, '_');
        return array(
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'order' => null,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'pair' => $market['id'],
        );
        $response = $this->publicGetTradesPair (array_merge($request, $params));
        $numTrades = is_array($response) ? count($response) : 0;
        if ($numTrades === 1) {
            $firstTrade = $response[0];
            if (!$firstTrade) {
                $response = array();
            }
        }
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        if ($type !== 'limit') {
            throw new ExchangeError($this->id . ' allows limit orders only');
        }
        $request = array(
            'currency_pair' => $this->market_id($symbol),
            'action' => ($side === 'buy') ? 'bid' : 'ask',
            'amount' => $amount,
            'price' => $price,
        );
        $response = $this->privatePostTrade (array_merge($request, $params));
        return array(
            'info' => $response,
            'id' => (string) $response['return']['order_id'],
        );
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $request = array(
            'order_id' => $id,
        );
        return $this->privatePostCancelOrder (array_merge($request, $params));
    }

    public function parse_order($order, $market = null) {
        //
        //     {
        //         "currency_pair" => "btc_jpy",
        //         "action" => "ask",
        //         "amount" => 0.03,
        //         "price" => 56000,
        //         "timestamp" => 1402021125,
        //         "comment" : "demo"
        //     }
        //
        $side = $this->safe_string($order, 'action');
        $side = ($side === 'bid') ? 'buy' : 'sell';
        $timestamp = $this->safe_timestamp($order, 'timestamp');
        $marketId = $this->safe_string($order, 'currency_pair');
        $symbol = $this->safe_symbol($marketId, $market, '_');
        $price = $this->safe_string($order, 'price');
        $amount = $this->safe_string($order, 'amount');
        $id = $this->safe_string($order, 'id');
        return $this->safe_order(array(
            'id' => $id,
            'clientOrderId' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
            'status' => 'open',
            'symbol' => $symbol,
            'type' => 'limit',
            'timeInForce' => null,
            'postOnly' => null,
            'side' => $side,
            'price' => $price,
            'stopPrice' => null,
            'cost' => null,
            'amount' => $amount,
            'filled' => null,
            'remaining' => null,
            'trades' => null,
            'fee' => null,
            'info' => $order,
            'average' => null,
        ), $market);
    }

    public function fetch_open_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array(
            // 'is_token' => false,
            // 'is_token_both' => false,
        );
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['currency_pair'] = $market['id'];
        }
        $response = $this->privatePostActiveOrders (array_merge($request, $params));
        return $this->parse_orders($response['return'], $market, $since, $limit);
    }

    public function fetch_closed_orders($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array(
            // 'from' => 0,
            // 'count' => 1000,
            // 'from_id' => 0,
            // 'end_id' => 1000,
            // 'order' => 'DESC',
            // 'since' => 1503821051,
            // 'end' => 1503821051,
            // 'is_token' => false,
        );
        if ($symbol !== null) {
            $market = $this->market($symbol);
            $request['currency_pair'] = $market['id'];
        }
        $response = $this->privatePostTradeHistory (array_merge($request, $params));
        return $this->parse_orders($response['return'], $market, $since, $limit);
    }

    public function withdraw($code, $amount, $address, $tag = null, $params = array ()) {
        list($tag, $params) = $this->handle_withdraw_tag_and_params($tag, $params);
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency($code);
        if ($code === 'JPY') {
            throw new ExchangeError($this->id . ' withdraw() does not allow ' . $code . ' withdrawals');
        }
        $request = array(
            'currency' => $currency['id'],
            'amount' => $amount,
            'address' => $address,
            // 'message' => 'Hi!', // XEM and others
            // 'opt_fee' => 0.003, // BTC and MONA only
        );
        if ($tag !== null) {
            $request['message'] = $tag;
        }
        $result = $this->privatePostWithdraw (array_merge($request, $params));
        return array(
            'info' => $result,
            'id' => $result['return']['txid'],
            'fee' => $result['return']['fee'],
        );
    }

    public function nonce() {
        $nonce = floatval($this->milliseconds() / 1000);
        return sprintf('%.8f', $nonce);
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/';
        if ($api === 'public') {
            $url .= 'api/' . $this->version . '/' . $this->implode_params($path, $params);
        } else if ($api === 'fapi') {
            $url .= 'fapi/' . $this->version . '/' . $this->implode_params($path, $params);
        } else {
            $this->check_required_credentials();
            if ($api === 'ecapi') {
                $url .= 'ecapi';
            } else if ($api === 'tlapi') {
                $url .= 'tlapi';
            } else {
                $url .= 'tapi';
            }
            $nonce = $this->nonce();
            $body = $this->urlencode(array_merge(array(
                'method' => $path,
                'nonce' => $nonce,
            ), $params));
            $headers = array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Key' => $this->apiKey,
                'Sign' => $this->hmac($this->encode($body), $this->encode($this->secret), 'sha512'),
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($httpCode, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return;
        }
        //
        //     array("error" => "unsupported currency_pair")
        //
        $feedback = $this->id . ' ' . $body;
        $error = $this->safe_string($response, 'error');
        if ($error !== null) {
            $this->throw_exactly_matched_exception($this->exceptions['exact'], $error, $feedback);
            $this->throw_broadly_matched_exception($this->exceptions['broad'], $error, $feedback);
            throw new ExchangeError($feedback); // unknown message
        }
        $success = $this->safe_value($response, 'success', true);
        if (!$success) {
            throw new ExchangeError($feedback);
        }
    }
}
