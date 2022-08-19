<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $x = $db->query('SELECT id, nome, codigo, valor, criado_em FROM produtos WHERE deletado_em IS NULL ORDER BY nome', [], ['id','valor']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $codigo = HttpHelper::validarParametro('codigo');
  $nome = HttpHelper::validarParametro('nome');
  $valor = HttpHelper::validarParametro('valor');

  if ($codigo) {
    $produto_codigo = $db->queryPrimeiraLinha('SELECT id, nome FROM produtos WHERE codigo = :codigo', [':codigo' => $codigo]);
    if ($produto_codigo && $produto_codigo['id'] !== $id) HttpHelper::erroJson(400, "O código que você colocou está em uso por outro produto: \"{$produto_codigo['nome']}\"");
  }

  if ($id) {
    $sql = 'UPDATE produtos SET nome = UPPER(:nome), codigo = :codigo, valor = :valor WHERE id = :id';
    $x = $db->update($sql, [':nome' => $nome, ':codigo' => $codigo ?: null, ':valor' => $valor, ':id' => $id]);
    HttpHelper::emitirJson(!!$x);
  } else {
    $sql = 'INSERT INTO produtos (nome, codigo, valor) VALUES (UPPER(:nome), :codigo, :valor)';
    $id = $db->insert($sql, [':nome' => $nome, ':codigo' => $codigo ?: null, ':valor' => $valor]);
    HttpHelper::emitirJson($id);
  }
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $x = $db->update('UPDATE produtos SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson(!!$x);
}