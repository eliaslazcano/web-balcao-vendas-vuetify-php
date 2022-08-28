<?php

namespace App\Helper;

use App\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{
  /**
   * @var PHPMailer
   */
  public PHPMailer $mensageiro;
  public bool $silenciar_erros;

  /**
   * EmailHelper constructor.
   * @param bool $silenciar_erros Impede que as falhas emitam resposta HTTP JSON contendo a mensagem de erro.
   * @param bool $debugar O envio do email sera impresso na saida PHP.
   */
  public function __construct(bool $silenciar_erros = false, bool $debugar = false)
  {
    $mensageiro = new PHPMailer(true);
    $mensageiro->isSMTP();
    $mensageiro->SMTPAuth = true;
    $mensageiro->Host = Config::SMTP_HOST;
    $mensageiro->Username = Config::SMTP_USUARIO;
    $mensageiro->Password = Config::SMTP_SENHA;
    $mensageiro->SMTPSecure = Config::SMTP_TLS ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mensageiro->Port = Config::SMTP_PORTA;
    $mensageiro->Timeout = Config::SMTP_TIMEOUT;
    $mensageiro->SMTPDebug = $debugar ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
    $mensageiro->CharSet = Config::SMTP_CHARSET;
    $this->mensageiro = $mensageiro;
    $this->silenciar_erros = $silenciar_erros;
  }

  /**
   * Adiciona um destinatario a mensagem.
   * @param string $email Endereco de email.
   * @param string|null $nome Nome do destinatario.
   * @return bool Indica o sucesso.
   */
  public function addDestinatario(string $email, ?string $nome = ''): bool
  {
    try {
      $this->mensageiro->addAddress($email, $nome ?: '');
      return true;
    } catch (Exception $e) {
      if (!$this->silenciar_erros) HttpHelper::erroJson(500, 'Ocorreu um erro ao definir o destinatario para mensagem de email', 1, [$e->getMessage(), $this->mensageiro->ErrorInfo]);
      return false;
    }
  }

  /**
   * Adiciona um destinatario CC.
   * @param string $email Endereco de email do destinatario.
   * @param string|null $nome Nome do destinatario.
   * @return bool Indica o sucesso.
   */
  public function addCC(string $email, ?string $nome = ''): bool
  {
    try {
      $this->mensageiro->addCC($email, $nome ?: '');
      return true;
    } catch (Exception $e) {
      if (!$this->silenciar_erros) HttpHelper::erroJson(500, 'Ocorreu um erro ao definir o destinatario CC para mensagem de email', 2, [$e->getMessage(), $this->mensageiro->ErrorInfo]);
      return false;
    }
  }

  /**
   * Adiciona um destinatario oculto (CCO).
   * @param string $email Endereco de email do destinatario.
   * @param string|null $nome Nome do destinatario.
   * @return bool Indica o sucesso.
   */
  public function addCCO(string $email, ?string $nome = ''): bool
  {
    try {
      $this->mensageiro->addBCC($email, $nome ?: '');
      return true;
    } catch (Exception $e) {
      if (!$this->silenciar_erros) HttpHelper::erroJson(500, 'Ocorreu um erro ao definir o destinatario para mensagem de email', 3, [$e->getMessage(), $this->mensageiro->ErrorInfo]);
      return false;
    }
  }

  /**
   * Adiciona um anexo.
   * @param string $path Caminho relativo ou absolito ate o arquivo.
   * @param string $nome Nome apresentavel do arquivo para os leitores do email.
   * @return bool Indica o sucesso.
   */
  public function addAnexo(string $path, string $nome = ''): bool
  {
    try {
      $this->mensageiro->addAttachment($path, $nome);
      return true;
    } catch (Exception $e) {
      if (!$this->silenciar_erros) HttpHelper::erroJson(500, 'Ocorreu um erro ao tentar enviar um anexo por email', 4, [$e->getMessage(), $this->mensageiro->ErrorInfo]);
      return false;
    }
  }

  /**
   * Define um endereco para receber mensagens caso os leitores cliquem em Responder a mensagem.
   * @param string $email Endereco de email.
   * @param string $nome Nome do destinatario.
   * @return bool Indica o sucesso.
   */
  public function addResponderPara(string $email, string $nome): bool
  {
    try {
      $this->mensageiro->addReplyTo($email, $nome);
      return true;
    } catch (Exception $e) {
      if (!$this->silenciar_erros) HttpHelper::erroJson(500, 'Ocorreu um erro ao tentar gerar uma mensagem de email', 5, [$e->getMessage(), $this->mensageiro->ErrorInfo]);
      return false;
    }
  }

  /**
   * Realiza o envio da mensagem.
   * @param string $assunto Assunto da mensagem.
   * @param string $mensagem Texto da mensagem.
   * @param bool $html Ajusta um envio de texto HTML ou texto puro.
   * @return bool Indica o sucesso.
   */
  public function enviarMensagem(string $assunto, string $mensagem, bool $html = true): bool
  {
    $this->mensageiro->isHTML($html);
    $this->mensageiro->Subject = $assunto;
    $this->mensageiro->Body = $mensagem;
    try {
      $this->mensageiro->setFrom(Config::SMTP_REMETENTE_ADDR, Config::SMTP_REMETENTE_NOME);
      $this->mensageiro->send();
      return true;
    } catch (Exception $e) {
      if (!$this->silenciar_erros) HttpHelper::erroJson(500, 'Estamos com problemas para enviar emails, tente mais tarde', 6, [$e->getMessage(), $this->mensageiro->ErrorInfo]);
      return false;
    }
  }
}
