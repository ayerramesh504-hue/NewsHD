<?php

namespace App\Controllers;

class Nepse extends BaseController
{
    public function index(): string
    {
        return view('site/nepse', [
            'meta'      => page_meta('NEPSE Market', 'Live Nepal Stock Exchange share market prices and market data.'),
            'activeNav' => 'nepse',
        ]);
    }
}
