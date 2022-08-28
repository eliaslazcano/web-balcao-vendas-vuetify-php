<?php
/**
 * Configuracoes do back-end da aplicação.
 * @author Elias Lazcano Castro Neto
 */

namespace App;

class Config
{
  const MEMORY_LIMIT = '128M';
  const TIMEZONE_PHP = 'America/Campo_Grande';
  const TIMEZONE_DB = '-04:00';

  # Configuracoes SMTP
  public const SMTP_HOST = 'smtp.titan.email';
  public const SMTP_PORTA = 465;
  public const SMTP_REMETENTE_ADDR = 'naoresponda@eliaslazcano.dev.br';
  public const SMTP_REMETENTE_NOME = 'Suporte Automatizado';
  public const SMTP_USUARIO = 'naoresponda@eliaslazcano.dev.br';
  public const SMTP_SENHA = 'PWILNx6835';
  public const SMTP_TLS = false;
  public const SMTP_CHARSET = 'utf-8';
  public const SMTP_TIMEOUT = 20;
}
