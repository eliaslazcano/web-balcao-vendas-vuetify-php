<?php
/**
 * GET: lista os dispositivos RFIDs conhecidos de acordo com os cadastros na tabela 'dispositivos_rfid'.
 * POST: lista o historico de uso do dispositivo informado.
 * PUT: cadastra um novo RFID, ou atualiza o informado.
 */

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST','PUT','DELETE']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $sql = <<<STR
SELECT dr.id, dr.rfid, dr.descricao, dr.criado_em, COALESCE(COUNT(vr.id), 0) AS usos, MAX(vr.criado_em) AS ultimo_uso,
       MAX(IF(vr.desvinculado_em IS NULL, vr.venda, NULL)) as venda_atual
FROM dispositivos_rfid dr LEFT JOIN venda_rfids vr ON vr.rfid = dr.rfid
WHERE dr.deletado_em IS NULL
GROUP BY dr.id
STR;
  $x = $db->query($sql, [], ['id','usos','vinculo_atual']);
  HttpHelper::emitirJson($x);
}
elseif (HttpHelper::isPost()) {
  $id = HttpHelper::validarParametro('id');
  $sql = 'SELECT vr.id, vr.venda, vr.criado_em, vr.desvinculado_em, v.cliente FROM venda_rfids vr INNER JOIN vendas v ON v.id = vr.venda INNER JOIN dispositivos_rfid dr ON dr.rfid = vr.rfid WHERE dr.id = :id';
  $x = $db->query($sql, [':id' => $id], ['id','venda']);
  HttpHelper::emitirJson($x);
}
elseif (HttpHelper::isPut()) {
  $rfid = HttpHelper::validarParametro('rfid');
  $descricao = HttpHelper::validarParametro('descricao');

  $x = $db->queryPrimeiraLinha('SELECT id, deletado_em FROM dispositivos_rfid WHERE rfid = :rfid', [':rfid' => trim($rfid)], ['id']);
  if ($x) {
    if (!$x['deletado_em']) HttpHelper::erroJson(400, 'Este dispositivo já está cadastrado!');
    $db->update('UPDATE dispositivos_rfid SET deletado_em = NULL WHERE id = :id', [':id' => $x['id']]);
    $id = $x['id'];
  } else {
    $sql = 'INSERT INTO dispositivos_rfid (rfid, descricao) VALUES (:rfid, :descricao)';
    $id = $db->insert($sql, [':rfid' => trim($rfid), ':descricao' => $descricao ?: null]);
  }
  HttpHelper::emitirJson(['id' => intval($id)]);
}
elseif (HttpHelper::isDelete()) {
  $id = HttpHelper::validarParametro('id');
  $db->update('UPDATE dispositivos_rfid SET deletado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $id]);
}