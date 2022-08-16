<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);

$db = new DbApp();

if (HttpHelper::isGet()) {
  $id = HttpHelper::obterParametro('id');
  if ($id) {
    $x = $db->queryPrimeiraLinha('SELECT id, descricao, valor, observacao, criado_em FROM despesas WHERE id = :id', [':id' => $id], ['id','valor']);
  } else {
    $data_inicio = HttpHelper::obterParametro('data_inicio') ?: '1900-01-01 00:00:00';
    $data_fim = HttpHelper::obterParametro('data_fim') ?: date('Y-m-d H:i:s');
    $x = $db->query('SELECT id, descricao, valor, criado_em FROM despesas WHERE deletado_em IS NULL AND criado_em BETWEEN :inicio AND :fim', [':inicio' => $data_inicio, ':fim' => $data_fim], ['id','valor']);
  }
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $descricao = HttpHelper::validarParametro('descricao');
  $valor = HttpHelper::obterParametro('valor');
  $observacao = HttpHelper::obterParametro('observacao');

  if (!$id) {
    $id = $db->insert('INSERT INTO despesas (descricao, valor, observacao) VALUES (:descricao, :valor, :observacao)', [':descricao' => $descricao, ':valor' => $valor, ':observacao' => $observacao]);
  } {
    $db->update('UPDATE despesas SET descricao = :descricao, valor = :valor, observacao = :observacao WHERE id = :id', [':descricao' => $descricao, ':valor' => $valor, ':observacao' => $observacao, ':id' => $id]);
  }
  HttpHelper::emitirJson(['id' => intval($id)]);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $db->update('UPDATE despesas SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
}