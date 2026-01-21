<?php
$layout->setLayout('layout2');
$config = getConfig();
$setHead(<<<HTML
<title> about - {$config['web']['name']}</title>
HTML);
?>