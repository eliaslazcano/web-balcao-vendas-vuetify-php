<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','PUT']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $id = HttpHelper::obterParametro('id');
  if ($id) {
    $sql = 'SELECT v.id, COALESCE(c.nome, v.cliente) AS cliente, v.cadastro, v.nota, v.credito, v.criado_em FROM vendas v LEFT JOIN clientes c ON v.cadastro = c.id WHERE v.id = :id';
    $x = $db->queryPrimeiraLinha($sql, [':id' => $id], ['id','cadastro','credito']);
    if (!$x)HttpHelper::erroJson(400, 'Venda não encontrada');
    $x['itens'] = $db->query('SELECT v.id, v.produto, p.nome AS produto_nome, v.quantidade, v.valor_un, v.valor FROM venda_itens v INNER JOIN produtos p on v.produto = p.id WHERE v.venda = :id', [':id' => $id], ['id','produto','quantidade','valor_un','valor']);
    if ($x['cadastro']) {
      $sql = 'SELECT c.id, COUNT(v.id) as visitas FROM clientes c LEFT JOIN vendas v on c.id = v.cadastro WHERE c.id = :cliente AND v.criado_em <= :data GROUP BY c.id';
      $visitas = $db->queryPrimeiraLinha($sql, [':cliente' => $x['cadastro'], ':data' => $x['criado_em']], ['visitas']);
      $x['visita'] = $visitas['visitas'];
    }
  }
  else {
    $data_inicio = HttpHelper::obterParametro('data_inicio') ?: '1900-01-01 00:00:00';
    $data_fim = HttpHelper::obterParametro('data_fim') ?: date('Y-m-d H:i:s');
    $sql = 'SELECT v.id, COALESCE(c.nome, v.cliente) AS cliente, v.criado_em, COALESCE(SUM(vi.valor),0) AS debito, v.credito FROM vendas v LEFT JOIN clientes c ON v.cadastro = c.id LEFT JOIN venda_itens vi ON v.id = vi.venda WHERE v.deletado_em IS NULL AND v.criado_em BETWEEN :inicio AND :fim GROUP BY v.id';
    $x = $db->query($sql, [':inicio' => $data_inicio, ':fim' => $data_fim], ['id', 'debito', 'credito']);
  }
  HttpHelper::emitirJson($x);
} elseif (HttpHelper::isPost()) {
  $id = HttpHelper::obterParametro('id');
  $cliente = HttpHelper::validarParametro('cliente');
  $cadastro = HttpHelper::validarParametro('cadastro');
  $nota = HttpHelper::obterParametro('nota');
  $credito = HttpHelper::validarParametro('credito');
  $itens = HttpHelper::validarParametro('itens'); //Array
  $remover = HttpHelper::obterParametro('remover'); //Array de ID dos itens que tem de ser removidos da venda

  if (!$id) {
    $sql = 'INSERT INTO vendas (cliente, credito, cadastro, nota) VALUES (UPPER(:cliente), :credito, :cadastro, :nota)';
    $id = $db->insert($sql, [':cliente' => $cliente ?: null, ':credito' => $credito ?: 0, ':cadastro' => $cadastro, ':nota' => $nota ?: null]);
  }
  else {
    $sql = 'UPDATE vendas SET cliente = UPPER(:cliente), credito = :credito, cadastro = :cadastro, nota = :nota WHERE id = :id';
    $db->update($sql, [':cliente' => $cliente ?: null, ':credito' => $credito ?: 0, ':cadastro' => $cadastro, ':nota' => $nota ?: null, ':id' => $id]);
  }

  foreach ($itens as $i) {
    if ($i['id']) {
      $item_encontrado = $db->queryPrimeiraLinha('SELECT id FROM venda_itens WHERE id = :id', [':id' => $i['id']]);
      if ($item_encontrado) $db->update('UPDATE venda_itens SET quantidade = :quantidade, valor_un = :valor_un, valor = :valor WHERE id = :id', [':quantidade' => $i['quantidade'], ':valor_un' => $i['valor_un'], ':valor' => $i['valor'], ':id' => $i['id']]);
    }
    else {
      $sql = 'INSERT INTO venda_itens (venda, produto, quantidade, valor_un, valor) VALUES (:venda, :produto, :quantidade, :valor_un, :valor)';
      $db->insert($sql, [':venda' => $id, ':produto' => $i['produto'], ':quantidade' => $i['quantidade'], ':valor_un' => $i['valor_un'], ':valor' => $i['valor']]);
    }
  }

  foreach ($remover as $i) {
    $db->update('DELETE FROM venda_itens WHERE id = :id', [':id' => $i]);
  }

  HttpHelper::emitirJson(['id' => intval($id), 'itens' => $itens, 'remover' => $remover]);
}
