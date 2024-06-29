<?php
namespace ccxt;

// ----------------------------------------------------------------------------

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

// -----------------------------------------------------------------------------
include_once PATH_TO_CCXT . '/test/exchange/base/test_currency.php';

function test_fetch_currencies($exchange, $skipped_properties) {
    $method = 'fetchCurrencies';
    // const isNative = exchange.has['fetchCurrencies'] && exchange.has['fetchCurrencies'] !== 'emulated';
    $currencies = $exchange->fetch_currencies();
    // todo: try to invent something to avoid undefined undefined, i.e. maybe move into private and force it to have a value
    if ($currencies !== null) {
        $values = is_array($currencies) ? array_values($currencies) : array();
        assert_non_emtpy_array($exchange, $skipped_properties, $method, $values);
        for ($i = 0; $i < count($values); $i++) {
            test_currency($exchange, $skipped_properties, $method, $values[$i]);
        }
    }
}
