<?php

namespace Classes\Git;

class Repository {

  private $config = [];
  private $path = '';

  public function __construct($path, $config) {
    $this -> config = $config;
    $this -> path = $path;
  }

  static public function getRepositories($config) {
    $result = [];

    $groupRegex = '#^[a-z0-9-_][a-z0-9-_.]*$#i';
    $repoRegex = '#^([a-z0-9-_][a-z0-9-_.]*)\.git$#i';

    $path = $config -> repositoriesPath;
    $groups = scandir($path);
    foreach ($groups as $group) {
      if (preg_match($groupRegex, $group)) {
        $groupPath = $path . '/' . $group;
        if (is_dir($groupPath)) {
          $repos = scandir($groupPath);
          foreach ($repos as $repo) {
            if (preg_match($repoRegex, $repo, $matches)) {
              $repoPath = $groupPath . '/' . $repo;
              $repo = $matches[1];
              if (is_dir($repoPath)) {
                $repository = new Repository($repoPath, $config);
                @$result[$group][$repo] = $repository;
              }
            }
          }
        }
      }
    }

    return $result;
  }

  static public function getRepository($name, $config) {
    $path = $config -> repositoriesPath . '/' . $name . '.git';
    return new Repository($path, $config);
  }

  public function getCloneUrl() {
    $path = substr($this -> path, strlen($this -> config -> repositoriesPath) + 1);
    return str_replace('{path}', $path, $this -> config -> cloneOverSSHTemplate);
  }

  public function getFiles() {
    list($status, $stdout) = Process::run(
      $this -> config -> gitBinary,
      ['ls-tree', '-r', 'HEAD'],
      $this -> path
    );

    $result = array_map(function($line) {
      $line = trim($line);
      list($description, $path) = explode("\t", $line, 2);
      $description = explode(' ', $description);
      return [
        'path' => $path,
        'commit' => $description[2],
        'mode' => $description[0],
      ];
    }, explode("\n", $stdout));

    return $result;
  }

  public function create() {
    list($status) = Process::run(
      $this -> config -> gitBinary,
      ['init', '--bare', '--shared=0775', $this -> path]
    );
    return $status === 0;
  }

}
