<?php

/**
 * Project:     Bright framework
 * Author:      Jager Mesh (jagermesh@gmail.com)
 *
 * @version 1.1.0.0
 * @package Bright Core
 */

require_once(__DIR__.'/BrObject.php');

class BrGenericDBProvider extends BrObject {

  function now() {

    return $this->toDateTime(time());

  }

  function today() {

    return $this->toDate(time());

  }

  function startTransaction() {

  }

  function commitTransaction() {

  }

  function rollbackTransaction() {

  }

  function validate($tableName, $row) {

    $dataBaseDictionaryFile = br()->basePath() . 'database/schema/DataBaseDictionary.php';
    if (file_exists($dataBaseDictionaryFile)) {
      require_once($dataBaseDictionaryFile);
      $dataBaseDictionary = DataBaseDictionary::getInstance();
      $dataBaseDictionary->validate($tableName, $row);
    }

  }

}
