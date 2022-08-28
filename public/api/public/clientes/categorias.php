<?php
/**
 * GET: obtem a lista de categorias.
 * POST: cadastra uma nova categoria.
 * DELETE: deleta uma categoria.
 */

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $x = $db->query('SELECT id, nome, criado_em, deletado_em FROM cliente_categorias WHERE deletado_em IS NULL', [], ['id']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $nome = HttpHelper::validarParametro('nome');

  if ($id) $db->update('UPDATE cliente_categorias SET nome = :nome WHERE id = :id', [':nome' => $nome, ':id' => $id]);
  else $id = $db->insert('INSERT INTO cliente_categorias (nome) VALUES (:nome)', [':nome' => $nome]);

  HttpHelper::emitirJson(['id' => $id ? intval($id) : null]);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $db->update('UPDATE cliente_categorias SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  $db->update('UPDATE clientes SET categoria = NULL WHERE categoria = :categoria', [':categoria' => $id]);
}