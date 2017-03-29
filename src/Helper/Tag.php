<?php

namespace PhpEssence\Helper;

use PhpEssence\Form\FormElement;
use PhpEssence\Service;

class Tag extends Service {

    public function renderFormElement(FormElement $element) {
        echo $element->getHtml();
        $this->renderErrors($element->getMessages());
    }

    public function renderErrorMessage($message) {
        echo '<div class="error">' . htmlentities($message) . '</div>';
    }

    public function renderErrors($messages) {
        foreach ($messages as $message) {
            $this->renderErrorMessage($message);
        }
    }

    public function renderJsInclude($file, $async = false) {
        echo sprintf(
            '<script %s type="text/javascript" src="%s"></script>',
            $async ? 'async': '',
            $this->getUrl()->get('/js/' . $file)
        ), PHP_EOL;
    }

    public function renderCssInclude($file, $async = false) {
        if ($async) {
            echo '<script>window.csss = window.csss || []; window.csss.push("' . $this->getUrl()->get('/css/' . $file) . '");</script>';
            return;
        }
        echo '<link rel="stylesheet" href="' .$this->getUrl()->get('/css/' . $file) . '" media="all" />' . PHP_EOL;
    }

    public function renderCssDeferLoadScript() {
            echo <<<SCRIPT
            <script>window.addEventListener("load", function(){
                if (window.csss) {
                    for (var i in window.csss) {
                        var l = document.createElement('link');
                        l.rel = "stylesheet";
                        l.media = "all";
                        l.href = window.csss[i];
                        document.body.appendChild(l);
                    }
                }
            })</script>
SCRIPT;
    }
}
