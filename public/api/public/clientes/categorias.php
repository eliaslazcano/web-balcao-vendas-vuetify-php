<?php
use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $id = HttpHelper::validarParametro('id');
  $x = $db->query('SELECT id, nome FROM cliente_categorias WHERE deletado_em IS NULL', [], ['id']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $nome = HttpHelper::validarParametro('nome');
  $id = $db->insert('INSERT INTO cliente_categorias (nome) VALUES (:nome)', [':nome' => $nome]);
  HttpHelper::emitirJson($id ? intval($id) : null);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $sucesso = $db->update('UPDATE cliente_categorias SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson($sucesso > 0);
}