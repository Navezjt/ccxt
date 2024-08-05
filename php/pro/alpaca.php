<?php

namespace ccxt\pro;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception; // a common import
use ccxt\ExchangeError;
use ccxt\AuthenticationError;
use React\Async;
use React\Promise\PromiseInterface;

class alpaca extends \ccxt\async\alpaca {

    public function describe() {
        return $this->deep_extend(parent::describe(), array(
            'has' => array(
                'ws' => true,
                'watchBalance' => false,
                'watchMyTrades' => true,
                'watchOHLCV' => true,
                'watchOrderBook' => true,
                'watchOrders' => true,
                'watchTicker' => true,
                'watchTickers' => false, // for now
                'watchTrades' => true,
                'watchPosition' => false,
            ),
            'urls' => array(
                'api' => array(
                    'ws' => array(
                        'crypto' => 'wss://stream.data.alpaca.markets/v1beta2/crypto',
                        'trading' => 'wss://api.alpaca.markets/stream',
                    ),
                ),
                'test' => array(
                    'ws' => array(
                        'crypto' => 'wss://stream.data.alpaca.markets/v1beta2/crypto',
                        'trading' => 'wss://paper-api.alpaca.markets/stream',
                    ),
                ),
            ),
            'options' => array(
            ),
            'streaming' => array(),
            'exceptions' => array(
                'ws' => array(
                    'exact' => array(
                    ),
                ),
            ),
        ));
    }

    public function watch_ticker(string $symbol, $params = array ()): PromiseInterface {
        return Async\async(function () use ($symbol, $params) {
            /**
             * watches a price ticker, a statistical calculation with the information calculated over the past 24 hours for a specific $market
             * @see https://docs.alpaca.markets/docs/real-time-crypto-pricing-data#quotes
             * @param {string} $symbol unified $symbol of the $market to fetch the ticker for
             * @param {array} [$params] extra parameters specific to the exchange API endpoint
             * @return {array} a ~@link https://docs.ccxt.com/#/?id=ticker-structure ticker structure~
             */
            $url = $this->urls['api']['ws']['crypto'];
            Async\await($this->authenticate($url));
            Async\await($this->load_markets());
            $market = $this->market($symbol);
            $messageHash = 'ticker:' . $market['symbol'];
            $request = array(
                'action' => 'subscribe',
                'quotes' => [ $market['id'] ],
            );
            return Async\await($this->watch($url, $messageHash, $this->extend($request, $params), $messageHash));
        }) ();
    }

    public function handle_ticker(Client $client, $message) {
        //
        //    {
        //         "T" => "q",
        //         "S" => "BTC/USDT",
        //         "bp" => 17394.44,
        //         "bs" => 0.021981,
        //         "ap" => 17397.99,
        //         "as" => 0.02,
        //         "t" => "2022-12-16T06:07:56.611063286Z"
        //    ]
        //
        $ticker = $this->parse_ticker($message);
        $symbol = $ticker['symbol'];
        $messageHash = 'ticker:' . $symbol;
        $this->tickers[$symbol] = $ticker;
        $client->resolve ($this->tickers[$symbol], $messageHash);
    }

    public function parse_ticker($ticker, $market = null): array {
        //
        //    {
        //         "T" => "q",
        //         "S" => "BTC/USDT",
        //         "bp" => 17394.44,
        //         "bs" => 0.021981,
        //         "ap" => 17397.99,
        //         "as" => 0.02,
        //         "t" => "2022-12-16T06:07:56.611063286Z"
        //    }
        //
        $marketId = $this->safe_string($ticker, 'S');
        $datetime = $this->safe_string($ticker, 't');
        return $this->safe_ticker(array(
            'symbol' => $this->safe_symbol($marketId, $market),
            'timestamp' => $this->parse8601($datetime),
            'datetime' => $datetime,
            'high' => null,
            'low' => null,
            'bid' => $this->safe_string($ticker, 'bp'),
            'bidVolume' => $this->safe_string($ticker, 'bs'),
            'ask' => $this->safe_string($ticker, 'ap'),
            'askVolume' => $this->safe_string($ticker, 'as'),
            'vwap' => null,
            'open' => null,
            'close' => null,
            'last' => null,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => null,
            'info' => $ticker,
        ), $market);
    }

    public function watch_ohlcv(string $symbol, $timeframe = '1m', ?int $since = null, ?int $limit = null, $params = array ()): PromiseInterface {
        return Async\async(function () use ($symbol, $timeframe, $since, $limit, $params) {
            /**
             * watches historical candlestick data containing the open, high, low, and close price, and the volume of a $market
             * @see https://docs.alpaca.markets/docs/real-time-crypto-pricing-data#bars
             * @param {string} $symbol unified $symbol of the $market to fetch OHLCV data for
             * @param {string} $timeframe the length of time each candle represents
             * @param {int} [$since] timestamp in ms of the earliest candle to fetch
             * @param {int} [$limit] the maximum amount of candles to fetch
             * @param {array} [$params] extra parameters specific to the exchange API endpoint
             * @return {int[][]} A list of candles ordered, open, high, low, close, volume
             */
            $url = $this->urls['api']['ws']['crypto'];
            Async\await($this->authenticate($url));
            Async\await($this->load_markets());
            $market = $this->market($symbol);
            $symbol = $market['symbol'];
            $request = array(
                'action' => 'subscribe',
                'bars' => [ $market['id'] ],
            );
            $messageHash = 'ohlcv:' . $symbol;
            $ohlcv = Async\await($this->watch($url, $messageHash, $this->extend($request, $params), $messageHash));
            if ($this->newUpdates) {
                $limit = $ohlcv->getLimit ($symbol, $limit);
            }
            return $this->filter_by_since_limit($ohlcv, $since, $limit, 0, true);
        }) ();
    }

    public function handle_ohlcv(Client $client, $message) {
        //
        //    {
        //        "T" => "b",
        //        "S" => "BTC/USDT",
        //        "o" => 17416.39,
        //        "h" => 17424.82,
        //        "l" => 17416.39,
        //        "c" => 17424.82,
        //        "v" => 1.341054,
        //        "t" => "2022-12-16T06:53:00Z",
        //        "n" => 21,
        //        "vw" => 17421.9529234915
        //    }
        //
        $marketId = $this->safe_string($message, 'S');
        $symbol = $this->safe_symbol($marketId);
        $stored = $this->safe_value($this->ohlcvs, $symbol);
        if ($stored === null) {
            $limit = $this->safe_integer($this->options, 'OHLCVLimit', 1000);
            $stored = new ArrayCacheByTimestamp ($limit);
            $this->ohlcvs[$symbol] = $stored;
        }
        $parsed = $this->parse_ohlcv($message);
        $stored->append ($parsed);
        $messageHash = 'ohlcv:' . $symbol;
        $client->resolve ($stored, $messageHash);
    }

    public function watch_order_book(string $symbol, ?int $limit = null, $params = array ()): PromiseInterface {
        return Async\async(function () use ($symbol, $limit, $params) {
            /**
             * watches information on open orders with bid (buy) and ask (sell) prices, volumes and other data
             * @see https://docs.alpaca.markets/docs/real-time-crypto-pricing-data#orderbooks
             * @param {string} $symbol unified $symbol of the $market to fetch the order book for
             * @param {int} [$limit] the maximum amount of order book entries to return.
             * @param {array} [$params] extra parameters specific to the exchange API endpoint
             * @return {array} A dictionary of ~@link https://docs.ccxt.com/#/?id=order-book-structure order book structures~ indexed by $market symbols
             */
            $url = $this->urls['api']['ws']['crypto'];
            Async\await($this->authenticate($url));
            Async\await($this->load_markets());
            $market = $this->market($symbol);
            $symbol = $market['symbol'];
            $messageHash = 'orderbook' . ':' . $symbol;
            $request = array(
                'action' => 'subscribe',
                'orderbooks' => [ $market['id'] ],
            );
            $orderbook = Async\await($this->watch($url, $messageHash, $this->extend($request, $params), $messageHash));
            return $orderbook->limit ();
        }) ();
    }

    public function handle_order_book(Client $client, $message) {
        //
        // $snapshot
        //    {
        //        "T" => "o",
        //        "S" => "BTC/USDT",
        //        "t" => "2022-12-16T06:35:31.585113205Z",
        //        "b" => [array(
        //                "p" => 17394.37,
        //                "s" => 0.015499,
        //            ),
        //            ...
        //        ],
        //        "a" => [array(
        //                "p" => 17398.8,
        //                "s" => 0.042919,
        //            ),
        //            ...
        //        ],
        //        "r" => true,
        //    }
        //
        $marketId = $this->safe_string($message, 'S');
        $symbol = $this->safe_symbol($marketId);
        $datetime = $this->safe_string($message, 't');
        $timestamp = $this->parse8601($datetime);
        $isSnapshot = $this->safe_bool($message, 'r', false);
        if (!(is_array($this->orderbooks) && array_key_exists($symbol, $this->orderbooks))) {
            $this->orderbooks[$symbol] = $this->order_book();
        }
        $orderbook = $this->orderbooks[$symbol];
        if ($isSnapshot) {
            $snapshot = $this->parse_order_book($message, $symbol, $timestamp, 'b', 'a', 'p', 's');
            $orderbook->reset ($snapshot);
        } else {
            $asks = $this->safe_list($message, 'a', array());
            $bids = $this->safe_list($message, 'b', array());
            $this->handle_deltas($orderbook['asks'], $asks);
            $this->handle_deltas($orderbook['bids'], $bids);
            $orderbook['timestamp'] = $timestamp;
            $orderbook['datetime'] = $datetime;
        }
        $messageHash = 'orderbook' . ':' . $symbol;
        $this->orderbooks[$symbol] = $orderbook;
        $client->resolve ($orderbook, $messageHash);
    }

    public function handle_delta($bookside, $delta) {
        $bidAsk = $this->parse_bid_ask($delta, 'p', 's');
        $bookside->storeArray ($bidAsk);
    }

    public function handle_deltas($bookside, $deltas) {
        for ($i = 0; $i < count($deltas); $i++) {
            $this->handle_delta($bookside, $deltas[$i]);
        }
    }

    public function watch_trades(string $symbol, ?int $since = null, ?int $limit = null, $params = array ()): PromiseInterface {
        return Async\async(function () use ($symbol, $since, $limit, $params) {
            /**
             * watches information on multiple $trades made in a $market
             * @see https://docs.alpaca.markets/docs/real-time-crypto-pricing-data#$trades
             * @param {string} $symbol unified $market $symbol of the $market $trades were made in
             * @param {int} [$since] the earliest time in ms to fetch orders for
             * @param {int} [$limit] the maximum number of trade structures to retrieve
             * @param {array} [$params] extra parameters specific to the exchange API endpoint
             * @return {array[]} a list of ~@link https://docs.ccxt.com/#/?id=trade-structure trade structures~
             */
            $url = $this->urls['api']['ws']['crypto'];
            Async\await($this->authenticate($url));
            Async\await($this->load_markets());
            $market = $this->market($symbol);
            $symbol = $market['symbol'];
            $messageHash = 'trade:' . $symbol;
            $request = array(
                'action' => 'subscribe',
                'trades' => [ $market['id'] ],
            );
            $trades = Async\await($this->watch($url, $messageHash, $this->extend($request, $params), $messageHash));
            if ($this->newUpdates) {
                $limit = $trades->getLimit ($symbol, $limit);
            }
            return $this->filter_by_since_limit($trades, $since, $limit, 'timestamp', true);
        }) ();
    }

    public function handle_trades(Client $client, $message) {
        //
        //     {
        //         "T" => "t",
        //         "S" => "BTC/USDT",
        //         "p" => 17408.8,
        //         "s" => 0.042919,
        //         "t" => "2022-12-16T06:43:18.327Z",
        //         "i" => 16585162,
        //         "tks" => "B"
        //     ]
        //
        $marketId = $this->safe_string($message, 'S');
        $symbol = $this->safe_symbol($marketId);
        $stored = $this->safe_value($this->trades, $symbol);
        if ($stored === null) {
            $limit = $this->safe_integer($this->options, 'tradesLimit', 1000);
            $stored = new ArrayCache ($limit);
            $this->trades[$symbol] = $stored;
        }
        $parsed = $this->parse_trade($message);
        $stored->append ($parsed);
        $messageHash = 'trade' . ':' . $symbol;
        $client->resolve ($stored, $messageHash);
    }

    public function watch_my_trades(?string $symbol = null, ?int $since = null, ?int $limit = null, $params = array ()): PromiseInterface {
        return Async\async(function () use ($symbol, $since, $limit, $params) {
            /**
             * watches information on multiple $trades made by the user
             * @see https://docs.alpaca.markets/docs/websocket-streaming#trade-updates
             * @param {string} $symbol unified market $symbol of the market $trades were made in
             * @param {int} [$since] the earliest time in ms to fetch $trades for
             * @param {int} [$limit] the maximum number of trade structures to retrieve
             * @param {array} [$params] extra parameters specific to the exchange API endpoint
             * @param {boolean} [$params->unifiedMargin] use unified margin account
             * @return {array[]} a list of ~@link https://docs.ccxt.com/#/?id=trade-structure trade structures~
             */
            $url = $this->urls['api']['ws']['trading'];
            Async\await($this->authenticate($url));
            $messageHash = 'myTrades';
            Async\await($this->load_markets());
            if ($symbol !== null) {
                $symbol = $this->symbol($symbol);
                $messageHash .= ':' . $symbol;
            }
            $request = array(
                'action' => 'listen',
                'data' => array(
                    'streams' => array( 'trade_updates' ),
                ),
            );
            $trades = Async\await($this->watch($url, $messageHash, $this->extend($request, $params), $messageHash));
            if ($this->newUpdates) {
                $limit = $trades->getLimit ($symbol, $limit);
            }
            return $this->filter_by_since_limit($trades, $since, $limit, 'timestamp', true);
        }) ();
    }

    public function watch_orders(?string $symbol = null, ?int $since = null, ?int $limit = null, $params = array ()): PromiseInterface {
        return Async\async(function () use ($symbol, $since, $limit, $params) {
            /**
             * watches information on multiple $orders made by the user
             * @param {string} $symbol unified $market $symbol of the $market $orders were made in
             * @param {int} [$since] the earliest time in ms to fetch $orders for
             * @param {int} [$limit] the maximum number of order structures to retrieve
             * @param {array} [$params] extra parameters specific to the exchange API endpoint
             * @return {array[]} a list of ~@link https://docs.ccxt.com/#/?id=order-structure order structures~
             */
            $url = $this->urls['api']['ws']['trading'];
            Async\await($this->authenticate($url));
            Async\await($this->load_markets());
            $messageHash = 'orders';
            if ($symbol !== null) {
                $market = $this->market($symbol);
                $symbol = $market['symbol'];
                $messageHash = 'orders:' . $symbol;
            }
            $request = array(
                'action' => 'listen',
                'data' => array(
                    'streams' => array( 'trade_updates' ),
                ),
            );
            $orders = Async\await($this->watch($url, $messageHash, $this->extend($request, $params), $messageHash));
            if ($this->newUpdates) {
                $limit = $orders->getLimit ($symbol, $limit);
            }
            return $this->filter_by_symbol_since_limit($orders, $symbol, $since, $limit, true);
        }) ();
    }

    public function handle_trade_update(Client $client, $message) {
        $this->handle_order($client, $message);
        $this->handle_my_trade($client, $message);
    }

    public function handle_order(Client $client, $message) {
        //
        //    {
        //        "stream" => "trade_updates",
        //        "data" => {
        //          "event" => "new",
        //          "timestamp" => "2022-12-16T07:28:51.67621869Z",
        //          "order" => array(
        //            "id" => "c2470331-8993-4051-bf5d-428d5bdc9a48",
        //            "client_order_id" => "0f1f3764-107a-4d09-8b9a-d75a11738f5c",
        //            "created_at" => "2022-12-16T02:28:51.673531798-05:00",
        //            "updated_at" => "2022-12-16T02:28:51.678736847-05:00",
        //            "submitted_at" => "2022-12-16T02:28:51.673015558-05:00",
        //            "filled_at" => null,
        //            "expired_at" => null,
        //            "cancel_requested_at" => null,
        //            "canceled_at" => null,
        //            "failed_at" => null,
        //            "replaced_at" => null,
        //            "replaced_by" => null,
        //            "replaces" => null,
        //            "asset_id" => "276e2673-764b-4ab6-a611-caf665ca6340",
        //            "symbol" => "BTC/USD",
        //            "asset_class" => "crypto",
        //            "notional" => null,
        //            "qty" => "0.01",
        //            "filled_qty" => "0",
        //            "filled_avg_price" => null,
        //            "order_class" => '',
        //            "order_type" => "market",
        //            "type" => "market",
        //            "side" => "buy",
        //            "time_in_force" => "gtc",
        //            "limit_price" => null,
        //            "stop_price" => null,
        //            "status" => "new",
        //            "extended_hours" => false,
        //            "legs" => null,
        //            "trail_percent" => null,
        //            "trail_price" => null,
        //            "hwm" => null
        //          ),
        //          "execution_id" => "5f781a30-b9a3-4c86-b466-2175850cf340"
        //        }
        //      }
        //
        $data = $this->safe_value($message, 'data', array());
        $rawOrder = $this->safe_value($data, 'order', array());
        if ($this->orders === null) {
            $limit = $this->safe_integer($this->options, 'ordersLimit', 1000);
            $this->orders = new ArrayCacheBySymbolById ($limit);
        }
        $orders = $this->orders;
        $order = $this->parse_order($rawOrder);
        $orders->append ($order);
        $messageHash = 'orders';
        $client->resolve ($orders, $messageHash);
        $messageHash = 'orders:' . $order['symbol'];
        $client->resolve ($orders, $messageHash);
    }

    public function handle_my_trade(Client $client, $message) {
        //
        //    {
        //        "stream" => "trade_updates",
        //        "data" => {
        //          "event" => "new",
        //          "timestamp" => "2022-12-16T07:28:51.67621869Z",
        //          "order" => array(
        //            "id" => "c2470331-8993-4051-bf5d-428d5bdc9a48",
        //            "client_order_id" => "0f1f3764-107a-4d09-8b9a-d75a11738f5c",
        //            "created_at" => "2022-12-16T02:28:51.673531798-05:00",
        //            "updated_at" => "2022-12-16T02:28:51.678736847-05:00",
        //            "submitted_at" => "2022-12-16T02:28:51.673015558-05:00",
        //            "filled_at" => null,
        //            "expired_at" => null,
        //            "cancel_requested_at" => null,
        //            "canceled_at" => null,
        //            "failed_at" => null,
        //            "replaced_at" => null,
        //            "replaced_by" => null,
        //            "replaces" => null,
        //            "asset_id" => "276e2673-764b-4ab6-a611-caf665ca6340",
        //            "symbol" => "BTC/USD",
        //            "asset_class" => "crypto",
        //            "notional" => null,
        //            "qty" => "0.01",
        //            "filled_qty" => "0",
        //            "filled_avg_price" => null,
        //            "order_class" => '',
        //            "order_type" => "market",
        //            "type" => "market",
        //            "side" => "buy",
        //            "time_in_force" => "gtc",
        //            "limit_price" => null,
        //            "stop_price" => null,
        //            "status" => "new",
        //            "extended_hours" => false,
        //            "legs" => null,
        //            "trail_percent" => null,
        //            "trail_price" => null,
        //            "hwm" => null
        //          ),
        //          "execution_id" => "5f781a30-b9a3-4c86-b466-2175850cf340"
        //        }
        //      }
        //
        $data = $this->safe_value($message, 'data', array());
        $event = $this->safe_string($data, 'event');
        if ($event !== 'fill' && $event !== 'partial_fill') {
            return;
        }
        $rawOrder = $this->safe_value($data, 'order', array());
        $myTrades = $this->myTrades;
        if ($myTrades === null) {
            $limit = $this->safe_integer($this->options, 'tradesLimit', 1000);
            $myTrades = new ArrayCacheBySymbolById ($limit);
        }
        $trade = $this->parse_my_trade($rawOrder);
        $myTrades->append ($trade);
        $messageHash = 'myTrades:' . $trade['symbol'];
        $client->resolve ($myTrades, $messageHash);
        $messageHash = 'myTrades';
        $client->resolve ($myTrades, $messageHash);
    }

    public function parse_my_trade($trade, $market = null) {
        //
        //    {
        //        "id" => "c2470331-8993-4051-bf5d-428d5bdc9a48",
        //        "client_order_id" => "0f1f3764-107a-4d09-8b9a-d75a11738f5c",
        //        "created_at" => "2022-12-16T02:28:51.673531798-05:00",
        //        "updated_at" => "2022-12-16T02:28:51.678736847-05:00",
        //        "submitted_at" => "2022-12-16T02:28:51.673015558-05:00",
        //        "filled_at" => null,
        //        "expired_at" => null,
        //        "cancel_requested_at" => null,
        //        "canceled_at" => null,
        //        "failed_at" => null,
        //        "replaced_at" => null,
        //        "replaced_by" => null,
        //        "replaces" => null,
        //        "asset_id" => "276e2673-764b-4ab6-a611-caf665ca6340",
        //        "symbol" => "BTC/USD",
        //        "asset_class" => "crypto",
        //        "notional" => null,
        //        "qty" => "0.01",
        //        "filled_qty" => "0",
        //        "filled_avg_price" => null,
        //        "order_class" => '',
        //        "order_type" => "market",
        //        "type" => "market",
        //        "side" => "buy",
        //        "time_in_force" => "gtc",
        //        "limit_price" => null,
        //        "stop_price" => null,
        //        "status" => "new",
        //        "extended_hours" => false,
        //        "legs" => null,
        //        "trail_percent" => null,
        //        "trail_price" => null,
        //        "hwm" => null
        //    }
        //
        $marketId = $this->safe_string($trade, 'symbol');
        $datetime = $this->safe_string($trade, 'filled_at');
        $type = $this->safe_string($trade, 'type');
        if (mb_strpos($type, 'limit') !== false) {
            // might be limit or stop-limit
            $type = 'limit';
        }
        return $this->safe_trade(array(
            'id' => $this->safe_string($trade, 'i'),
            'info' => $trade,
            'timestamp' => $this->parse8601($datetime),
            'datetime' => $datetime,
            'symbol' => $this->safe_symbol($marketId, null, '/'),
            'order' => $this->safe_string($trade, 'id'),
            'type' => $type,
            'side' => $this->safe_string($trade, 'side'),
            'takerOrMaker' => ($type === 'market') ? 'taker' : 'maker',
            'price' => $this->safe_string($trade, 'filled_avg_price'),
            'amount' => $this->safe_string($trade, 'filled_qty'),
            'cost' => null,
            'fee' => null,
        ), $market);
    }

    public function authenticate($url, $params = array ()) {
        return Async\async(function () use ($url, $params) {
            $this->check_required_credentials();
            $messageHash = 'authenticated';
            $client = $this->client($url);
            $future = $client->future ($messageHash);
            $authenticated = $this->safe_value($client->subscriptions, $messageHash);
            if ($authenticated === null) {
                $request = array(
                    'action' => 'auth',
                    'key' => $this->apiKey,
                    'secret' => $this->secret,
                );
                if ($url === $this->urls['api']['ws']['trading']) {
                    // this auth $request is being deprecated in test environment
                    $request = array(
                        'action' => 'authenticate',
                        'data' => array(
                            'key_id' => $this->apiKey,
                            'secret_key' => $this->secret,
                        ),
                    );
                }
                $this->watch($url, $messageHash, $request, $messageHash, $future);
            }
            return Async\await($future);
        }) ();
    }

    public function handle_error_message(Client $client, $message) {
        //
        //    {
        //        "T" => "error",
        //        "code" => 400,
        //        "msg" => "invalid syntax"
        //    }
        //
        $code = $this->safe_string($message, 'code');
        $msg = $this->safe_value($message, 'msg', array());
        throw new ExchangeError($this->id . ' $code => ' . $code . ' $message => ' . $msg);
    }

    public function handle_connected(Client $client, $message) {
        //
        //    {
        //        "T" => "success",
        //        "msg" => "connected"
        //    }
        //
        return $message;
    }

    public function handle_crypto_message(Client $client, $message) {
        for ($i = 0; $i < count($message); $i++) {
            $data = $message[$i];
            $T = $this->safe_string($data, 'T');
            $msg = $this->safe_string($data, 'msg');
            if ($T === 'subscription') {
                $this->handle_subscription($client, $data);
                return;
            }
            if ($T === 'success' && $msg === 'connected') {
                $this->handle_connected($client, $data);
                return;
            }
            if ($T === 'success' && $msg === 'authenticated') {
                $this->handle_authenticate($client, $data);
                return;
            }
            $methods = array(
                'error' => array($this, 'handle_error_message'),
                'b' => array($this, 'handle_ohlcv'),
                'q' => array($this, 'handle_ticker'),
                't' => array($this, 'handle_trades'),
                'o' => array($this, 'handle_order_book'),
            );
            $method = $this->safe_value($methods, $T);
            if ($method !== null) {
                $method($client, $data);
            }
        }
    }

    public function handle_trading_message(Client $client, $message) {
        $stream = $this->safe_string($message, 'stream');
        $methods = array(
            'authorization' => array($this, 'handle_authenticate'),
            'listening' => array($this, 'handle_subscription'),
            'trade_updates' => array($this, 'handle_trade_update'),
        );
        $method = $this->safe_value($methods, $stream);
        if ($method !== null) {
            $method($client, $message);
        }
    }

    public function handle_message(Client $client, $message) {
        if (gettype($message) === 'array' && array_keys($message) === array_keys(array_keys($message))) {
            $this->handle_crypto_message($client, $message);
            return;
        }
        $this->handle_trading_message($client, $message);
    }

    public function handle_authenticate(Client $client, $message) {
        //
        // crypto
        //    {
        //        "T" => "success",
        //        "msg" => "connected"
        //    ]
        //
        // trading
        //    {
        //        "stream" => "authorization",
        //        "data" => {
        //            "status" => "authorized",
        //            "action" => "authenticate"
        //        }
        //    }
        // error
        //    {
        //        "stream" => "authorization",
        //        "data" => {
        //            "action" => "authenticate",
        //            "message" => "access key verification failed",
        //            "status" => "unauthorized"
        //        }
        //    }
        //
        $T = $this->safe_string($message, 'T');
        $data = $this->safe_value($message, 'data', array());
        $status = $this->safe_string($data, 'status');
        if ($T === 'success' || $status === 'authorized') {
            $promise = $client->futures['authenticated'];
            $promise->resolve ($message);
            return;
        }
        throw new AuthenticationError($this->id . ' failed to authenticate.');
    }

    public function handle_subscription(Client $client, $message) {
        //
        // crypto
        //    {
        //          "T" => "subscription",
        //          "trades" => array(),
        //          "quotes" => array( "BTC/USDT" ),
        //          "orderbooks" => array(),
        //          "bars" => array(),
        //          "updatedBars" => array(),
        //          "dailyBars" => array()
        //    }
        // trading
        //    {
        //        "stream" => "listening",
        //        "data" => {
        //            "streams" => ["trade_updates"]
        //        }
        //    }
        //
        return $message;
    }
}
