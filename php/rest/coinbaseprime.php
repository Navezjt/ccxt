<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

class coinbaseprime extends coinbasepro {

    public function describe() {
        return $this->deep_extend(parent::describe(), array(
            'id' => 'coinbaseprime',
            'name' => 'Coinbase Prime',
            'pro' => true,
            'hostname' => 'exchange.coinbase.com',
            'urls' => array(
                'test' => array(
                    'public' => 'https://public.sandbox.exchange.coinbase.com',
                    'private' => 'https://public.sandbox.exchange.coinbase.com',
                ),
                'logo' => 'https://user-images.githubusercontent.com/1294454/44539184-29f26e00-a70c-11e8-868f-e907fc236a7c.jpg',
                'api' => array(
                    'public' => 'https://api.{hostname}',
                    'private' => 'https://api.{hostname}',
                ),
                'www' => 'https://exchange.coinbase.com',
                'doc' => 'https://docs.exchange.coinbase.com',
            ),
        ));
    }
}
