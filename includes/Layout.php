<?php
namespace App\includes;
class Layout
{
    private $layout = 'default';
    private $head = '';
    private $content = '';
    private static $instance = null;
    private $config;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../config.php';
        $this->head = $this->config['layout']['default_head'];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function setHead($head)
    {
        $this->head = $head;
    }

    public function addHead($head)
    {
        $this->head .= $head;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function render()
    {
        ob_start();
        $layout = $this;
        require __DIR__ . '/../layouts/' . $this->layout . '.php';
        return ob_get_clean();
    }
}
