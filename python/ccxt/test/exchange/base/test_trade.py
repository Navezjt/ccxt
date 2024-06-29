import os
import sys

root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))))
sys.path.append(root)

# ----------------------------------------------------------------------------

# PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
# https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

# ----------------------------------------------------------------------------
# -*- coding: utf-8 -*-

from ccxt.test.exchange.base import test_shared_methods  # noqa E402

def test_trade(exchange, skipped_properties, method, entry, symbol, now):
    format = {
        'info': {},
        'id': '12345-67890:09876/54321',
        'timestamp': 1502962946216,
        'datetime': '2017-08-17 12:42:48.000',
        'symbol': 'ETH/BTC',
        'order': '12345-67890:09876/54321',
        'side': 'buy',
        'takerOrMaker': 'taker',
        'price': exchange.parse_number('0.06917684'),
        'amount': exchange.parse_number('1.5'),
        'cost': exchange.parse_number('0.10376526'),
        'fees': [],
        'fee': {},
    }
    # todo: add takeOrMaker as mandatory (atm, many exchanges fail)
    # removed side because some public endpoints return trades without side
    empty_allowed_for = ['fees', 'fee', 'symbol', 'order', 'id', 'takerOrMaker', 'timestamp', 'datetime']
    test_shared_methods.assert_structure(exchange, skipped_properties, method, entry, format, empty_allowed_for)
    test_shared_methods.assert_timestamp_and_datetime(exchange, skipped_properties, method, entry, now)
    test_shared_methods.assert_symbol(exchange, skipped_properties, method, entry, 'symbol', symbol)
    #
    test_shared_methods.assert_in_array(exchange, skipped_properties, method, entry, 'side', ['buy', 'sell'])
    test_shared_methods.assert_in_array(exchange, skipped_properties, method, entry, 'takerOrMaker', ['taker', 'maker'])
    test_shared_methods.assert_fee_structure(exchange, skipped_properties, method, entry, 'fee')
    if not ('fees' in skipped_properties):
        # todo: remove undefined check and probably non-empty array check later
        if entry['fees'] is not None:
            for i in range(0, len(entry['fees'])):
                test_shared_methods.assert_fee_structure(exchange, skipped_properties, method, entry['fees'], i)
