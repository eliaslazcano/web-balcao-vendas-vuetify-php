<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $id = HttpHelper::obterParametro('id');
  if ($id) {
    $x = $db->queryPrimeiraLinha('SELECT id, nome, digital, criado_em FROM clientes WHERE id = :id', [':id' => $id], ['id']);
  } else {
    $x = $db->query('SELECT id, nome, digital, criado_em FROM clientes WHERE deletado_em IS NULL ORDER BY nome', [], ['id']);
  }
  $configuracao = $db->queryPrimeiraLinha('SELECT biometria_nitgen FROM configuracoes ORDER BY id DESC LIMIT 1', [], [], ['biometria_nitgen']);
  HttpHelper::emitirJson([
    'biometria_nitgen' => $configuracao ? $configuracao['biometria_nitgen'] : false,
    'dados' => $x,
  ]);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $nome = HttpHelper::validarParametro('nome');
  $digital = HttpHelper::obterParametro('digital');
  if ($id) {
    $sql = 'UPDATE clientes SET nome = UPPER(:nome), digital = :digital WHERE id = :id';
    $x = $db->update($sql, [':nome' => $nome, ':digital' => $digital ?: null, ':id' => $id]);
    HttpHelper::emitirJson(!!$x);
  } else {
    $sql = 'INSERT INTO clientes (nome, digital) VALUES (UPPER(:nome), :digital)';
    $id = $db->insert($sql, [':nome' => $nome, ':digital' => $digital ?: null]);
    HttpHelper::emitirJson($id ? intval($id) : null);
  }
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $x = $db->update('UPDATE clientes SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson(!!$x);
}