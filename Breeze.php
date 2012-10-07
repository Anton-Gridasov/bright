<?php

/**
 * Project:     Breeze framework
 * Author:      Jager Mesh (jagermesh@gmail.com)
 *
 * @version 1.1.0.0
 * @package Breeze Core
 */

require_once(__DIR__.'/Br.php');

// Core PHP settings
error_reporting(E_ALL & ~E_COMPILE_WARNING & ~E_DEPRECATED);
set_magic_quotes_runtime(0);

if (get_magic_quotes_gpc()) { 
  br()->stripSlashes($_GET);
  br()->stripSlashes($_POST);
  br()->stripSlashes($_COOKIE); 
  br()->stripSlashes($_REQUEST);
  if (isset($_SERVER['PHP_AUTH_USER'])) br()->stripSlashes($_SERVER['PHP_AUTH_USER']); 
  if (isset($_SERVER['PHP_AUTH_PW'])) br()->stripSlashes($_SERVER['PHP_AUTH_PW']);
}

ini_set('url_rewriter.tags', null);
if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get")) {
  @date_default_timezone_set(@date_default_timezone_get());
}
// Core PHP settings - End

// Breeze files base path
define('BreezePath', __DIR__ . '/');

// Installing custom error handler
require_once(__DIR__.'/BrErrorHandler.php');
BrErrorHandler::GetInstance();

// Application base path - we assuming that Breeze library inlcuded by main index.php
$traces = debug_backtrace();
br()->saveCallerScript($traces[0]['file']);

// Loading application settings
br()->importAtBasePath('config.php');

// Core PHP settings - Secondary
ini_set('session.gc_maxlifetime', br()->config()->get('php/session.gc_maxlifetime', 3600));
ini_set('session.cache_expire', br()->config()->get('php/session.cache_expire', 180));
ini_set('session.cookie_lifetime', br()->config()->get('php/session.cookie_lifetime', 0));
// Core PHP settings - Secondary - End

// Logging
br()->importLib('FileLogAdapter');
br()->importLib('ErrorFileLogAdapter');
br()->importLib('ErrorMailLogAdapter');
br()->log()->addAdapter(new BrFileLogAdapter(br()->atBasePath('_logs')));
br()->log()->addAdapter(new BrErrorFileLogAdapter(br()->atBasePath('_logs')));
br()->log()->addAdapter(new BrErrorMailLogAdapter());
if (br()->isConsoleMode()) {
  br()->importLib('ConsoleLogAdapter');
  br()->log()->addAdapter(new BrConsoleLogAdapter());
}

// Running application
if (br()->isConsoleMode()) {
  // If we are in console mode - Breeze is just a set of useful functions
} else {
  // Starting session
  session_cache_limiter('none');
  session_start();

  br()->request()->routeGET('/breeze-scripts', function($matches) {
    br()->assetsCache()->send($matches);
  });

  // Running application
  require_once(__DIR__.'/BrApplication.php');
  $app = new BrApplication();
  $app->main();
}
