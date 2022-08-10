<?php
/**
 * Entry Point.
 * Este arquivo é o ponto central executado antes de qualquer outro script.
 * @author Elias Lazcano Castro Neto
 * @version 2022-03-13
 */

use App\Helper\HttpHelper;

#Carregando dependencias
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/vendor/autoload.php';

#Ativando sessao local
session_start();

#Ativando log local e configurando o PHP
date_default_timezone_set(\App\Config::TIMEZONE_PHP);
ini_set('memory_limit',\App\Config::MEMORY_LIMIT);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/errors.log');
error_reporting(E_ALL);

#Roteando
$mensagem_notfound = 'Nenhum serviço corresponde a sua solicitação';
$pathInfo = isset($_SERVER['PATH_INFO']);
$origPathInfo = isset($_SERVER['ORIG_PATH_INFO']);
if ($pathInfo) $caminho = $_SERVER['PATH_INFO'];
elseif ($origPathInfo) $caminho = $_SERVER['ORIG_PATH_INFO'];
else HttpHelper::erroJson(404, $mensagem_notfound);

if (!empty($caminho) && substr($caminho, 0, 1) === '/') $caminho = substr($caminho, 1); #Remove a barra inicial
if (empty($caminho)) HttpHelper::erroJson(404, $mensagem_notfound, 2);
else {
  if (file_exists(__DIR__.'/public/' . $caminho . '.php')) {
    require __DIR__ . '/public/' . $caminho . '.php';
  }
  elseif (file_exists(__DIR__.'/public/' . $caminho . '/index.php')) {
    require __DIR__ . '/public/' . $caminho . '/index.php' ;
  }
  else HttpHelper::erroJson(404, $mensagem_notfound, 3, $caminho);
}
