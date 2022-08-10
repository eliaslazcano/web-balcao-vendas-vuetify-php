<?php
/**
 * Classe Abstrata, nao consegue ser instanciada.
 *
 * Classe documentada nos padroes intellij para que as IDEs consigam automatizar a escrita do programador.
 *
 * Esta classe serve para criar classes que conectam com o banco de dados, herdando esta estrutura.
 *
 * Qual o beneficio? Escrevendo apenas uma linha eh possivel realizar uma query e retornar o dado desejado:
 * - Realizar uma consulta e obter todos os resultados em array: query();
 * - Realizar uma consulta e obter apenas a primeira linha do resultado: queryPrimeiraLinha();
 * - Realizar um INSERT e ja obter o ID do seu novo registro: insert();
 * - Realizar um UPDATE e ja obter o numero de linhas afetadas por ele: update();
 *
 * @author Elias Lazcano Castro Neto
 * @version 2022-01-12
 * @since 5.3
 */

namespace App\Database;

use PDO;
use PDOException;
use PDOStatement;

abstract class DatabaseController
{
  protected $host;
  protected $usuario;
  protected $senha;
  protected $base_de_dados;
  protected $charset = 'utf8';
  protected $timezone = '-03:00';
  private $conexao;
  public $forceColunasCaixaBaixa = false;

  public function __construct(PDO $conn = null)
  {
    if ($conn) $this->conexao = $conn;
    else {
      $dsn = "mysql:host=$this->host;dbname=$this->base_de_dados";
      if ($this->charset) $dsn .= ";charset=$this->charset;";
      try {
        $conn = new PDO($dsn, trim($this->usuario), trim($this->senha));
        if ($this->timezone) $conn->exec("SET time_zone='$this->timezone';");
        $this->conexao = $conn;
      } catch (PDOException $e) {
        $this->aoFalhar($e->getMessage());
      }
    }
  }

  /**
   * @return PDO
   */
  public function getConexao(): PDO
  {
    return $this->conexao;
  }

  /**
   * Comportamento em caso de falha.
   * @param string $mensagem
   */
  protected function aoFalhar(string $mensagem) {
    trigger_error($mensagem, E_USER_ERROR);
  }

  /**
   * Adapta colunas para a tipagem adequada.
   * @param array $matriz
   * @param string[] $colunasNumericas
   * @param string[] $colunasBoleanas
   * @return array
   */
  private function tiparMatriz(array $matriz = array(), array $colunasNumericas = array(), array $colunasBoleanas = array()): array
  {
    $forceColunasCaixaBaixa = $this->forceColunasCaixaBaixa;
    if ($forceColunasCaixaBaixa) {
      $colunasNumericas = array_map('strtolower', $colunasNumericas);
      $colunasBoleanas = array_map('strtolower', $colunasBoleanas);
    }
    return array_map(function ($linha) use ($forceColunasCaixaBaixa, $colunasNumericas, $colunasBoleanas) {
      if ($forceColunasCaixaBaixa) $linha = array_change_key_case($linha, CASE_LOWER);
      foreach ($linha as $coluna => $valor) {
        if (in_array($coluna, $colunasNumericas)) {
          if (is_numeric($valor)) $linha[$coluna] = $valor + 0;
        }
        if (in_array($coluna, $colunasBoleanas)) {
          if ($valor !== null) $linha[$coluna] = (boolean) $valor;
        }
      }
      return $linha;
    }, $matriz);
  }

  /**
   * Obtem um statement do PDO, com "prepare" e "bind" realizados.
   * @param string $query
   * @param array<string, mixed> $bindParams
   * @return PDOStatement
   */
  public function statementPreparado(string $query, array $bindParams = array()): PDOStatement
  {
    $statement = $this->conexao->prepare($query);
    if (!$statement) $this->aoFalhar(json_encode($this->conexao->errorInfo()));
    if (!empty($bindParams)) {
      foreach ($bindParams as $key => $value) {
        if (is_int($value)) $statement->bindValue($key, $value, PDO::PARAM_INT);
        elseif (is_bool($value)) $statement->bindValue($key, $value, PDO::PARAM_BOOL);
        elseif (is_null($value)) $statement->bindValue($key, $value, PDO::PARAM_NULL);
        elseif (is_string($value)) $statement->bindValue($key, $value);
        else $statement->bindValue($key, strval($value));
      }
    }
    return $statement;
  }

  /**
   * Obtem um statement do PDO, com "bind" e "execute" realizados.
   * @param string $query
   * @param array<string, mixed> $bindParams
   * @return PDOStatement
   */
  public function statementExecutado(string $query, array $bindParams = array()): PDOStatement
  {
    $statement = $this->statementPreparado($query, $bindParams);
    if (!$statement->execute()) $this->aoFalhar(json_encode($statement->errorInfo()));
    return $statement;
  }

  /**
   * Realiza uma query, retornando o resultado em array[linha]['coluna'].
   * @param string $query Query SQL.
   * @param array $bindParams Parametros da query que serao substituidos exemplo: [':id' => $id]
   * @param array $colunasNumericas O nome das colunas que devem vir tipadas em numero.
   * @param array $colunasBoleanas O nome das colunas que devem vir tipadas em boleano.
   * @param int $fetch_mode Modo FETCH do PDO
   * @return array<int, array<string, mixed>> array[linha]['coluna'].
   */
  public function query(string $query, array $bindParams = array(), array $colunasNumericas = array(), array $colunasBoleanas = array(), int $fetch_mode = PDO::FETCH_ASSOC): array
  {
    $statement = $this->statementExecutado($query, $bindParams);
    $linhas = $statement->fetchAll($fetch_mode);

    // Ajusta a tipagem dos dados, pois o PDO sempre retorna tudo em String.
    if ($this->forceColunasCaixaBaixa || count($colunasNumericas) > 0 || count($colunasBoleanas) > 0) {
      $linhas = $this->tiparMatriz($linhas, $colunasNumericas, $colunasBoleanas);
    }

    return $linhas;
  }

  /**
   * Realiza uma query e retorna a primeira linha do resultado. Em array['coluna']. Se nao houver nenhuma linha, retorna null.
   * @param string $query Query SQL.
   * @param array $bindParams Parametros da query que serao substituidos exemplo: [':id' => $id]
   * @param array $colunasNumericas O nome das colunas que devem vir tipadas em numero.
   * @param array $colunasBoleanas O nome das colunas que devem vir tipadas em boleano.
   * @param int $fetch_mode Modo FETCH do PDO
   * @return array<string, mixed>|false False caso nao tenha nenhuma linha no resultado.
   */
  public function queryPrimeiraLinha(string $query, array $bindParams = array(), array $colunasNumericas = array(), array $colunasBoleanas = array(), int $fetch_mode = PDO::FETCH_ASSOC) {
    $linhas = $this->query($query, $bindParams, $colunasNumericas, $colunasBoleanas, $fetch_mode);
    if (count($linhas) === 0) return false;
    else return $linhas[0];
  }

  /**
   * Executa uma instrução SQL de inserção, tenta retornar o ID (chave primaria) do registro inserido.
   * @param string $sql Instrucao SQL.
   * @param array $bindParams Valores para insercao.
   * @return string Chave primaria.
   */
  public function insert(string $sql, array $bindParams = array()): string
  {
    if (strpos(strtoupper(substr($sql, 0, 11)), 'INSERT INTO') === false) $this->aoFalhar('Operação de inserção inválida');
    $this->query($sql, $bindParams);
    return $this->conexao->lastInsertId();
  }

  /**
   * Executa uma instrução SQL de atualização (UPDATE), tenta retornar o número de linhas afetadas.
   * @param string $sql Instrução SQL com UPDATE.
   * @param array $bindParams Valores da instrução.
   * @return int Quantiade de linhas afetadas.
   */
  public function update(string $sql, array $bindParams = array()): int
  {
    $statement = $this->statementExecutado($sql, $bindParams);
    return $statement->rowCount();
  }

  /**
   * Obtem o ID (Chave primaria) do ultimo INSERT realizado com a conexao atual.
   * @return string
   */
  public function idUltimaInsercao(): string
  {
    return $this->conexao->lastInsertId();
  }

  /**
   * Obtem a data atual do banco em formato "AAAA-MM-DD".
   * @return string|false
   */
  public function dateAtual() {
    $resultado = $this->queryPrimeiraLinha('SELECT current_date() AS date');
    return $resultado ? $resultado['date'] : false;
  }

  /**
   * Obtem a data-hora atual do banco em formato "AAAA-MM-DD hh:mm:ss".
   * @return string|false
   */
  public function datetimeAtual() {
    $resultado = $this->queryPrimeiraLinha('SELECT current_timestamp() AS datetime');
    return $resultado ? $resultado['datetime'] : false;
  }

  /**
   * Obtem o numero correspondente ao dia da semana, 1 = domingo ate 7 = sabado.
   * @return int|false
   */
  public function diaDaSemanaAtual() {
    $resultado = $this->queryPrimeiraLinha('SELECT dayofweek(current_date()) AS numero');
    return $resultado ? intval($resultado['numero']) : false;
  }

  /**
   * Exporta os dados de uma tabela do banco de dados para um arquivo CSV.
   * @param string $tabela Nome da tabela.
   * @param string[] $colunas Nome das colunas. Se quiser todas, use NULL ou um array vazio.
   * @param boolean $incluir_cabecalho O nome das colunas sera inserido na primeira linha do CSV.
   * @param string $nome_do_arquivo
   * @param string|null $diretorio Local para salvar o arquivo, NULL realiza o download no browser do cliente.
   */
  public function exportarCsv_deTabela(string $tabela, array $colunas = array(), bool $incluir_cabecalho = true, string $nome_do_arquivo = 'exportar.csv', string $diretorio = null) {
    $str_colunas = $colunas ? implode(', ', $colunas) : '*';
    $this->exportarCsv_deQuery("SELECT $str_colunas FROM $tabela", $incluir_cabecalho, $nome_do_arquivo, $diretorio);
  }

  /**
   * Exporta uma query para um arquivo CSV.
   * @param string $query Query de consulta.
   * @param bool $incluir_cabecalho O nome das colunas sera inserido na primeira linha do CSV.
   * @param string $nome_do_arquivo
   * @param string|null $diretorio Local para salvar o arquivo, NULL realiza o download no browser do cliente.
   */
  public function exportarCsv_deQuery(string $query, bool $incluir_cabecalho = true, string $nome_do_arquivo = 'exportar.csv', string $diretorio = null) {
    $resultado = $this->query($query);
    self::exportarCsv_deArray($resultado, $incluir_cabecalho, $nome_do_arquivo, $diretorio);
  }

  /**
   * Exporta um array[][] para arquivo CSV
   * @param array<int, array<string, mixed>> $array
   * @param bool $incluir_cabecalho O nome das colunas sera inserido na primeira linha do CSV.
   * @param string $nome_do_arquivo
   * @param string|null $diretorio Local para salvar o arquivo, NULL realiza o download no browser do cliente.
   */
  public static function exportarCsv_deArray(array $array, bool $incluir_cabecalho = true, string $nome_do_arquivo = 'exportar.csv', string $diretorio = null) {
    if (!$diretorio) {
      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="'.$nome_do_arquivo.'"');
      header('Pragma: no-cache');
      header('Expires: 0');
    }
    $resource = fopen($diretorio ? ($diretorio.'/'.$nome_do_arquivo) : 'php://output', 'w');
    if ($incluir_cabecalho) {
      if (is_array($array[0])) fputcsv($resource, array_keys($array[0]));
      else fputcsv($resource, array_keys($array));
    }
    if (is_array($array[0])) {
      foreach ($array as $linha) fputcsv($resource, $linha);
    } else {
      fputcsv($resource, $array);
    }
    fclose($resource);
    if (!$diretorio) die();
  }
}
