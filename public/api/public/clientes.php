<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $x = $db->query('SELECT id, nome, criado_em FROM clientes WHERE deletado_em IS NULL ORDER BY nome', [], ['id']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $nome = HttpHelper::validarParametro('nome');
  if ($id) {
    $sql = 'UPDATE clientes SET nome = UPPER(:nome) WHERE id = :id';
    $x = $db->update($sql, [':nome' => $nome, ':id' => $id]);
    HttpHelper::emitirJson(!!$x);
  } else {
    $sql = 'INSERT INTO clientes (nome) VALUES (UPPER(:nome))';
    $id = $db->insert($sql, [':nome' => $nome]);
    HttpHelper::emitirJson($id ? intval($id) : null);
  }
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $x = $db->update('UPDATE clientes SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson(!!$x);
}