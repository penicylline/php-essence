<?php

namespace PhpEssence;

class Logger
{
    private $fileHandle;
    private $timeWriten;
    public function __construct($path) {
        if ($path) {
            $this->fileHandle = fopen($path, 'a');
        }
    }

    public function __destruct() {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

    public function write($message) {
        if (null !== $this->fileHandle) {
            fwrite($this->fileHandle, $this->format($message));
        }
    }

    protected function format($message) {
        if (!$this->timeWriten) {
            $message = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $message;
            $this->timeWriten = true;
        }
        return  $message . PHP_EOL;
    }
}