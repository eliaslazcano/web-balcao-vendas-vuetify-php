export default class StringHelper {

  /**
   * Abrevia o nome para duas palavras.
   * @param {string} fullname - Nome completo.
   * @param {boolean} useSecondName - Usar o segundo nome ao invés do último.
   * @returns {string} - Nome abreviado.
   */
  static shortName(fullname, useSecondName = false) {
    fullname = fullname.trim();
    if (!fullname) return '';
    const names = fullname.split(' ');
    if (names.length === 1) return names[0];
    else return names[0] + ' ' + (useSecondName ? names[1] : names[names.length - 1]);
  }

  /**
   * Obtem duas letras que abreviam com o primeiro e último nome.
   * @param {string} name - Nome completo ou parcial.
   * @returns {string} - Iniciais.
   */
  static nameInitials(name) {
    name = name.trim();
    if (!name) return '';
    const names = name.split(' ');
    if (names.length === 1) return names[0].substr(0,2);
    else return names[0].substr(0,1) + names[names.length - 1].substr(0,1);
  }

  /**
   * Extrai os digitos numericos da string.
   * @param {string} string
   * @returns {string}
   */
  static extractNumbers(string) {
    return string.replace(/\D/g,'');
  }

  /**
   * Remove caracteres numericos da string.
   * @param {string} string
   * @returns {string}
   */
  static removeNumbers(string) {
    return string.replace(/\d/g,'');
  }

  /**
   * Valida um CPF brasileiro.
   * @param {string} cpf Numeros do CPF.
   * @returns {boolean}
   */
  static validCpf(cpf) {
    let strCPF = cpf;
    if (typeof strCPF == 'number') strCPF = strCPF.toString();
    strCPF = strCPF.replace(/\D+/g, '');
    if (strCPF.length !== 11) return false;
    let soma = 0;
    let resto;
    let i;
    if (strCPF === "00000000000") return false;

    for (i=1; i<=9; i++) soma = soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
    resto = (soma * 10) % 11;

    if ((resto === 10) || (resto === 11))  resto = 0;
    if (resto !== parseInt(strCPF.substring(9, 10)) ) return false;

    soma = 0;
    for (i = 1; i <= 10; i++) soma = soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
    resto = (soma * 10) % 11;

    if ((resto === 10) || (resto === 11))  resto = 0;
    return resto === parseInt(strCPF.substring(10, 11));
  }

  /**
   * Valida um CPNJ brasileiro.
   * @param {string} cnpj Numeros do CPNJ.
   * @returns {boolean}
   */
  static validCnpj(cnpj) {
    if (typeof cnpj == 'number') cnpj = cnpj.toString();
    cnpj = cnpj.replace(/\D+/g, '');
    if (cnpj.length !== 14) return false;

    // Elimina CNPJs invalidos conhecidos
    if (cnpj === "00000000000000" ||
      cnpj === "11111111111111" ||
      cnpj === "22222222222222" ||
      cnpj === "33333333333333" ||
      cnpj === "44444444444444" ||
      cnpj === "55555555555555" ||
      cnpj === "66666666666666" ||
      cnpj === "77777777777777" ||
      cnpj === "88888888888888" ||
      cnpj === "99999999999999")
      return false;

    // Valida DVs
    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0,tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;
    let i;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2) pos = 9;
    }
    let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado.toString() !== digitos.charAt(0)) return false;

    tamanho = tamanho + 1;
    numeros = cnpj.substring(0,tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
        pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    return resultado.toString() === digitos.charAt(1);
  }

  /**
   * Higieniza o texto, removendo espaços duplicados ou maiores.
   * @param {string} string
   * @returns {string}
   */
  static removeDuplicatedSpaces(string) {
    return string.trim().replace(/ {2,}/g, ' ');
  }

  /**
   * Formata um CPF, introduzindo os pontos e traços padrões.
   * @param {string} cpf
   * @returns {string}
   */
  static beautifyCpf(cpf) {
    if (cpf.length === 11) return cpf.substring(0, 3) + '.' + cpf.substring(3, 6) + '.' + cpf.substring(6, 9) + '-' + cpf.substring(9, 11);
    else if (cpf.length === 14) return cpf.substring(0, 2) + '.' + cpf.substring(2, 5) + '.' + cpf.substring(5, 8) + '/' + cpf.substring(8, 12) + '-' + cpf.substring(12, 14);
    else return '';
  }

  /**
   * Copia o texto para a area de transferencia.
   * @param {string} text
   * @return {Promise<void>}
   */
  static async copyTextToClipboard(text) {
    if (!navigator.clipboard) {
      let textArea = document.createElement('textarea');
      textArea.value = text;

      // Avoid scrolling to bottom
      textArea.style.top = "0";
      textArea.style.left = "0";
      textArea.style.position = "fixed";

      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();

      document.execCommand('copy');
      document.body.removeChild(textArea);
      return;
    }
    await navigator.clipboard.writeText(text);
  }

  /**
   * Remove acentos de uma string.
   * @param {string} text
   * @return {string}
   */
  static removeAccents(text) {
    const accentsMap = new Map([
      ["A", "Á|À|Ã|Â|Ä"],
      ["a", "á|à|ã|â|ä"],
      ["E", "É|È|Ê|Ë"],
      ["e", "é|è|ê|ë"],
      ["I", "Í|Ì|Î|Ï"],
      ["i", "í|ì|î|ï"],
      ["O", "Ó|Ò|Ô|Õ|Ö"],
      ["o", "ó|ò|ô|õ|ö"],
      ["U", "Ú|Ù|Û|Ü"],
      ["u", "ú|ù|û|ü"],
      ["C", "Ç"],
      ["c", "ç"],
      ["N", "Ñ"],
      ["n", "ñ"]
    ]);
    const reducer = (acc, [key]) => acc.replace(new RegExp(accentsMap.get(key), "g"), key);
    const remover = (text) => [...accentsMap].reduce(reducer, text);
    return remover(text);
  }

  /**
   * Converte uma data/datetime para SQL ou BR, invertendo a posicao do DIA com o ANO.
   * @param {string} data
   * @param {?string} separadorNovo
   * @return {string|false} Data invertida. Em caso de falha retorna false.
   */
  static formatDate(data, separadorNovo = null) {
    if (!data || data.length < 8) return false; //Tamanho minimo, para datas abreviadas como AA-MM-DD

    //Tenta descobrir qual eh o caractere separador da data atualmente, pegando o primeiro caractere nao-numerico
    let separadorAtual = this.removeNumbers(data);
    if (!separadorAtual) return false;
    separadorAtual = separadorAtual[0];

    const dataExplodida = data.split(separadorAtual);
    if (dataExplodida.length !== 3) return false;
    if (!separadorNovo) separadorNovo = separadorAtual;

    //Se contiver horas, devemos posiciona-la sempre no final da string
    if (dataExplodida[2].includes(' ') && dataExplodida[2].includes(':')) {
      dataExplodida[0] += dataExplodida[2].substring(dataExplodida[2].indexOf(' '));
      dataExplodida[2] = dataExplodida[2].substring(0, dataExplodida[2].indexOf(' '));
    }

    return dataExplodida[2] + separadorNovo + dataExplodida[1] + separadorNovo + dataExplodida[0];
  }
}
