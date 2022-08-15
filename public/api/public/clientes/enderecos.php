<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $x = $db->query('SELECT id, cep, uf, bairro, cidade, logradouro, criado_em FROM cliente_enderecos WHERE cliente = :cliente AND deletado_em IS NULL', [':cliente' => $cadastro]);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $cadastro = HttpHelper::validarParametro('cadastro');
  $cep = HttpHelper::validarParametro('cep');
  $uf = HttpHelper::validarParametro('uf');
  $bairro = HttpHelper::validarParametro('bairro');
  $cidade = HttpHelper::validarParametro('cidade');
  $logradouro = HttpHelper::validarParametro('logradouro');

  $sql = 'INSERT INTO cliente_enderecos (cliente, cep, uf, bairro, cidade, logradouro) VALUES (:cliente, :cep, :uf, :bairro, :cidade, :logradouro)';
  $id = $db->insert($sql, [':cliente' => $cadastro, ':cep' => $cep, ':uf' => $uf, ':bairro' => $bairro, ':cidade' => $cidade, ':logradouro' => $logradouro]);
  HttpHelper::emitirJson($id ? intval($id) : null);
} elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $sucesso = $db->update('UPDATE cliente_enderecos SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
  HttpHelper::emitirJson($sucesso > 0);
}