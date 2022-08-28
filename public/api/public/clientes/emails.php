<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $x = $db->query('SELECT id, email, autorizado, criado_em FROM cliente_emails WHERE cliente = :cliente AND deletado_em IS NULL', [':cliente' => $cadastro], ['id'], ['autorizado']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $cadastro = HttpHelper::validarParametro('cadastro');
  $email = HttpHelper::validarParametro('email');
  $autorizado = HttpHelper::obterParametro('autorizado');

  if ($id) {
    $sql = 'UPDATE cliente_emails SET email = :email, autorizado = :autorizado WHERE id = :id';
    $db->update($sql, [':email' => mb_strtolower(trim($email)), ':autorizado' => $autorizado ? 1 : 0, ':id' => $id]);
  } else {
    $sql = 'INSERT INTO cliente_emails (cliente, email, autorizado) VALUES (:cliente, :email, :autorizado)';
    $id = $db->insert($sql, [':cliente' => $cadastro, ':email' => mb_strtolower(trim($email)), ':autorizado' => $autorizado ? 1 : 0]);
  }

  HttpHelper::emitirJson($id ? intval($id) : null);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $sucesso = $db->update('UPDATE cliente_emails SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson($sucesso > 0);
}