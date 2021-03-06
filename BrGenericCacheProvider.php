<?php

/**
 * Project:     Bright framework
 * Author:      Jager Mesh (jagermesh@gmail.com)
 *
 * @version 1.1.0.0
 * @package Bright Core
 */

require_once(__DIR__.'/BrObject.php');

class BrGenericCacheProvider extends BrObject {

  private $cacheTag = null;
  private $cacheLifeTime = 300;

  function __construct($cfg = array()) {

    if ($this->isSupported()) {
      $this->cacheTag = md5(__FILE__);
    } else {
      throw new BrException(get_class($this).' is not supported.');
    }

  }

  public static function isSupported() {

    return true;

  }

  public function reset() {

  }

  public function exists($name) {

    return false;

  }

  public function get($name, $default = null, $saveDefault = false) {

  }

  public function getEx($name) {

  }

  public function set($name, $value, $expirationSeconds = null) {

  }

  public function remove($name) {

  }

  // functions

  public function setCacheLifeTime($seconds) {

    $this->cacheLifeTime = $seconds;

  }

  public function getCacheLifeTime() {

    return $this->cacheLifeTime;

  }

  public function getService() {

  }

  protected function safeName($name) {

    return $this->cacheTag.':'.$name;

  }

}

