<?php

class CurrencySelectorItem
{
    public $title;
    public $decimals;
    public $code;
    public $symbol;
    public $rate;
    public $URL;
    public $image;
    public $active = false;
    public $decPoint;
    public $thousandsSep;

    public function __construct($info, $activeCode, $currentURL)
    {
        $this->code = strtolower($info['code']);
        $this->symbol = $info['symbol'];
        $this->rate = floatval(str_replace([" ", ','], ["", '.'], $info['rate']));
        $this->title = $info['title'];

        $LocaleInfo = localeconv();
        $this->decimals = isset($info['decimals']) ? $info['decimals'] : 2;
        $this->decPoint = !empty($info['decPoint']) ? $info['decPoint'] : $LocaleInfo["mon_decimal_point"];
        $this->thousandsSep = !empty($info['thousandsSep']) ? $info['thousandsSep'] : $LocaleInfo["mon_thousands_sep"];

        $this->prepareURL($currentURL);

        if ($this->code == $activeCode) {
            $this->active = true;
        }
    }

    protected function prepareURL($currentURL)
    {
        $this->URL = $currentURL . 'currency:' . $this->code . '/';
    }
}

