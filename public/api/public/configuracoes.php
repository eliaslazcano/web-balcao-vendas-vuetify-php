<?php

use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarGet();

$db = new DbApp();
$x = $db->queryPrimeiraLinha('SELECT id, nome_empresa FROM configuracoes ORDER BY id DESC', [], ['id']);
HttpHelper::emitirJson($x ?: null);
