<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

class zipmex extends ndax {

    public function describe() {
        return $this->deep_extend(parent::describe(), array(
            'id' => 'zipmex',
            'name' => 'Zipmex',
            'countries' => array( 'AU', 'SG', 'TH', 'ID' ), // Australia, Singapore, Thailand, Indonesia
            'certified' => false,
            'pro' => true,
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/146103275-c39a34d9-68a4-4cd2-b1f1-c684548d311b.jpg',
                'test' => null,
                'api' => array(
                    'public' => 'https://apws.zipmex.com:8443/AP',
                    'private' => 'https://apws.zipmex.com:8443/AP',
                    // 'ws' => 'wss://apws.zipmex.com/WSGateway'
                ),
                'www' => 'https://zipmex.com/',
                'referral' => 'https://trade.zipmex.com/global/accounts/sign-up?aff=KLm7HyCsvN',
                'fees' => 'https://zipmex.com/fee-schedule',
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => true,
                    'percentage' => true,
                    'taker' => $this->parse_number('0.002'),
                    'maker' => $this->parse_number('0.002'),
                ),
            ),
        ));
    }
}
