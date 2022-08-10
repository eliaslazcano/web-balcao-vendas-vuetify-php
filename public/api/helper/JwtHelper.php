<?php
/**
 * Oferece metodos estaticos que facilitam criar, manipular e validar Json Web Tokens.
 * @author Elias Lazcano Castro Neto
 * @version 2020-04-04
 * @since 7.4
 */

namespace App\Helper;

class JwtHelper
{
  /* === Propriedades === */

  private string $token;
  private string $segredo;

  /**
   * Construtor do Token.
   * @param string|null $segredo Uma string usada para criptografar o token. Se não for passada, será gerada aleatoriamente.
   * @param array|object $payload Array de dados que o token carregará, podendo ser consultado abertamente.
   */
  public function __construct(?string $segredo = null, $payload = [])
  {
    $token = self::tokenCreate($segredo, $payload);

    $this->segredo = $segredo ?: $token['segredo'];
    $this->token = $segredo ? $token : $token['token'];
  }

  public function __toString()
  {
    return $this->getToken();
  }

  /**
   * Obtem o token padronizado em string JWT
   * @return string
   */
  public function getToken(): string
  {
    return $this->token;
  }

  /**
   * Obtem a chave secreta capaz de autenticar o token
   * @return string
   */
  public function getSecret(): string
  {
    return $this->segredo;
  }

  /**
   * Obtem um array com os dados contidos no Token
   * @return object
   */
  public function getPayload(): object
  {
    $part = explode(".", $this->token);
    $payload = $part[1];
    $payload = base64_decode($payload);
    return json_decode($payload);
  }

  /**
   * Indica se o segredo é autentico deste token
   * @param string $segredo A chave que foi utilizada na criação do token
   * @return bool
   */
  public function validateSecret(string $segredo): bool
  {
    $part = explode(".", $this->token);
    $header = $part[0];
    $payload = $part[1];
    $signature = $part[2];

    $valid = hash_hmac('sha256', "$header.$payload", $segredo, true);
    $valid = base64_encode($valid);

    return $signature === $valid;
  }

  /* === Metodos da Classe === */

  /**
   * @param string|null $segredo Uma chave de sua escolha para futuramente validar a autenticidade do token. Se nao informado, sera gerada aleatoriamente.
   * @param array|object $payload Dados incorporados ao token. Evite dados sigilosos como senhas.
   * @return array|string Se voce nao fornecer um segredo, o retorno sera um array com os indices 'token' e 'segredo'
   */
  public static function tokenCreate(?string $segredo = null, $payload = [])
  {
    $key = $segredo ?: md5(uniqid(mt_rand().mt_rand(), true), false);

    $header = [
      'alg' => 'HS256', //algoritmo de criptografia
      'typ' => 'JWT'    //tipo de token
    ];
    $header = json_encode($header);         //converte em string JSON
    $header = base64_encode($header);       //codifica em texto BASE64

    $payload = json_encode((object) $payload);  //converte em string JSON
    $payload = base64_encode($payload);         //codifica em texto BASE64

    $signature = hash_hmac('sha256', "$header.$payload", $key, true); //gera a assinatura
    $signature = base64_encode($signature); //codifica em texto BASE64

    $token = "$header.$payload.$signature";
    return $segredo ? $token : ['segredo' => $key, 'token' => $token];
  }

  /**
   * Valida um token a partir de uma string de segredo, caso ela coincida com a utilizada na sua criação
   * @param string $token O token em string JWT
   * @param string $segredo A string que foi utilizada na criação do token para criptografar
   * @return bool
   */
  public static function tokenValidate(string $token, string $segredo): bool
  {
    $part = explode(".", $token);
    $header = $part[0];
    $payload = $part[1];
    $signature = $part[2];

    $valid = hash_hmac('sha256', "$header.$payload", $segredo, true);
    $valid = base64_encode($valid);

    return $signature === $valid;
  }

  /**
   * Obtem os dados de um Token
   * @param string $token O token em string JWT
   * @param bool $associative
   * @return object|array
   */
  public static function getData(string $token, bool $associative = true)
  {
    $part = explode(".", $token);
    $payload = $part[1];
    $payload = base64_decode($payload);
    return json_decode($payload, $associative);
  }
}
