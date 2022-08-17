<?php

namespace App\Database;

use App\Config;

class DbApp extends DatabaseController
{
  protected $host = 'localhost';
  protected $usuario = 'eliasl75_viana';
  protected $senha = '2q1.qJw()Xf{';
  protected $base_de_dados = 'eliasl75_viana';
  protected $timezone = Config::TIMEZONE_DB;
}
