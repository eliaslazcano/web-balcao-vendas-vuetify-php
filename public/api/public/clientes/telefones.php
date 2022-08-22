<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $x = $db->query('SELECT id, numero, tipo, criado_em FROM cliente_telefones WHERE cliente = :cliente AND deletado_em IS NULL', [':cliente' => $cadastro], ['id','tipo']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $numero = HttpHelper::validarParametro('numero');
  $tipo = HttpHelper::validarParametro('tipo');

  $sql = 'INSERT INTO cliente_telefones (cliente, numero, tipo) VALUES (:cliente, :numero, :tipo)';
  $id = $db->insert($sql, [':cliente' => $cadastro, ':numero' => trim($numero), ':tipo' => $tipo]);
  HttpHelper::emitirJson($id ? intval($id) : null);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $sucesso = $db->update('UPDATE cliente_telefones SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson($sucesso > 0);
}