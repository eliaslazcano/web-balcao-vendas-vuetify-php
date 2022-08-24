<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $id = HttpHelper::obterParametro('id');
  $categorias = $db->query('SELECT id, nome FROM cliente_categorias', [], ['id']);
  if ($id) {
    $x = $db->queryPrimeiraLinha('SELECT id, nome, categoria, digital, criado_em FROM clientes WHERE id = :id', [':id' => $id], ['id','categoria']);
    $x['categorias'] = $categorias;
    $json = $x;
  } else {
    $sql = 'SELECT c.id, c.nome, c.categoria, c.digital, c.criado_em, COUNT(v.id) AS vendas FROM clientes c LEFT JOIN vendas v ON c.id = v.cadastro WHERE c.deletado_em IS NULL GROUP BY c.id';
    $x = $db->query($sql, [], ['id','categoria','vendas']);
    $json = ['clientes' => $x, 'categorias' => $categorias];
  }
  HttpHelper::emitirJson($json);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $nome = HttpHelper::validarParametro('nome');
  $categoria = HttpHelper::obterParametro('categoria');
  $digital = HttpHelper::obterParametro('digital');
  if ($id) {
    $sql = 'UPDATE clientes SET nome = UPPER(:nome), categoria = :categoria, digital = :digital WHERE id = :id';
    $x = $db->update($sql, [':nome' => $nome, ':categoria' => $categoria, ':digital' => $digital ?: null, ':id' => $id]);
    $db->update('UPDATE vendas SET cliente = :nome WHERE cadastro = :cadastro',  [':nome' => $nome, ':cadastro' => $id]);
    HttpHelper::emitirJson(!!$x);
  } else {
    $sql = 'INSERT INTO clientes (nome, categoria, digital) VALUES (UPPER(:nome), :categoria, :digital)';
    $id = $db->insert($sql, [':nome' => $nome, ':categoria' => $categoria, ':digital' => $digital ?: null]);
    HttpHelper::emitirJson($id ? intval($id) : null);
  }
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $x = $db->update('UPDATE clientes SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson(!!$x);
}