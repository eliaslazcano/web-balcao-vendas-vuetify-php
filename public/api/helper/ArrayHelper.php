<?php
/**
 * Functions to manipulate arrays like JavaScript
 * @author Elias Lazcano Castro Neto
 * @version 2021-04-27
 */

namespace App\Helper;

class ArrayHelper
{
  public $array = [];

  /**
   * ArrayHelper constructor.
   * @param array $array
   */
  public function __construct(array $array)
  {
    $this->array = $array;
  }
  public function __call($name, $arguments)
  {
    return self::$name($this->array, $arguments[0]);
  }

  /**
   * Filtra os itens do array, retornando apenas aqueles que derem condição verdadeira na função.
   * @param array $array O array que será filtrado.
   * @param callback $function Função que percorre o array, fornecendo o item, esperando o retorno do valor boleano da filtragem.
   * @return array
   */
  public static function filter($array, $function)
  {
    return array_filter($array, $function);
  }

  /**
   * Modifica o array, retornando um novo valor para substituir os valores atuais.
   * @param array $array O array que será modificado.
   * @param callback $function Função que percorre o array, fornecendo o item, esperando o retorno do novo valor.
   * @return array
   */
  public static function map($array, $function)
  {
    return array_map($function, $array);
  }

  /**
   * Procura um valor dentro do array que passe na condição do callback.
   * @param array $array O array que será percorrido.
   * @param callback $function Função que percorre o array, fornecendo o item, esperando o retorno do valor boleano.
   * @return array|null
   */
  public static function find($array, $function)
  {
    $filtered = self::filter($array, $function);
    $filtered = array_values($filtered);
    if (count($filtered)) return $filtered[0];
    else return null;
  }

  /**
   * Higieniza o array, removendo os valores empty (consulte a funcao empty).
   * @param array $array O array que será higienizado.
   * @param bool $preserveIndex Mantem os indices do array.
   * @return array Novo array higienizado.
   */
  public static function removeEmptyItems($array, $preserveIndex = false) {
    $resultado = array_filter($array, function ($item) { return !empty($item); });
    return $preserveIndex ? $resultado : array_values($resultado);
  }

  /**
   * Higieniza o array, removendo os valores NULL.
   * @param array $array O array que será higienizado.
   * @param bool $preserveIndex Mantem os indices do array.
   * @return array Novo array higienizado.
   */
  public static function removeNullItems($array, $preserveIndex = false) {
    $resultado = array_filter($array, function ($item) { return $item !== null; });
    return $preserveIndex ? $resultado : array_values($resultado);
  }

  /**
   * Remove valores duplicados no array, use apenas para tipos primitivos.
   * @param array<string|int|double> $array Array sujo.
   * @param bool $preserveIndex Mantem os indices do array.
   * @return array Novo array higienizado.
   */
  public static function removeDuplicateItems($array, $preserveIndex = false) {
    $resultado = array_unique($array);
    return $preserveIndex ? $resultado : array_values($resultado);
  }

  /**
   * Converte os valores String de uma matriz em numéricos, ou apenas das colunas citadas.
   * @param array<array> $matrix
   * @param array<string> $columns
   * @return array<array>
   */
  public static function matrixStringToNumber($matrix, $columns = null)
  {
    foreach ($matrix as $line => $lineValue) {
      foreach ($lineValue as $column => $columnValue) {
        if ($columns === null || (in_array($column, $columns))) {
          if (is_numeric($columnValue)) $matrix[$line][$column] = $columnValue + 0;
        }
      }
    }
    return $matrix;
  }

  /**
   * Converte os valores Booleanos de uma matriz em numéricos, ou apenas das colunas citadas.
   * @param array<array> $matrix
   * @param array<string> $columns
   * @return array<array>
   */
  public static function matrixStringToBool($matrix, $columns = null)
  {
    foreach ($matrix as $line => $lineValue) {
      foreach ($lineValue as $column => $columnValue) {
        if ($columns === null || (in_array($column, $columns))) {
          $matrix[$line][$column] = boolval($columnValue);
        }
      }
    }
    return $matrix;
  }
}
