<?php

class BrJobsManager {

  function __construct() {

    $this->jobsFolder = br()->basePath() . 'jobs/';

  }

  function run() {

    if (!br()->isConsoleMode()) { br()->panic('Console mode only'); }
    $handle = br()->OS()->lockIfRunning(br()->callerScript());

    br()->log('[...] Jobs folder: ' . $this->jobsFolder);

    br()->log('[...] Starting JobsManager');
    while (true) {
      $jobs = array();
      br()->fs()->iterateDir($this->jobsFolder, '^Job.*[.]php$', function($jobFile) use (&$jobs) {
        $jobs[] = array( 'classFile' => $jobFile->nameWithPath(), 'className' => br()->fs()->fileNameOnly($jobFile->name()) );
      });
      foreach ($jobs as $jobDesc) {
        br()->log('[PRC] Starting process checker for ' . $jobDesc['className']);
        $classFile = $jobDesc['classFile'];
        $className = $jobDesc['className'];
        require_once($classFile);
        $job = new $className();
        $job->spawn(true);
      }
      br()->log('[...] Idle');
      sleep(30);
    }

  }

}
