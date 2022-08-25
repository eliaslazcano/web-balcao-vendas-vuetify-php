<?php
/**
 * GET: obtem os dados do usuario logado.
 * POST: atualiza os dados do usuario logado.
 * PUT: troca a senha do usuario logado.
 */

use App\Helper\HttpHelper;
use App\Database\DbApp;
use App\Helper\AutenticacaoHelper;

HttpHelper::validarMetodos(['GET','POST','PUT']);
$db = new DbApp();
$payload = AutenticacaoHelper::autenticarSessao($db->getConexao());
try {
  if (HttpHelper::isGet()) {
    $x = $db->queryPrimeiraLinha('SELECT id, nome, login, email FROM usuarios WHERE id = :id', [':id' => $payload['id']], ['id']);
    HttpHelper::emitirJson($x);
  }
  elseif (HttpHelper::isPost()) {
    $nome = HttpHelper::validarParametro('nome');
    $login = HttpHelper::validarParametro('login');
    $email = HttpHelper::obterParametro('email');
    $sql = 'UPDATE usuarios SET nome = :nome, login = :login, email = :email WHERE id = :id';
    $db->update($sql, [':nome' => $nome, ':login' => $login, ':email' => $email ?: null, ':id' => $payload['id']]);
  }
  elseif (HttpHelper::isPut()) {
    $atual = HttpHelper::validarParametro('atual');
    $nova = HttpHelper::validarParametro('nova');
    $x = $db->queryPrimeiraLinha('SELECT senha FROM usuarios WHERE id = :id', [':id' => $payload['id']]);
    if (md5($atual) !== $x['senha']) HttpHelper::erroJson(400, 'A senha atual está incorreta');
    $db->update('UPDATE usuarios SET senha = MD5(:senha) WHERE id = :id', [':senha' => $nova, ':id' => $payload['id']]);
  }
} catch (Exception $e) {
  var_dump($e->getMessage());
}