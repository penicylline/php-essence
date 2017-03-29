<?php

namespace PhpEssence\Helper;


use PhpEssence\Service;

class Paginator extends Service {

    const DEFAULT_PAGE_SIZE = 10;
    const PAGE_VAR = '_p';
    const PAGE_RANGE = 3;

    private $currentPage;
    private $total;
    private $pageSize;
    private $totalPage;

    /**
     * Paginator constructor.
     * @param $total
     * @param null $pageSize
     */
    public function __construct($total, $pageSize = null) {
        parent::__construct();
        $this->total = $total;
        $this->pageSize = is_numeric($pageSize) ? $pageSize : static::DEFAULT_PAGE_SIZE;
        $this->totalPage = $this->total === 0 ? 1 : ceil($this->total / $this->pageSize);
        $request = $this->_sc->get('request');
        if ($request->hasGet(static::PAGE_VAR)) {
            $this->currentPage = (int)$request->get(static::PAGE_VAR);
            if ($this->currentPage < 1) $this->currentPage = 1;
            if ($this->currentPage > $this->totalPage) $this->currentPage = $this->totalPage;
        } else {
            $this->currentPage = 1;
        }
    }

    public function getQueryStart() {
        return $this->pageSize * ($this->currentPage - 1);
    }

    public function getPageSize() {
        return $this->pageSize;
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function getTotalPage() {
        return $this->totalPage;
    }

    public function render($url = null) {
        if (!$url) {
            $url = $this->_sc->get('request')->getCurrentUrl();
        }
        $parts = parse_url($url);
        isset($parts['query']) || $parts['query'] = null;
        parse_str($parts['query'], $queries);
        $start = $this->currentPage - static::PAGE_RANGE;
        if ($start < 1) $start = 1;
        $end = $this->currentPage + static::PAGE_RANGE;
        if ($end > $this->totalPage) $end = $this->totalPage;
        if ($start === $end) {
            return;
        }
        $html = '<div class="pagination">';
        if ($start > 1) {
            $html .= $this->renderPageLink($parts, $queries, 1, '<<');
        }
        for($i = $start; $i <= $end; $i++) {
            $html .= $this->renderPageLink($parts, $queries, $i);
        }
        if ($end < $this->totalPage) {
            $html .= $this->renderPageLink($parts, $queries, $this->totalPage, '>>');
        }
        $html .= '</div>';
        return $html;
    }

    private function renderPageLink(array $urlParts, array $queries, $page, $pageIden = null) {
        if ($pageIden === null) {
            $pageIden = $page;
        }
        $pageIden = htmlentities($pageIden);
        if ($page === $this->currentPage) {
            return '<span>' . $pageIden . '</span>';
        }
        $queries[static::PAGE_VAR] = $page;
        $urlParts['query'] = http_build_query($queries);
        $href = http_build_url($urlParts);
        return '<a href="' . $href . '">' . $pageIden . '</a>';
    }
}

/**
 * URL constants as defined in the PHP Manual under "Constants usable with
 * http_build_url()".
 *
 * @see http://us2.php.net/manual/en/http.constants.php#http.constants.url
 */
if (!defined('HTTP_URL_REPLACE')) {
    define('HTTP_URL_REPLACE', 1);
}
if (!defined('HTTP_URL_JOIN_PATH')) {
    define('HTTP_URL_JOIN_PATH', 2);
}
if (!defined('HTTP_URL_JOIN_QUERY')) {
    define('HTTP_URL_JOIN_QUERY', 4);
}
if (!defined('HTTP_URL_STRIP_USER')) {
    define('HTTP_URL_STRIP_USER', 8);
}
if (!defined('HTTP_URL_STRIP_PASS')) {
    define('HTTP_URL_STRIP_PASS', 16);
}
if (!defined('HTTP_URL_STRIP_AUTH')) {
    define('HTTP_URL_STRIP_AUTH', 32);
}
if (!defined('HTTP_URL_STRIP_PORT')) {
    define('HTTP_URL_STRIP_PORT', 64);
}
if (!defined('HTTP_URL_STRIP_PATH')) {
    define('HTTP_URL_STRIP_PATH', 128);
}
if (!defined('HTTP_URL_STRIP_QUERY')) {
    define('HTTP_URL_STRIP_QUERY', 256);
}
if (!defined('HTTP_URL_STRIP_FRAGMENT')) {
    define('HTTP_URL_STRIP_FRAGMENT', 512);
}
if (!defined('HTTP_URL_STRIP_ALL')) {
    define('HTTP_URL_STRIP_ALL', 1024);
}

if (!function_exists('http_build_url')) {

    /**
     * Build a URL.
     *
     * The parts of the second URL will be merged into the first according to
     * the flags argument.
     *
     * @param mixed $url     (part(s) of) an URL in form of a string or
     *                       associative array like parse_url() returns
     * @param mixed $parts   same as the first argument
     * @param int   $flags   a bitmask of binary or'ed HTTP_URL constants;
     *                       HTTP_URL_REPLACE is the default
     * @param array $new_url if set, it will be filled with the parts of the
     *                       composed url like parse_url() would return
     * @return string
     */
    function http_build_url($url, $parts = array(), $flags = HTTP_URL_REPLACE, &$new_url = array())
    {
        is_array($url) || $url = parse_url($url);
        is_array($parts) || $parts = parse_url($parts);

        isset($url['query']) && is_string($url['query']) || $url['query'] = null;
        isset($parts['query']) && is_string($parts['query']) || $parts['query'] = null;

        $keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

        // HTTP_URL_STRIP_ALL and HTTP_URL_STRIP_AUTH cover several other flags.
        if ($flags & HTTP_URL_STRIP_ALL) {
            $flags |= HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS
                | HTTP_URL_STRIP_PORT | HTTP_URL_STRIP_PATH
                | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT;
        } elseif ($flags & HTTP_URL_STRIP_AUTH) {
            $flags |= HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS;
        }

        // Schema and host are alwasy replaced
        foreach (array('scheme', 'host') as $part) {
            if (isset($parts[$part])) {
                $url[$part] = $parts[$part];
            }
        }

        if ($flags & HTTP_URL_REPLACE) {
            foreach ($keys as $key) {
                if (isset($parts[$key])) {
                    $url[$key] = $parts[$key];
                }
            }
        } else {
            if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
                if (isset($url['path']) && substr($parts['path'], 0, 1) !== '/') {
                    // Workaround for trailing slashes
                    $url['path'] .= 'a';
                    $url['path'] = rtrim(
                            str_replace(basename($url['path']), '', $url['path']),
                            '/'
                        ) . '/' . ltrim($parts['path'], '/');
                } else {
                    $url['path'] = $parts['path'];
                }
            }

            if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
                if (isset($url['query'])) {
                    parse_str($url['query'], $url_query);
                    parse_str($parts['query'], $parts_query);

                    $url['query'] = http_build_query(
                        array_replace_recursive(
                            $url_query,
                            $parts_query
                        )
                    );
                } else {
                    $url['query'] = $parts['query'];
                }
            }
        }

        if (isset($url['path']) && $url['path'] !== '' && substr($url['path'], 0, 1) !== '/') {
            $url['path'] = '/' . $url['path'];
        }

        foreach ($keys as $key) {
            $strip = 'HTTP_URL_STRIP_' . strtoupper($key);
            if ($flags & constant($strip)) {
                unset($url[$key]);
            }
        }

        $parsed_string = '';

        if (!empty($url['scheme'])) {
            $parsed_string .= $url['scheme'] . '://';
        }

        if (!empty($url['user'])) {
            $parsed_string .= $url['user'];

            if (isset($url['pass'])) {
                $parsed_string .= ':' . $url['pass'];
            }

            $parsed_string .= '@';
        }

        if (!empty($url['host'])) {
            $parsed_string .= $url['host'];
        }

        if (!empty($url['port'])) {
            $parsed_string .= ':' . $url['port'];
        }

        if (!empty($url['path'])) {
            $parsed_string .= $url['path'];
        }

        if (!empty($url['query'])) {
            $parsed_string .= '?' . $url['query'];
        }

        if (!empty($url['fragment'])) {
            $parsed_string .= '#' . $url['fragment'];
        }

        $new_url = $url;

        return $parsed_string;
    }
}