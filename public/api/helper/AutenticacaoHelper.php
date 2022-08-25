<?php

namespace App\Helper;

use App\Database\DbApp;
use PDO;

class AutenticacaoHelper
{
  public static function autenticarSessao(?PDO $conn = null, ?string $token = null) {
    if (!$token) $token = HttpHelper::obterCabecalho('Authorization');
    if (!$token) HttpHelper::erroJson(410, 'Token não identificado', 1, ['token' => $token]);
    $payload = JwtHelper::getData($token);
    $db = new DbApp($conn);
    $sessao = $db->queryPrimeiraLinha('SELECT chave FROM sessoes WHERE id = :id', [':id' => $payload['sessao']]);
    if (!$sessao) HttpHelper::erroJson(410, 'Sessão não identificada');
    $sucesso = JwtHelper::tokenValidate($token, $sessao['chave']);
    if (!$sucesso) HttpHelper::erroJson(410, 'Sessão reprovada na autenticação');
    return $payload;
  }
}