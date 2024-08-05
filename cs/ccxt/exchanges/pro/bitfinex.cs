namespace ccxt.pro;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code


public partial class bitfinex { public bitfinex(object args = null) : base(args) { } }
public partial class bitfinex : ccxt.bitfinex
{
    public override object describe()
    {
        return this.deepExtend(base.describe(), new Dictionary<string, object>() {
            { "has", new Dictionary<string, object>() {
                { "ws", true },
                { "watchTicker", true },
                { "watchTickers", false },
                { "watchOrderBook", true },
                { "watchTrades", true },
                { "watchBalance", false },
                { "watchOHLCV", false },
            } },
            { "urls", new Dictionary<string, object>() {
                { "api", new Dictionary<string, object>() {
                    { "ws", new Dictionary<string, object>() {
                        { "public", "wss://api-pub.bitfinex.com/ws/1" },
                        { "private", "wss://api.bitfinex.com/ws/1" },
                    } },
                } },
            } },
            { "options", new Dictionary<string, object>() {
                { "watchOrderBook", new Dictionary<string, object>() {
                    { "prec", "P0" },
                    { "freq", "F0" },
                } },
                { "ordersLimit", 1000 },
            } },
        });
    }

    public async virtual Task<object> subscribe(object channel, object symbol, object parameters = null)
    {
        parameters ??= new Dictionary<string, object>();
        await this.loadMarkets();
        object market = this.market(symbol);
        object marketId = getValue(market, "id");
        object url = getValue(getValue(getValue(this.urls, "api"), "ws"), "public");
        object messageHash = add(add(channel, ":"), marketId);
        // const channel = 'trades';
        object request = new Dictionary<string, object>() {
            { "event", "subscribe" },
            { "channel", channel },
            { "symbol", marketId },
            { "messageHash", messageHash },
        };
        return await this.watch(url, messageHash, this.deepExtend(request, parameters), messageHash);
    }

    public async override Task<object> watchTrades(object symbol, object since = null, object limit = null, object parameters = null)
    {
        /**
        * @method
        * @name bitfinex#watchTrades
        * @description get the list of most recent trades for a particular symbol
        * @see https://docs.bitfinex.com/v1/reference/ws-public-trades
        * @param {string} symbol unified symbol of the market to fetch trades for
        * @param {int} [since] timestamp in ms of the earliest trade to fetch
        * @param {int} [limit] the maximum amount of trades to fetch
        * @param {object} [params] extra parameters specific to the exchange API endpoint
        * @returns {object[]} a list of [trade structures]{@link https://docs.ccxt.com/#/?id=public-trades}
        */
        parameters ??= new Dictionary<string, object>();
        await this.loadMarkets();
        symbol = this.symbol(symbol);
        object trades = await this.subscribe("trades", symbol, parameters);
        if (isTrue(this.newUpdates))
        {
            limit = callDynamically(trades, "getLimit", new object[] {symbol, limit});
        }
        return this.filterBySinceLimit(trades, since, limit, "timestamp", true);
    }

    public async override Task<object> watchTicker(object symbol, object parameters = null)
    {
        /**
        * @method
        * @name bitfinex#watchTicker
        * @description watches a price ticker, a statistical calculation with the information calculated over the past 24 hours for a specific market
        * @see https://docs.bitfinex.com/v1/reference/ws-public-ticker
        * @param {string} symbol unified symbol of the market to fetch the ticker for
        * @param {object} [params] extra parameters specific to the exchange API endpoint
        * @returns {object} a [ticker structure]{@link https://docs.ccxt.com/#/?id=ticker-structure}
        */
        parameters ??= new Dictionary<string, object>();
        return await this.subscribe("ticker", symbol, parameters);
    }

    public virtual void handleTrades(WebSocketClient client, object message, object subscription)
    {
        //
        // initial snapshot
        //
        //     [
        //         2,
        //         [
        //             [ null, 1580565020, 9374.9, 0.005 ],
        //             [ null, 1580565004, 9374.9, 0.005 ],
        //             [ null, 1580565003, 9374.9, 0.005 ],
        //         ]
        //     ]
        //
        // when a trade does not have an id yet
        //
        //     // channel id, update type, seq, time, price, amount
        //     [ 2, "te", "28462857-BTCUSD", 1580565041, 9374.9, 0.005 ],
        //
        // when a trade already has an id
        //
        //     // channel id, update type, seq, trade id, time, price, amount
        //     [ 2, "tu", "28462857-BTCUSD", 413357662, 1580565041, 9374.9, 0.005 ]
        //
        object channel = this.safeValue(subscription, "channel");
        object marketId = this.safeString(subscription, "pair");
        object messageHash = add(add(channel, ":"), marketId);
        object tradesLimit = this.safeInteger(this.options, "tradesLimit", 1000);
        object market = this.safeMarket(marketId);
        object symbol = getValue(market, "symbol");
        object data = this.safeValue(message, 1);
        object stored = this.safeValue(this.trades, symbol);
        if (isTrue(isEqual(stored, null)))
        {
            stored = new ArrayCache(tradesLimit);
            ((IDictionary<string,object>)this.trades)[(string)symbol] = stored;
        }
        if (isTrue(((data is IList<object>) || (data.GetType().IsGenericType && data.GetType().GetGenericTypeDefinition().IsAssignableFrom(typeof(List<>))))))
        {
            object trades = this.parseTrades(data, market);
            for (object i = 0; isLessThan(i, getArrayLength(trades)); postFixIncrement(ref i))
            {
                callDynamically(stored, "append", new object[] {getValue(trades, i)});
            }
        } else
        {
            object second = this.safeString(message, 1);
            if (isTrue(!isEqual(second, "tu")))
            {
                return;
            }
            object trade = this.parseTrade(message, market);
            callDynamically(stored, "append", new object[] {trade});
        }
        callDynamically(client as WebSocketClient, "resolve", new object[] {stored, messageHash});
    }

    public override object parseTrade(object trade, object market = null)
    {
        //
        // snapshot trade
        //
        //     // null, time, price, amount
        //     [ null, 1580565020, 9374.9, 0.005 ],
        //
        // when a trade does not have an id yet
        //
        //     // channel id, update type, seq, time, price, amount
        //     [ 2, "te", "28462857-BTCUSD", 1580565041, 9374.9, 0.005 ],
        //
        // when a trade already has an id
        //
        //     // channel id, update type, seq, trade id, time, price, amount
        //     [ 2, "tu", "28462857-BTCUSD", 413357662, 1580565041, 9374.9, 0.005 ]
        //
        if (!isTrue(((trade is IList<object>) || (trade.GetType().IsGenericType && trade.GetType().GetGenericTypeDefinition().IsAssignableFrom(typeof(List<>))))))
        {
            return base.parseTrade(trade, market);
        }
        object tradeLength = getArrayLength(trade);
        object eventVar = this.safeString(trade, 1);
        object id = null;
        if (isTrue(isEqual(eventVar, "tu")))
        {
            id = this.safeString(trade, subtract(tradeLength, 4));
        }
        object timestamp = this.safeTimestamp(trade, subtract(tradeLength, 3));
        object price = this.safeString(trade, subtract(tradeLength, 2));
        object amount = this.safeString(trade, subtract(tradeLength, 1));
        object side = null;
        if (isTrue(!isEqual(amount, null)))
        {
            side = ((bool) isTrue(Precise.stringGt(amount, "0"))) ? "buy" : "sell";
            amount = Precise.stringAbs(amount);
        }
        object seq = this.safeString(trade, 2);
        object parts = ((string)seq).Split(new [] {((string)"-")}, StringSplitOptions.None).ToList<object>();
        object marketId = this.safeString(parts, 1);
        if (isTrue(!isEqual(marketId, null)))
        {
            marketId = ((string)marketId).Replace((string)"t", (string)"");
        }
        object symbol = this.safeSymbol(marketId, market);
        object takerOrMaker = null;
        object orderId = null;
        return this.safeTrade(new Dictionary<string, object>() {
            { "info", trade },
            { "timestamp", timestamp },
            { "datetime", this.iso8601(timestamp) },
            { "symbol", symbol },
            { "id", id },
            { "order", orderId },
            { "type", null },
            { "takerOrMaker", takerOrMaker },
            { "side", side },
            { "price", price },
            { "amount", amount },
            { "cost", null },
            { "fee", null },
        });
    }

    public virtual void handleTicker(WebSocketClient client, object message, object subscription)
    {
        //
        //     [
        //         2,             // 0 CHANNEL_ID integer Channel ID
        //         236.62,        // 1 BID float Price of last highest bid
        //         9.0029,        // 2 BID_SIZE float Size of the last highest bid
        //         236.88,        // 3 ASK float Price of last lowest ask
        //         7.1138,        // 4 ASK_SIZE float Size of the last lowest ask
        //         -1.02,         // 5 DAILY_CHANGE float Amount that the last price has changed since yesterday
        //         0,             // 6 DAILY_CHANGE_PERC float Amount that the price has changed expressed in percentage terms
        //         236.52,        // 7 LAST_PRICE float Price of the last trade.
        //         5191.36754297, // 8 VOLUME float Daily volume
        //         250.01,        // 9 HIGH float Daily high
        //         220.05,        // 10 LOW float Daily low
        //     ]
        //
        object marketId = this.safeString(subscription, "pair");
        object symbol = this.safeSymbol(marketId);
        object channel = "ticker";
        object messageHash = add(add(channel, ":"), marketId);
        object last = this.safeString(message, 7);
        object change = this.safeString(message, 5);
        object open = null;
        if (isTrue(isTrue((!isEqual(last, null))) && isTrue((!isEqual(change, null)))))
        {
            open = Precise.stringSub(last, change);
        }
        object result = new Dictionary<string, object>() {
            { "symbol", symbol },
            { "timestamp", null },
            { "datetime", null },
            { "high", this.safeFloat(message, 9) },
            { "low", this.safeFloat(message, 10) },
            { "bid", this.safeFloat(message, 1) },
            { "bidVolume", null },
            { "ask", this.safeFloat(message, 3) },
            { "askVolume", null },
            { "vwap", null },
            { "open", this.parseNumber(open) },
            { "close", this.parseNumber(last) },
            { "last", this.parseNumber(last) },
            { "previousClose", null },
            { "change", this.parseNumber(change) },
            { "percentage", this.safeFloat(message, 6) },
            { "average", null },
            { "baseVolume", this.safeFloat(message, 8) },
            { "quoteVolume", null },
            { "info", message },
        };
        ((IDictionary<string,object>)this.tickers)[(string)symbol] = result;
        callDynamically(client as WebSocketClient, "resolve", new object[] {result, messageHash});
    }

    public async override Task<object> watchOrderBook(object symbol, object limit = null, object parameters = null)
    {
        /**
        * @method
        * @name bitfinex#watchOrderBook
        * @description watches information on open orders with bid (buy) and ask (sell) prices, volumes and other data
        * @see https://docs.bitfinex.com/v1/reference/ws-public-order-books
        * @param {string} symbol unified symbol of the market to fetch the order book for
        * @param {int} [limit] the maximum amount of order book entries to return
        * @param {object} [params] extra parameters specific to the exchange API endpoint
        * @returns {object} A dictionary of [order book structures]{@link https://docs.ccxt.com/#/?id=order-book-structure} indexed by market symbols
        */
        parameters ??= new Dictionary<string, object>();
        if (isTrue(!isEqual(limit, null)))
        {
            if (isTrue(isTrue((!isEqual(limit, 25))) && isTrue((!isEqual(limit, 100)))))
            {
                throw new ExchangeError ((string)add(this.id, " watchOrderBook limit argument must be undefined, 25 or 100")) ;
            }
        }
        object options = this.safeValue(this.options, "watchOrderBook", new Dictionary<string, object>() {});
        object prec = this.safeString(options, "prec", "P0");
        object freq = this.safeString(options, "freq", "F0");
        object request = new Dictionary<string, object>() {
            { "prec", prec },
            { "freq", freq },
            { "len", limit },
        };
        object orderbook = await this.subscribe("book", symbol, this.deepExtend(request, parameters));
        return (orderbook as IOrderBook).limit();
    }

    public virtual void handleOrderBook(WebSocketClient client, object message, object subscription)
    {
        //
        // first message (snapshot)
        //
        //     [
        //         18691, // channel id
        //         [
        //             [ 7364.8, 10, 4.354802 ], // price, count, size > 0 = bid
        //             [ 7364.7, 1, 0.00288831 ],
        //             [ 7364.3, 12, 0.048 ],
        //             [ 7364.9, 3, -0.42028976 ], // price, count, size < 0 = ask
        //             [ 7365, 1, -0.25 ],
        //             [ 7365.5, 1, -0.00371937 ],
        //         ]
        //     ]
        //
        // subsequent updates
        //
        //     [
        //         30,     // channel id
        //         9339.9, // price
        //         0,      // count
        //         -1,     // size > 0 = bid, size < 0 = ask
        //     ]
        //
        object marketId = this.safeString(subscription, "pair");
        object symbol = this.safeSymbol(marketId);
        object channel = "book";
        object messageHash = add(add(channel, ":"), marketId);
        object prec = this.safeString(subscription, "prec", "P0");
        object isRaw = (isEqual(prec, "R0"));
        // if it is an initial snapshot
        if (isTrue(((getValue(message, 1) is IList<object>) || (getValue(message, 1).GetType().IsGenericType && getValue(message, 1).GetType().GetGenericTypeDefinition().IsAssignableFrom(typeof(List<>))))))
        {
            object limit = this.safeInteger(subscription, "len");
            if (isTrue(isRaw))
            {
                // raw order books
                ((IDictionary<string,object>)this.orderbooks)[(string)symbol] = this.indexedOrderBook(new Dictionary<string, object>() {}, limit);
            } else
            {
                // P0, P1, P2, P3, P4
                ((IDictionary<string,object>)this.orderbooks)[(string)symbol] = this.countedOrderBook(new Dictionary<string, object>() {}, limit);
            }
            object orderbook = getValue(this.orderbooks, symbol);
            if (isTrue(isRaw))
            {
                object deltas = getValue(message, 1);
                for (object i = 0; isLessThan(i, getArrayLength(deltas)); postFixIncrement(ref i))
                {
                    object delta = getValue(deltas, i);
                    object id = this.safeString(delta, 0);
                    object price = this.safeFloat(delta, 1);
                    object delta2Value = getValue(delta, 2);
                    object size = ((bool) isTrue((isLessThan(delta2Value, 0)))) ? prefixUnaryNeg(ref delta2Value) : delta2Value;
                    object side = ((bool) isTrue((isLessThan(delta2Value, 0)))) ? "asks" : "bids";
                    object bookside = getValue(orderbook, side);
                    (bookside as IOrderBookSide).storeArray(new List<object>() {price, size, id});
                }
            } else
            {
                object deltas = getValue(message, 1);
                for (object i = 0; isLessThan(i, getArrayLength(deltas)); postFixIncrement(ref i))
                {
                    object delta = getValue(deltas, i);
                    object delta2 = getValue(delta, 2);
                    object size = ((bool) isTrue((isLessThan(delta2, 0)))) ? prefixUnaryNeg(ref delta2) : delta2;
                    object side = ((bool) isTrue((isLessThan(delta2, 0)))) ? "asks" : "bids";
                    object countedBookSide = getValue(orderbook, side);
                    (countedBookSide as IOrderBookSide).storeArray(new List<object>() {getValue(delta, 0), size, getValue(delta, 1)});
                }
            }
            callDynamically(client as WebSocketClient, "resolve", new object[] {orderbook, messageHash});
        } else
        {
            object orderbook = getValue(this.orderbooks, symbol);
            if (isTrue(isRaw))
            {
                object id = this.safeString(message, 1);
                object price = this.safeString(message, 2);
                object message3 = getValue(message, 3);
                object size = ((bool) isTrue((isLessThan(message3, 0)))) ? prefixUnaryNeg(ref message3) : message3;
                object side = ((bool) isTrue((isLessThan(message3, 0)))) ? "asks" : "bids";
                object bookside = getValue(orderbook, side);
                // price = 0 means that you have to remove the order from your book
                object amount = ((bool) isTrue(Precise.stringGt(price, "0"))) ? size : "0";
                (bookside as IOrderBookSide).storeArray(new List<object> {this.parseNumber(price), this.parseNumber(amount), id});
            } else
            {
                object message3Value = getValue(message, 3);
                object size = ((bool) isTrue((isLessThan(message3Value, 0)))) ? prefixUnaryNeg(ref message3Value) : message3Value;
                object side = ((bool) isTrue((isLessThan(message3Value, 0)))) ? "asks" : "bids";
                object countedBookSide = getValue(orderbook, side);
                (countedBookSide as IOrderBookSide).storeArray(new List<object>() {getValue(message, 1), size, getValue(message, 2)});
            }
            callDynamically(client as WebSocketClient, "resolve", new object[] {orderbook, messageHash});
        }
    }

    public virtual void handleHeartbeat(WebSocketClient client, object message)
    {
        //
        // every second (approx) if no other updates are sent
        //
        //     { "event": "heartbeat" }
        //
        object eventVar = this.safeString(message, "event");
        callDynamically(client as WebSocketClient, "resolve", new object[] {message, eventVar});
    }

    public virtual object handleSystemStatus(WebSocketClient client, object message)
    {
        //
        // todo: answer the question whether handleSystemStatus should be renamed
        // and unified as handleStatus for any usage pattern that
        // involves system status and maintenance updates
        //
        //     {
        //         "event": "info",
        //         "version": 2,
        //         "serverId": "e293377e-7bb7-427e-b28c-5db045b2c1d1",
        //         "platform": { status: 1 }, // 1 for operative, 0 for maintenance
        //     }
        //
        return message;
    }

    public virtual object handleSubscriptionStatus(WebSocketClient client, object message)
    {
        //
        //     {
        //         "event": "subscribed",
        //         "channel": "book",
        //         "chanId": 67473,
        //         "symbol": "tBTCUSD",
        //         "prec": "P0",
        //         "freq": "F0",
        //         "len": "25",
        //         "pair": "BTCUSD"
        //     }
        //
        object channelId = this.safeString(message, "chanId");
        ((IDictionary<string,object>)((WebSocketClient)client).subscriptions)[(string)channelId] = message;
        return message;
    }

    public async virtual Task<object> authenticate(object parameters = null)
    {
        parameters ??= new Dictionary<string, object>();
        object url = getValue(getValue(getValue(this.urls, "api"), "ws"), "private");
        var client = this.client(url);
        var future = client.future("authenticated");
        object method = "auth";
        object authenticated = this.safeValue(((WebSocketClient)client).subscriptions, method);
        if (isTrue(isEqual(authenticated, null)))
        {
            object nonce = this.milliseconds();
            object payload = add("AUTH", ((object)nonce).ToString());
            object signature = this.hmac(this.encode(payload), this.encode(this.secret), sha384, "hex");
            object request = new Dictionary<string, object>() {
                { "apiKey", this.apiKey },
                { "authSig", signature },
                { "authNonce", nonce },
                { "authPayload", payload },
                { "event", method },
                { "filter", new List<object>() {"trading", "wallet"} },
            };
            this.spawn(this.watch, new object[] { url, method, request, 1});
        }
        return await (future as Exchange.Future);
    }

    public virtual void handleAuthenticationMessage(WebSocketClient client, object message)
    {
        object status = this.safeString(message, "status");
        if (isTrue(isEqual(status, "OK")))
        {
            // we resolve the future here permanently so authentication only happens once
            var future = this.safeValue((client as WebSocketClient).futures, "authenticated");
            (future as Future).resolve(true);
        } else
        {
            var error = new AuthenticationError(this.json(message));
            ((WebSocketClient)client).reject(error, "authenticated");
            // allows further authentication attempts
            object method = this.safeString(message, "event");
            if (isTrue(inOp(((WebSocketClient)client).subscriptions, method)))
            {

            }
        }
    }

    public async virtual Task<object> watchOrder(object id, object symbol = null, object parameters = null)
    {
        parameters ??= new Dictionary<string, object>();
        await this.loadMarkets();
        object url = getValue(getValue(getValue(this.urls, "api"), "ws"), "private");
        await this.authenticate();
        return await this.watch(url, id, null, 1);
    }

    public async override Task<object> watchOrders(object symbol = null, object since = null, object limit = null, object parameters = null)
    {
        /**
        * @method
        * @name bitfinex#watchOrders
        * @description watches information on multiple orders made by the user
        * @see https://docs.bitfinex.com/v1/reference/ws-auth-order-updates
        * @see https://docs.bitfinex.com/v1/reference/ws-auth-order-snapshots
        * @param {string} symbol unified market symbol of the market orders were made in
        * @param {int} [since] the earliest time in ms to fetch orders for
        * @param {int} [limit] the maximum number of order structures to retrieve
        * @param {object} [params] extra parameters specific to the exchange API endpoint
        * @returns {object[]} a list of [order structures]{@link https://docs.ccxt.com/#/?id=order-structure}
        */
        parameters ??= new Dictionary<string, object>();
        await this.loadMarkets();
        await this.authenticate();
        if (isTrue(!isEqual(symbol, null)))
        {
            symbol = this.symbol(symbol);
        }
        object url = getValue(getValue(getValue(this.urls, "api"), "ws"), "private");
        object orders = await this.watch(url, "os", null, 1);
        if (isTrue(this.newUpdates))
        {
            limit = callDynamically(orders, "getLimit", new object[] {symbol, limit});
        }
        return this.filterBySymbolSinceLimit(orders, symbol, since, limit, true);
    }

    public virtual void handleOrders(WebSocketClient client, object message, object subscription)
    {
        //
        // order snapshot
        //
        //     [
        //         0,
        //         "os",
        //         [
        //             [
        //                 45287766631,
        //                 "ETHUST",
        //                 -0.07,
        //                 -0.07,
        //                 "EXCHANGE LIMIT",
        //                 "ACTIVE",
        //                 210,
        //                 0,
        //                 "2020-05-16T13:17:46Z",
        //                 0,
        //                 0,
        //                 0
        //             ]
        //         ]
        //     ]
        //
        // order cancel
        //
        //     [
        //         0,
        //         "oc",
        //         [
        //             45287766631,
        //             "ETHUST",
        //             -0.07,
        //             -0.07,
        //             "EXCHANGE LIMIT",
        //             "CANCELED",
        //             210,
        //             0,
        //             "2020-05-16T13:17:46Z",
        //             0,
        //             0,
        //             0,
        //         ]
        //     ]
        //
        object data = this.safeValue(message, 2, new List<object>() {});
        object messageType = this.safeString(message, 1);
        if (isTrue(isEqual(messageType, "os")))
        {
            for (object i = 0; isLessThan(i, getArrayLength(data)); postFixIncrement(ref i))
            {
                object value = getValue(data, i);
                this.handleOrder(client as WebSocketClient, value);
            }
        } else
        {
            this.handleOrder(client as WebSocketClient, data);
        }
        if (isTrue(!isEqual(this.orders, null)))
        {
            callDynamically(client as WebSocketClient, "resolve", new object[] {this.orders, "os"});
        }
    }

    public virtual object parseWsOrderStatus(object status)
    {
        object statuses = new Dictionary<string, object>() {
            { "ACTIVE", "open" },
            { "CANCELED", "canceled" },
        };
        return this.safeString(statuses, status, status);
    }

    public virtual object handleOrder(WebSocketClient client, object order)
    {
        // [ 45287766631,
        //     "ETHUST",
        //     -0.07,
        //     -0.07,
        //     "EXCHANGE LIMIT",
        //     "CANCELED",
        //     210,
        //     0,
        //     "2020-05-16T13:17:46Z",
        //     0,
        //     0,
        //     0 ]
        object id = this.safeString(order, 0);
        object marketId = this.safeString(order, 1);
        object symbol = this.safeSymbol(marketId);
        object amount = this.safeString(order, 2);
        object remaining = this.safeString(order, 3);
        object side = "buy";
        if (isTrue(Precise.stringLt(amount, "0")))
        {
            amount = Precise.stringAbs(amount);
            remaining = Precise.stringAbs(remaining);
            side = "sell";
        }
        object type = this.safeString(order, 4);
        if (isTrue(isGreaterThan(getIndexOf(type, "LIMIT"), -1)))
        {
            type = "limit";
        } else if (isTrue(isGreaterThan(getIndexOf(type, "MARKET"), -1)))
        {
            type = "market";
        }
        object status = this.parseWsOrderStatus(this.safeString(order, 5));
        object price = this.safeString(order, 6);
        object rawDatetime = this.safeString(order, 8);
        object timestamp = this.parse8601(rawDatetime);
        object parsed = this.safeOrder(new Dictionary<string, object>() {
            { "info", order },
            { "id", id },
            { "clientOrderId", null },
            { "timestamp", timestamp },
            { "datetime", this.iso8601(timestamp) },
            { "lastTradeTimestamp", null },
            { "symbol", symbol },
            { "type", type },
            { "side", side },
            { "price", price },
            { "stopPrice", null },
            { "triggerPrice", null },
            { "average", null },
            { "amount", amount },
            { "remaining", remaining },
            { "filled", null },
            { "status", status },
            { "fee", null },
            { "cost", null },
            { "trades", null },
        });
        if (isTrue(isEqual(this.orders, null)))
        {
            object limit = this.safeInteger(this.options, "ordersLimit", 1000);
            this.orders = new ArrayCacheBySymbolById(limit);
        }
        object orders = this.orders;
        callDynamically(orders, "append", new object[] {parsed});
        callDynamically(client as WebSocketClient, "resolve", new object[] {parsed, id});
        return parsed;
    }

    public override void handleMessage(WebSocketClient client, object message)
    {
        if (isTrue(((message is IList<object>) || (message.GetType().IsGenericType && message.GetType().GetGenericTypeDefinition().IsAssignableFrom(typeof(List<>))))))
        {
            object channelId = this.safeString(message, 0);
            //
            //     [
            //         1231,
            //         "hb",
            //     ]
            //
            if (isTrue(isEqual(getValue(message, 1), "hb")))
            {
                return;  // skip heartbeats within subscription channels for now
            }
            object subscription = this.safeValue(((WebSocketClient)client).subscriptions, channelId, new Dictionary<string, object>() {});
            object channel = this.safeString(subscription, "channel");
            object name = this.safeString(message, 1);
            object methods = new Dictionary<string, object>() {
                { "book", this.handleOrderBook },
                { "ticker", this.handleTicker },
                { "trades", this.handleTrades },
                { "os", this.handleOrders },
                { "on", this.handleOrders },
                { "oc", this.handleOrders },
            };
            object method = this.safeValue2(methods, channel, name);
            if (isTrue(!isEqual(method, null)))
            {
                DynamicInvoker.InvokeMethod(method, new object[] { client, message, subscription});
            }
        } else
        {
            // todo add bitfinex handleErrorMessage
            //
            //     {
            //         "event": "info",
            //         "version": 2,
            //         "serverId": "e293377e-7bb7-427e-b28c-5db045b2c1d1",
            //         "platform": { status: 1 }, // 1 for operative, 0 for maintenance
            //     }
            //
            object eventVar = this.safeString(message, "event");
            if (isTrue(!isEqual(eventVar, null)))
            {
                object methods = new Dictionary<string, object>() {
                    { "info", this.handleSystemStatus },
                    { "subscribed", this.handleSubscriptionStatus },
                    { "auth", this.handleAuthenticationMessage },
                };
                object method = this.safeValue(methods, eventVar);
                if (isTrue(!isEqual(method, null)))
                {
                    DynamicInvoker.InvokeMethod(method, new object[] { client, message});
                }
            }
        }
    }
}
