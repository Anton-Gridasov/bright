<?php

class BrJobCustomJob {

  private $shellScript = 'nohup /usr/bin/php -f';
  private $runJobScript = 'run-job.php';
  private $checkJobScript = 'check-job.php';

  private $runJobCommand;
  private $checkJobCommand;

  private $coresAmount;
  private $maxProcessesAmountMultiplier = 8;
  private $maxProcessesAmount;

  protected $lastRunFile;

  function __construct() {

    $this->lastRunFile = br()->tempPath() . get_class($this) . '.timestamp';

    $this->checkJobCommand = $this->checkJobScript . ' ' . get_class($this);
    $this->runJobCommand   = $this->runJobScript   . ' ' . get_class($this);

    $this->coresAmount = br()->OS()->getCoresAmount();
    $this->maxProcessesAmount = $this->coresAmount * $this->maxProcessesAmountMultiplier;

  }

  function waitForProcessor() {

    while (br()->OS()->findProcesses(array($this->runJobScript, $this->checkJobScript))->count() > $this->maxProcessesAmount) {
      br()->log('[...] Too many processes started, maximum is ' . $this->maxProcessesAmount . '. Waiting to continue');
      sleep(10);
    }

  }

  private function getCommand($check, $withPath = true, $arguments = '') {

    if ($check) {
      $cmd = trim($this->checkJobCommand . ' ' . $arguments);
    } else {
      $cmd = trim($this->runJobCommand . ' ' . $arguments);
    }

    if ($withPath) {
      $cmd = br()->basePath() . $cmd;
    }

    return $cmd;

  }

  function getCheckCommand($withPath = true, $arguments = '') {

    return $this->getCommand(true, $withPath, $arguments);

  }

  function getRunCommand($withPath = true, $arguments = '') {

    return $this->getCommand(false, $withPath, $arguments);

  }

  function spawn($check, $arguments = '') {

    $this->waitForProcessor();

    $runCommand = $this->getCommand($check, false, $arguments);
    $runCommandWithPath = $this->getCommand($check, true, $arguments);

    br()->log('[CHK] Checking ' . $runCommandWithPath);
    if (br()->OS()->findProcesses($runCommandWithPath)->count() == 0) {
      $logFileName = br()->basePath() . '_logs';
      if (is_writable($logFileName)) {
        $logFileName .= '/' . date('Y-m-d') . '/' . br()->fs()->normalizeFileName(trim($runCommand));
        if (br()->fs()->makeDir($logFileName)) {
          $logFileName .= '/' . date('Y-m-d-H') . '.console.log';
        } else {
          $logFileName = '/dev/null';
        }
      } else {
        $logFileName = '/dev/null';
      }
      $command = $this->shellScript . ' ' . $runCommandWithPath . ' >> ' . $logFileName . ' 2>&1 & echo $!';
      br()->log('[PRC] Starting ' . $command);
      $output = '';
      exec($command, $output);
      br()->log('[PRC] PID ' . @$output[0]);
    } else {
      br()->log('[ERR] Already running');
    }

  }

  function check() {

    if ($list = $this->timeToStart()) {
      if (!is_array($list)) {
        $list = array(null);
      }
      foreach($list as $arguments) {
        $this->spawn(false, $arguments);
      }
    }

  }

  function timeToStart($period = 5) {

    if (file_exists($this->lastRunFile)) {
      return time() - filemtime($this->lastRunFile) > $period * 60;
    } else {
      return true;
    }

  }

  function done() {

    br()->fs()->saveToFile($this->lastRunFile, time());

  }

  function run($params) {

    $this->done();

  }

  static function generateJobScript($name, $path) {

    $name     = ucfirst($name);
    $fileName = $path . '/jobs/Job' . $name . '.php';

    if (file_exists($fileName)) {
      throw new BrAppException('Such job already exists - ' . $fileName);
    } else {
      br()->fs()->saveToFile( $fileName
                            , br()->renderer()->fetchString( br()->fs()->loadFromFile(__DIR__ . '/templates/Job.tpl')
                                                           , array( 'guid' => br()->guid()
                                                                  , 'name' => $name
                                                                  )));
    }

  }

}
