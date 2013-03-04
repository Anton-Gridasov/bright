<?php

/**
 * Project:     Bright framework
 * Author:      Jager Mesh (jagermesh@gmail.com)
 *
 * @version 1.1.0.0
 * @package Bright Core
 */


require_once(__DIR__.'/BrImage.php');

class BrImages extends BrSingleton {

  function isValid($path) {

    try {
      new BrImage($path);
      return true;
    } catch (Exception $e) {
      return false;
    }

  }

  function generateThumbnail($src, $w, $h, $relativePath = null) {

    $path = $src;

    if (!preg_match('~^/~', $path)) {
      $path = br()->atBasePath($path);
    }

    if (!file_exists($path)) {
      $path = br()->atBasePath($path);
    }

    if (!file_exists($path) && $relativePath) {
      $path = $relativePath . $src;
    }

    if (!file_exists($path)) {
      return $src;
    }

    $pathinfo = pathinfo($path);

    $dst = str_replace($pathinfo['basename'], $w.'x'.$h.'/'.$pathinfo['basename'], $src);
    $dstPath = $pathinfo['dirname'].'/'.$w.'x'.$h;

    br()->fs()->makeDir($dstPath);

    $dstPath .= '/'.$pathinfo['basename'];

    if (file_exists($dstPath)) {

      return $dst;
      
    } else {

      br()->log()->writeLn('Creating thumbnail from ' . $src . ' in ' . $dstPath);

      $image = new BrImage($path);
      $image->generateThumbnail($w, $h, $dstPath);

      return $dst;

    }

  }

}

