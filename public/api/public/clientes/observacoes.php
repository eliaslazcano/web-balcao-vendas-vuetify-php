<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $x = $db->query('SELECT id, observacao, criado_em FROM cliente_observacoes WHERE cliente = :cliente AND deletado_em IS NULL', [':cliente' => $cadastro], ['id']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $cadastro = HttpHelper::validarParametro('cadastro');
  $observacao = HttpHelper::validarParametro('observacao');
  if ($id) {
    $db->update('UPDATE cliente_observacoes SET cliente = :cliente, observacao = :observacao WHERE id = :id', [':cliente' => $cadastro, ':observacao' => $observacao, ':id' => $id]);
  } else {
    $id = $db->insert('INSERT INTO cliente_observacoes (cliente, observacao) VALUES (:cliente, :observacao)', [':cliente' => $cadastro, ':observacao' => $observacao]);
  }
  HttpHelper::emitirJson(['id' => intval($id)]);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $db->update('UPDATE cliente_observacoes SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
}