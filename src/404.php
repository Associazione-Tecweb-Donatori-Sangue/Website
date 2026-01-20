<?php
http_response_code(404);
readfile(__DIR__ . '/html/404.html');
?>