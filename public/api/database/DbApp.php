<?php

namespace App\Database;

use App\Config;

class DbApp extends DatabaseController
{
  protected $host = 'localhost';
  protected $usuario = 'root';
  protected $senha = '';
  protected $base_de_dados = 'balcao-venda';
  protected $timezone = Config::TIMEZONE_DB;
}
