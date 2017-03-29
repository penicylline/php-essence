<?php

namespace PhpEssence\Component;

class CliDispatcher extends Dispatcher  {

    protected function fetchRoute() {
        $options = getopt('m:c:a:');
        if (count($options) < 3) {
            return $this->printHelp();
        }
        $this->module = $options['m'];
        $this->controller = $options['c'];
        $this->action = $options['a'];
        return true;
    }

    public function getParam($name)
    {
        $options = getopt('m:c:a:', [$name . ':']);
        if (isset($options[$name])) {
            return $options[$name];
        }
        return null;
    }

    protected function sendOutput()
    {
        //nothing here
    }

    private function printHelp() {
        echo 'php cli.php -m <module> -c <controller> -a <action>', PHP_EOL;
    }
}
