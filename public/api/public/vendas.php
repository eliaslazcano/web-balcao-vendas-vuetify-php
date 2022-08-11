<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $id = HttpHelper::obterParametro('id');
  if ($id) {
    $x = $db->queryPrimeiraLinha('SELECT v.id, COALESCE(c.nome, v.cliente) AS cliente, v.cadastro, v.credito, v.criado_em FROM vendas v LEFT JOIN clientes c ON v.cadastro = c.id WHERE v.id = :id', [':id' => $id], ['id','cadastro','credito']);
    if (!$x)HttpHelper::erroJson(400, 'Venda não encontrada');
    $x['itens'] = $db->query('SELECT v.id, v.produto, p.nome AS produto_nome, v.quantidade, v.valor FROM venda_itens v INNER JOIN produtos p on v.produto = p.id WHERE v.venda = :id', [':id' => $id], ['id','produto','quantidade','valor']);
  }
  else $x = $db->query('SELECT v.id, COALESCE(c.nome, v.cliente) AS cliente, v.criado_em, SUM(vi.valor) AS debito, v.credito FROM vendas v LEFT JOIN clientes c ON v.cadastro = c.id LEFT JOIN venda_itens vi ON v.id = vi.venda GROUP BY v.id', [], ['id','debito','credito']);
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $cliente = HttpHelper::validarParametro('cliente');
  $cadastro = HttpHelper::validarParametro('cadastro');
  $credito = HttpHelper::validarParametro('credito');
  $itens = HttpHelper::validarParametro('itens'); //Array

  if (empty($itens)) HttpHelper::erroJson(400, 'A venda está sem nenhum produto');

  if (!$id) $id = $db->insert('INSERT INTO vendas (cliente, credito, cadastro) VALUES (UPPER(:cliente), :credito, :cadastro)', [':cliente' => $cliente ?: null, ':credito' => $credito ?: 0, ':cadastro' => $cadastro]);
  else $db->update('UPDATE vendas SET cliente = UPPER(:cliente), credito = :credito, cadastro = :cadastro WHERE id = :id', [':cliente' => $cliente ?: null, ':credito' => $credito ?: 0, ':cadastro' => $cadastro, ':id' => $id]);

  foreach ($itens as $i) {
    $item_encontrado = $db->queryPrimeiraLinha('SELECT id FROM venda_itens WHERE venda = :venda AND produto = :produto', [':venda' => $id, ':produto' => $i['produto']]);
    if ($item_encontrado) $db->update('UPDATE venda_itens SET quantidade = :quantidade, valor = :valor WHERE id = :id', [':quantidade' => $i['quantidade'], ':valor' => $i['valor'], ':id' => $item_encontrado['id']]);
    else $db->insert('INSERT INTO venda_itens (venda, produto, quantidade, valor) VALUES (:venda, :produto, :quantidade, :valor)', [':venda' => $id, ':produto' => $i['produto'], ':quantidade' => $i['quantidade'], ':valor' => $i['valor']]);
  }

  HttpHelper::emitirJson(['id' => intval($id)]);
}
