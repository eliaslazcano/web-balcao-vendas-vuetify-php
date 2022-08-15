<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $x = $db->query('SELECT id, email, criado_em FROM cliente_emails WHERE cliente = :cliente AND deletado_em IS NULL', [':cliente' => $cadastro]);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $email = HttpHelper::validarParametro('email');
  $id = $db->insert('INSERT INTO cliente_emails (cliente, email) VALUES (:cliente, :email)', [':cliente' => $cadastro, ':email' => mb_strtolower(trim($email))]);
  HttpHelper::emitirJson($id ? intval($id) : null);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $sucesso = $db->update('UPDATE cliente_emails SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson($sucesso > 0);
}