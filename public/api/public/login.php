<?php

use App\Database\DbApp;
use App\Helper\HttpHelper;
use App\Helper\JwtHelper;

HttpHelper::validarPost();
$login = HttpHelper::validarParametro('login');
$senha = HttpHelper::validarParametro('senha');
$agente = HttpHelper::obterParametro('agente');
$ip = HttpHelper::obterIp();

$db = new DbApp();
$usuario = $db->queryPrimeiraLinha('SELECT id, senha, nome, inativado_em FROM usuarios WHERE login = :login', [':login' => $login], ['id']);
if (!$usuario) HttpHelper::erroJson(400, 'Nome de usuário não encontrado');
if ($usuario['inativado_em'] !== null) HttpHelper::erroJson(400, 'Sua conta está desativada');
if ($usuario['senha'] !== md5($senha)) HttpHelper::erroJson(400, 'Senha incorreta');

$sql = 'INSERT INTO sessoes (usuario, ip, agente) VALUES (:usuario, :ip, :agente)';
$sessao_id = (int) $db->insert($sql, [':usuario' => $usuario['id'], ':ip' => $ip, ':agente' => $agente ?: null]);
$jwt = new JwtHelper(null, ['id' => $usuario['id'], 'nome' => $usuario['nome'], 'sessao' => $sessao_id]);
$db->update('UPDATE sessoes SET chave = :chave WHERE id = :id', [':chave' => $jwt->getSecret(), ':id' => $sessao_id]);
HttpHelper::emitirJson(['token' => $jwt->getToken()]);
