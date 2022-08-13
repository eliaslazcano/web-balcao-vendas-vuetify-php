import http from '@/plugins/axios';
import {config} from '@/config';

export default class BiometriaNitgen {

  constructor() {
    this.webclient = http();
    this.webclient.defaults.baseURL = config.http.biometriaBaseUrl;
  }

  /**
   * Escaneia um dedo e fornece uma string que representa a digital.
   * @returns {Promise<*>}
   */
  async capturaRapida() {
    const {data} = await this.webclient.get('Capturar/1');
    return data;
  }

  /**
   * Escaneia ate dez dedos e fornece uma string que representa essas digitais.
   * @returns {Promise<*>}
   */
  async capturaCompleta() {
    const {data} = await this.webclient.get('Enroll/1');
    return data;
  }

  /**
   * Confere se a string da digital fornecida pertence a pessoa que esta sendo escaneada.
   * @param digital Digital armazenada para conferir com a escaneada.
   * @returns {Promise<boolean>} true: positivo, false: negativo.
   */
  async comparar(digital) {
    const {data} = await this.webclient.get(`Comparar?Digital=${digital}`);
    return data;
  }

  /**
   * Informe uma listagem de usuarios e suas digitais em forma de array<{int id, string digital}>,
   * o scanner ira acionar para saber quem eh a esta pessoa retornando o id dela.
   * @param {array<{id: number, digital: string}>} usuarios
   * @returns {Promise<number|null>} 0 = Nao esta no cadastro informado, null = Scan cancelado ou falhou.
   */
  async identificar(usuarios) {
    const {data} = await this.webclient.post('Identificar', usuarios);
    return data;
  }
}