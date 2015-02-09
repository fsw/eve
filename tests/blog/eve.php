<?php
/**
 * blog.php
 *
 * A very simple blog instance showing core features of Eve framework.
 * To run this website in development mode run:
 * # php -S localhost:8000 tests/blog/eve.php
 * and navigate your browser to "localhost:8000"
 * To build the output production code run:
 * # php tests/blog/eve.php build public_html
 */
require_once (__DIR__ . '/../../eve/Eve.php');

Eve::run(['auth.lib', 'admin.lib', __DIR__], 
        ['db' => ['dsn' => 'mysql:host=localhost;dbname=eve_test_blog', 'user' => 'test', 'pass' => 'test']]);
