using ccxt;
namespace Tests;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code


public partial class testMainClass : BaseTest
{
    public static void testMarginModification(Exchange exchange, object skippedProperties, object method, object entry)
    {
        object format = new Dictionary<string, object>() {
            { "info", new Dictionary<string, object>() {} },
            { "type", "add" },
            { "amount", exchange.parseNumber("0.1") },
            { "total", exchange.parseNumber("0.29934828") },
            { "code", "USDT" },
            { "symbol", "ADA/USDT:USDT" },
            { "status", "ok" },
        };
        object emptyAllowedFor = new List<object>() {"status", "symbol", "code", "total", "amount"};
        testSharedMethods.assertStructure(exchange, skippedProperties, method, entry, format, emptyAllowedFor);
        testSharedMethods.assertCurrencyCode(exchange, skippedProperties, method, entry, getValue(entry, "code"));
        //
        testSharedMethods.assertGreaterOrEqual(exchange, skippedProperties, method, entry, "amount", "0");
        testSharedMethods.assertGreaterOrEqual(exchange, skippedProperties, method, entry, "total", "0");
        testSharedMethods.assertInArray(exchange, skippedProperties, method, entry, "type", new List<object>() {"add", "reduce", "set"});
        testSharedMethods.assertInArray(exchange, skippedProperties, method, entry, "status", new List<object>() {"ok", "pending", "canceled", "failed"});
        testSharedMethods.assertSymbol(exchange, skippedProperties, method, entry, "symbol");
    }

}