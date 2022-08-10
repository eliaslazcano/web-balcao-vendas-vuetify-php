<?php
/**
 * Oferece metodos estaticos que facilitam tratar strings.
 * @author Elias Lazcano Castro Neto
 * @version 2022-01-20
 */

namespace App\Helper;

class StringHelper
{
  /**
   * Obtem o texto convertido em caixa alta
   * @param string $string
   * @param bool $trim
   * @param string $charset
   * @return string
   */
  public static function toUpperCase($string, $trim = true, $charset = 'UTF-8')
  {
    if ($trim) $string = trim($string);
    return mb_strtoupper($string, $charset);
  }

  /**
   * Obtem o texto convertido em caixa baixa
   * @param string $string
   * @param bool $trim
   * @param string $charset
   * @return string
   */
  public static function toLowerCase($string, $trim = true, $charset = 'UTF-8')
  {
    if ($trim) $string = trim($string);
    return mb_strtolower($string, $charset);
  }

  /**
   * Obtem os numeros que aparecem na string
   * @param string $string
   * @param bool $separadosEmArray
   * @return string|string[]
   */
  public static function extractNumbers($string, $separadosEmArray = false) {
    $array = array();
    preg_match_all('/[0-9]+/', $string, $array);
    return ($separadosEmArray) ? $array[0] : array_reduce($array[0], function ($carry, $item) {return $carry.$item;});
  }

  /**
   * Confere se o texto inicia com os caracteres indicados
   * @param $fullString string Texto completo
   * @param $startString string Texto inicial
   * @param bool $caseSensitive Comparacao sensivel a diferenca de caixa alta/baixa
   * @return bool
   */
  public static function startsWith ($fullString, $startString, $caseSensitive = true)
  {
    if (!$caseSensitive) {
      $fullString  = self::toLowerCase($fullString, false);
      $startString = self::toLowerCase($startString, false);
    }
    $len = strlen($startString);
    return substr($fullString, 0, $len) === $startString;
  }

  /**
   * Confere se o texto encerra com os caracteres indicados
   * @param $fullString string Texto completo
   * @param $endString string Texto final
   * @param bool $caseSensitive Comparacao insensivel a diferenca de caixa alta/baixa
   * @return bool
   */
  public static function endsWith($fullString, $endString, $caseSensitive = true)
  {
    if (!$caseSensitive) {
      $fullString  = self::toLowerCase($fullString, false);
      $endString = self::toLowerCase($endString, false);
    }
    $len = strlen($endString);
    if ($len == 0) return true;
    return (substr($fullString, -$len) === $endString);
  }

  /**
   * Remove caracteres acentados.
   * @param $string
   * @return string
   */
  public static function removeAccents($string) {
    return preg_replace(
      array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/",
        "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/",
        "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/", "/(Ç)/"),
      explode(' ',"a A e E i I o O u U n N c C"),
      $string
    );
  }

  /**
   * Remove caracteres numericos da string.
   * @param $string
   * @param $separadosEmArray
   * @return string|string[]
   */
  public static function removeNumbers($string, $separadosEmArray = false) {
    $array = array();
    preg_match_all('/[^0-9]+/', $string, $array);
    return ($separadosEmArray) ? $array[0] : array_reduce($array[0], function ($carry, $item) {return $carry.$item;});
  }

  /**
   * Formata um CPF ou CNPJ para a mascara ideal.
   * @param string $cpf_cnpj
   * @return string
   */
  public static function formatCpfCnpj($cpf_cnpj) {
    $digitos = self::extractNumbers($cpf_cnpj);
    $tamanho = strlen($digitos);
    if ($tamanho !== 11 && $tamanho !== 14) return $cpf_cnpj;
    $mascara = $tamanho === 11 ? '###.###.###-##' : '##.###.###/####-##';
    $mascara_size = strlen($mascara);
    $indice = -1;
    for ($i = 0; $i < $mascara_size; $i++) if ($mascara[$i] === '#') $mascara[$i] = $digitos[++$indice];
    return $mascara;
  }

  /**
   * Converte uma data/datetime para SQL ou BR, invertendo a posicao do DIA com o ANO.
   * @param string $data Detecta automaticamente o caractere separador. Aceita ANO com 2 ou 4 digitos. Pode conter horas.
   * @param string $novo_separador Novo caractere que ira separar DIA, MES e ANO.
   * @return string|false Data invertida. Em caso de falha retorna false.
   */
  public static function formatDate($data, $novo_separador = null) {
    if (strlen($data) < 8) return false; //Tamanho minimo, para datas abreviadas como AA-MM-DD

    //Tenta descobrir qual eh o caractere separador da data atualmente, pegando o primeiro caractere nao-numerico
    $separador = self::removeNumbers($data);
    if (strlen($separador) === 0) return false;
    $separador = $separador[0]; //pega o primeiro caractere

    $data_explodida = explode($separador, $data);
    if (count($data_explodida) !== 3) return false;
    if (!$novo_separador) $novo_separador = $separador;

    //Se contiver horas, devemos posiciona-la sempre no final da string
    if (strpos($data_explodida[2], ' ') !== false && strpos($data_explodida[2], ':') !== false) {
      $data_explodida[0] .= substr($data_explodida[2], strpos($data_explodida[2], ' '));
      $data_explodida[2] = substr($data_explodida[2], 0, strpos($data_explodida[2], ' '));
    }

    return $data_explodida[2] . $novo_separador . $data_explodida[1] . $novo_separador . $data_explodida[0];
  }

  /**
   * Corrige a ausencia de digito zero nas datas, exemplo 9 ao inves de 09.
   * @param $data
   * @return false|string
   */
  public static function fixDate($data)
  {
    if (strlen($data) < 6) return false;

    $separador = self::removeNumbers($data);
    if (strlen($separador) === 0) return false;
    $separador = $separador[0];

    $data_explodida = explode($separador, $data);
    if (count($data_explodida) !== 3) return false;
    return (strlen($data_explodida[0]) === 1 ? '0'.$data_explodida[0] : $data_explodida[0]) . $separador . (strlen($data_explodida[1]) === 1 ? '0'.$data_explodida[1] : $data_explodida[1]) . $separador . (strlen($data_explodida[2]) === 1 ? '0'.$data_explodida[2] : $data_explodida[2]);
  }

  /**
   * Formata um número de telefone, adicionando a mascara ideal de acordo com o cumprimento do numero.
   * @param string $num Somente digitos numericos.
   * @return string
   */
  public static function formatPhone($num)
  {
    $tamanho = strlen($num);
    if ($tamanho === 8) return substr_replace($num, '-', 4, 0);
    elseif ($tamanho === 9) return substr_replace($num, '-', 5, 0);
    elseif ($tamanho === 10) {
      $novo = substr_replace($num, '(', 0, 0);
      $novo = substr_replace($novo, ') ', 3, 0);
      return substr_replace($novo, '-', 9, 0);
    }
    elseif ($tamanho === 11) {
      $novo = substr_replace($num, '(', 0, 0);
      $novo = substr_replace($novo, ') ', 3, 0);
      return substr_replace($novo, '-', 10, 0);
    }
    return $num;
  }

  /**
   * Obtem o primeiro nome de uma pessoa ao fornecer o nome completo.
   * @param $nome_completo
   * @return string
   */
  public static function primeiroNome($nome_completo)
  {
    $nomes = explode(' ', trim($nome_completo));
    return $nomes ? $nomes[0] : '';
  }
}
