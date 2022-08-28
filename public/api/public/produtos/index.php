<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $x = $db->query('SELECT id, nome, categoria, codigo, valor, criado_em FROM produtos WHERE deletado_em IS NULL ORDER BY nome', [], ['id','categoria','valor']);
  $y = $db->query('SELECT id, nome, criado_em FROM produto_categorias WHERE deletado_em IS NULL', [], ['id']);
  HttpHelper::emitirJson(['produtos' => $x, 'categorias' => $y]);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $categoria = HttpHelper::obterParametro('categoria');
  $codigo = HttpHelper::validarParametro('codigo');
  $nome = HttpHelper::validarParametro('nome');
  $valor = HttpHelper::validarParametro('valor');

  if ($codigo) {
    $produto_codigo = $db->queryPrimeiraLinha('SELECT id, nome FROM produtos WHERE codigo = :codigo', [':codigo' => $codigo], ['id']);
    if ($produto_codigo && $produto_codigo['id'] != $id) HttpHelper::erroJson(400, "O código que você colocou está em uso por outro produto: \"{$produto_codigo['nome']}\"");
  }

  if ($id) {
    $sql = 'UPDATE produtos SET nome = UPPER(:nome), codigo = :codigo, valor = :valor, categoria = :categoria WHERE id = :id';
    $x = $db->update($sql, [':nome' => $nome, ':codigo' => $codigo ?: null, ':valor' => $valor, ':categoria' => $categoria ?: null, ':id' => $id]);
    HttpHelper::emitirJson(!!$x);
  } else {
    $sql = 'INSERT INTO produtos (nome, codigo, valor, categoria) VALUES (UPPER(:nome), :codigo, :valor, :categoria)';
    $id = $db->insert($sql, [':nome' => $nome, ':codigo' => $codigo ?: null, ':categoria' => $categoria ?: null, ':valor' => $valor]);
    HttpHelper::emitirJson($id);
  }
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $x = $db->update('UPDATE produtos SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson(!!$x);
}