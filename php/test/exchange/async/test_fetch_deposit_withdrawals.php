<?php
namespace ccxt;

// ----------------------------------------------------------------------------

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

// -----------------------------------------------------------------------------
use React\Async;
use React\Promise;
include_once PATH_TO_CCXT . '/test/exchange/base/test_deposit_withdrawal.php';
include_once PATH_TO_CCXT . '/test/exchange/base/test_shared_methods.php';

function test_fetch_deposits_withdrawals($exchange, $skipped_properties, $code) {
    return Async\async(function () use ($exchange, $skipped_properties, $code) {
        $method = 'fetchTransactions';
        $transactions = Async\await($exchange->fetch_transactions($code));
        assert_non_emtpy_array($exchange, $skipped_properties, $method, $transactions, $code);
        $now = $exchange->milliseconds();
        for ($i = 0; $i < count($transactions); $i++) {
            test_deposit_withdrawal($exchange, $skipped_properties, $method, $transactions[$i], $code, $now);
        }
        assert_timestamp_order($exchange, $method, $code, $transactions);
    }) ();
}
