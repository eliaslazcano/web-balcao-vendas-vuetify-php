<?php
/**
 * GET: obtem os dados do vinculo atual do RFID informado. (venda, data, cliente).
 * POST: vincula o RFID informado a uma venda informada.
 * DELETE: desvincula o RFID informado do seu vinculo atual.
 * PUT: lista os RFIDs vinculados a venda informada.
 * PATH: lista todos os RFIDs ativos no sistema.
 */

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','DELETE','PUT','PATCH']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $rfid = HttpHelper::validarParametro('rfid');

  $sql = <<<STR
SELECT r.id, r.venda, r.criado_em, IF(v.cadastro > 0, c.nome, v.cliente) AS cliente, v.cadastro
FROM venda_rfids r INNER JOIN vendas v ON r.venda = v.id LEFT JOIN clientes c ON v.cadastro = c.id
WHERE r.rfid = :rfid AND r.desvinculado_em IS NULL ORDER BY r.id DESC
STR;
  $x = $db->queryPrimeiraLinha($sql, [':rfid' => $rfid], ['id','venda','cadastro']);
  HttpHelper::emitirJson($x ?: null);

}
elseif (HttpHelper::isPost()) {
  $venda = HttpHelper::validarParametro('venda');
  $rfid = HttpHelper::validarParametro('rfid');

  //Verificar se o dispositivo já está vinculado
  $x = $db->queryPrimeiraLinha('SELECT id, venda FROM venda_rfids WHERE rfid = :rfid AND desvinculado_em IS NULL', [':rfid' => $rfid], ['id','venda']);
  if ($x) {
    if ($x['venda'] == $venda) HttpHelper::emitirJson(['id' => $x['id'], 'mensagem' => 'O dispositivo já estava vinculado a esta venda']);
    else HttpHelper::erroJson(400, 'Acesso negado! O dispositivo está vinculado a outra venda!', 2, ['venda' => $x['venda']]);
  }

  $x = $db->insert('INSERT INTO venda_rfids (rfid, venda) VALUES (:rfid, :venda)', [':rfid' => $rfid, ':venda' => $venda]);
  if (!$x) HttpHelper::erroJson(400, 'Não foi possível vincular o dispositivo a esta venda');
  HttpHelper::emitirJson(['id' => intval($x)]);
}
elseif (HttpHelper::isDelete()) {
  $rfid = HttpHelper::validarParametro('rfid');
  $x = $db->update('UPDATE venda_rfids SET desvinculado_em = CURRENT_TIMESTAMP WHERE rfid = :rfid AND desvinculado_em IS NULL', [':rfid' => $rfid]);
  HttpHelper::emitirJson(['afetados' => $x]);
}
elseif (HttpHelper::isPut()) {
  $venda = HttpHelper::validarParametro('venda');
  $sql = 'SELECT vr.id, vr.rfid, vr.criado_em, dr.id AS id_dispositivo, dr.descricao FROM venda_rfids vr LEFT JOIN dispositivos_rfid dr ON vr.rfid = dr.rfid WHERE vr.venda = :venda AND vr.desvinculado_em IS NULL GROUP BY vr.id';
  $x = $db->query($sql, [':venda' => $venda], ['id']);
  HttpHelper::emitirJson($x);
}
elseif (HttpHelper::isPatch()) {
  $x = $db->query('SELECT vr.id, vr.venda, vr.rfid, vr.criado_em, v.cliente, dr.id AS id_dispositivo, dr.descricao FROM venda_rfids vr LEFT JOIN vendas v ON vr.venda = v.id LEFT JOIN dispositivos_rfid dr ON vr.rfid = dr.rfid WHERE vr.desvinculado_em IS NULL GROUP BY vr.id', [], ['id','venda']);
  HttpHelper::emitirJson($x);
}