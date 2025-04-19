<?php

class Page {
    private $template;

    public function __construct($template) {
        $this->template = file_get_contents($template);
    }

    public function Render($data) {
        $output = $this->template;
        foreach ($data as $k => $v) {
            $output = str_replace("{{{$k}}}", htmlspecialchars($v), $output);
        }
        return $output;
    }
}
