<?php
/**
 * GET: envia um email com um codigo utilizado para trocar a senha do usuario informado sem precisar estar autenticado.
 * POST: realiza a troca da senha atraves do codigo de seguranca informado.
 */

use App\Helper\EmailHelper;
use App\Helper\HttpHelper;
use App\Database\DbApp;

HttpHelper::validarMetodos(['GET','POST']);
$db = new DbApp();

if (HttpHelper::isGet()) {
  $login = HttpHelper::validarParametro('login');
  $x = $db->queryPrimeiraLinha('SELECT id, nome, email, inativado_em FROM usuarios WHERE login = :login', [':login' => $login]);
  if (!$x) HttpHelper::erroJson(400, 'Usuário não encontrado');
  if ($x['inativado_em'] !== null) HttpHelper::erroJson(400, 'Acesso negado, a conta está desativada.');
  if (!$x['email']) HttpHelper::erroJson(400, 'A conta não possui nenhum e-mail registrado para enviarmos uma nova senha.');

  $sql = 'SELECT TIMESTAMPDIFF(MINUTE, criado_em, CURRENT_TIMESTAMP) minutos FROM usuario_recoveries WHERE usuario = :usuario AND utilizado_em IS NULL ORDER BY id DESC LIMIT 1';
  $y = $db->queryPrimeiraLinha($sql, [':usuario' => $x['id']], ['minutos']);
  if ($y && $y['minutos'] < 5) HttpHelper::emitirJson(['mensagem' => 'Você já solicitou o código de recuperação de senha recentemente. Aguarde 5 minutos para poder solicitar outro.']);

  $codigo = (string) rand(100000, 999999);

  $emailHelper = new EmailHelper();
  $emailHelper->addDestinatario($x['email'], $x['nome']);
  $sucesso = $emailHelper->enviarMensagem('Recuperação de senha', "Você solicitou a recuperação da sua senha de acesso ao sistema, utilize este código para recuperar: $codigo. O código expira 10 minutos após o envio.", false);
  if (!$sucesso) HttpHelper::erroJson(500, 'Não foi possível enviar te enviar um e-mail para recuperar a senha, problemas na aplicação.');

  $sql = 'INSERT INTO usuario_recoveries (usuario, email, codigo, ip) VALUES (:usuario, :email, :codigo, :ip)';
  $db->update($sql, [':usuario' => $x['id'], ':email' => $x['email'], ':codigo' => $codigo, ':ip' => HttpHelper::obterIp()]);
  HttpHelper::emitirJson(['mensagem' => "Enviamos um código de segurança para o seu e-mail {$x['email']}, ele expira em 10 minutos."]);
}
elseif (HttpHelper::isPost()) {
  $codigo = HttpHelper::validarParametro('codigo');
  $novaSenha = HttpHelper::validarParametro('senha');
  $sql = 'SELECT id, usuario, TIMESTAMPDIFF(MINUTE, criado_em, CURRENT_TIMESTAMP) minutos FROM usuario_recoveries WHERE codigo = :codigo ORDER BY id DESC LIMIT 1';
  $x = $db->queryPrimeiraLinha($sql, [':codigo' => $codigo], ['id','usuario','minutos']);
  if (!$x) HttpHelper::erroJson(400, 'Código inválido');
  elseif ($x['minutos'] > 10) HttpHelper::erroJson(400, 'O código informado expirou, o limite de tempo é de 10 minutos');
  $db->update('UPDATE usuarios SET senha = :senha WHERE id = :id', [':senha' => md5($novaSenha), ':id' => $x['usuario']]);
  $db->update('UPDATE usuario_recoveries SET utilizado_em = CURRENT_TIMESTAMP WHERE id = :id', [':id' => $x['id']]);
  HttpHelper::emitirHttp(200);
}
