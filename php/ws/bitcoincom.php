<?php

namespace ccxtpro;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

class bitcoincom extends \ccxt\rest\async\hitbtc {

    public function describe() {
        return $this->deep_extend(parent::describe(), array(
            'id' => 'bitcoincom',
            'name' => 'bitcoin.com',
            'countries' => array( 'KN' ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/97296144-514fa300-1861-11eb-952b-3d55d492200b.jpg',
                'api' => array(
                    'ws' => 'wss://api.fmfw.io/api/2/ws',
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'maker' => 0.15 / 100,
                    'taker' => 0.2 / 100,
                ),
            ),
        ));
    }
}
