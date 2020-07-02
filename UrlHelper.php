<?php

class UrlHelper {
    public $segments;
    public $query;

    public function __construct()
    {
        $urlRequest = explode('?', $_SERVER['REQUEST_URI']);
        $this->segments = explode('/', $urlRequest[0]);
        $this->query = explode('&', $urlRequest[1]); // TODO: check if use $_REQUEST instead?
    }

    public function getVar($var) {
        foreach ($this->query as $query) {
            $keyVar = explode('=', $query);
            if ($keyVar[0] === $var) {
                return $keyVar[1];
            }
        }
    }
}