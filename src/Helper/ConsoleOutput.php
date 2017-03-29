<?php

namespace PhpEssence\Helper;

class ConsoleOutput
{
    const COLOR_WHITE = '1;37';
    const COLOR_RED = '0;31';
    const COLOR_GREEN = '0;32';
    const COLOR_BLUE = '0;34';

    public function write($str, $color)
    {
        echo sprintf("\033[%sm%s\033[0m", $color, $str);
    }

    public function white($str)
    {
        $this->write($str, static::COLOR_WHITE);
    }

    public function red($str)
    {
        $this->write($str, static::COLOR_RED);
    }

    public function blue($str)
    {
        $this->write($str, static::COLOR_BLUE);
    }

    public function green($str)
    {
        $this->write($str, static::COLOR_GREEN);
    }
}