<?php

namespace App\Database;

use App\Config;

class DbApp extends DatabaseController
{
  protected $host = 'eliaslazcano.dev.br';
  protected $usuario = 'eliasl75_barjk';
  protected $senha = 'LHn8tUYoBp@8';
  protected $base_de_dados = 'eliasl75_barjk';
  protected $timezone = Config::TIMEZONE_DB;
}
