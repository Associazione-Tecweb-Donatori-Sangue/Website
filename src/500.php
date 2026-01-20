<?php
http_response_code(500);
readfile(__DIR__ . '/html/500.html');
?>