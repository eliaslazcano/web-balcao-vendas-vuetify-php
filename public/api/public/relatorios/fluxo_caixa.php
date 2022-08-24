<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarGet();

$data_inicio = HttpHelper::validarParametro('data_inicio');
$data_fim = HttpHelper::validarParametro('data_fim');

$db = new DbApp();
$sql = 'SELECT COUNT(id) AS vendas, COALESCE(SUM(credito), 0) AS credito FROM vendas WHERE deletado_em IS NULL AND criado_em BETWEEN :inicio AND :fim AND credito > 0';
$vendas = $db->queryPrimeiraLinha($sql, [':inicio' => "$data_inicio 00:00:00", ':fim' => "$data_fim 23:59:00"], ['vendas', 'credito']);

$sql = 'SELECT COUNT(id) AS despesas, COALESCE(SUM(valor), 0) AS debito FROM despesas WHERE deletado_em IS NULL AND criado_em BETWEEN :inicio AND :fim AND valor > 0';
$despesas = $db->queryPrimeiraLinha($sql, [':inicio' => "$data_inicio 00:00:00", ':fim' => "$data_fim 23:59:00"], ['despesas', 'debito']);

HttpHelper::emitirJson([
  'vendas' => $vendas,
  'despesas' => $despesas,
  'credito' => $vendas['credito'],
  'debito' => $despesas['debito'],
]);