<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarGet();
$cadastro = HttpHelper::validarParametro('cadastro');
$db = new DbApp();
$sql = <<<STR
SELECT v.id, v.criado_em, v.encerrado_em, COALESCE(SUM(vi.valor),0) AS debito, v.credito
FROM vendas v LEFT JOIN clientes c ON v.cadastro = c.id LEFT JOIN venda_itens vi ON v.id = vi.venda
WHERE v.deletado_em IS NULL AND cadastro = :cadastro GROUP BY v.id
STR;
$x = $db->query($sql, [':cadastro' => $cadastro], ['id','debito','credito']);
HttpHelper::emitirJson($x);
