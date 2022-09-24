<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

class flowbtc extends ndax {

    public function describe() {
        return $this->deep_extend(parent::describe(), array(
            'id' => 'flowbtc',
            'name' => 'flowBTC',
            'countries' => array( 'BR' ), // Brazil
            'rateLimit' => 1000,
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/51840849/87443317-01c0d080-c5fe-11ea-95c2-9ebe1a8fafd9.jpg',
                'api' => array(
                    'public' => 'https://api.flowbtc.com.br:8443/ap/',
                    'private' => 'https://api.flowbtc.com.br:8443/ap/',
                ),
                'www' => 'https://www.flowbtc.com.br',
                'doc' => 'https://www.flowbtc.com.br/api.html',
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.0025,
                    'taker' => 0.005,
                ),
            ),
        ));
    }
}
