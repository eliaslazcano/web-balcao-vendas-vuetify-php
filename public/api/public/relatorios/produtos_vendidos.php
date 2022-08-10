<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarGet();

$data_inicio = HttpHelper::validarParametro('data_inicio');
$data_fim = HttpHelper::validarParametro('data_fim');

$sql = 'SELECT p.id, p.nome, SUM(vi.quantidade) AS quantidade, SUM(vi.valor) AS valor FROM produtos p LEFT JOIN venda_itens vi ON p.id = vi.produto INNER JOIN vendas v ON vi.venda = v.id WHERE v.criado_em BETWEEN :inicio AND :fim GROUP BY p.id';
$db = new DbApp();
$resultado = $db->query($sql, [':inicio' => "$data_inicio 00:00:00", ':fim' => "$data_fim 23:59:00"], ['id','quantidade','valor']);
HttpHelper::emitirJson($resultado);