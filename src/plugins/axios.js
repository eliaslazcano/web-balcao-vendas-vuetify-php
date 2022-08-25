import axios from 'axios';
import store from '@/store';
import {config} from '@/config';

/**
 * Cria uma instância Axios com regras da aplicação.
 * @param {function(string, int?)} errorCallback - Callback com mensagem de erro para feedback.
 * @param {?string} mensagemErroConexao - Mensagem de erro quando não houver conexão entre o cliente e o servidor.
 * @returns AxiosInstance
 */
export const createWebClient = (errorCallback, mensagemErroConexao = null) => {
  if (!mensagemErroConexao) mensagemErroConexao = `Não foi possível te conectar com o servidor.\nPossíveis causas:\n- Você pode estar sem rede;\n- O serviço está em manutenção;\n- O serviço está fora do ar;`;

  //Axios Instance
  const axiosClient = axios.create({
    baseURL: config.http.requestBaseUrl,
    timeout: config.http.requestTimeout,
    headers: {'Cache-Control': 'no-cache'},
    withCredentials: false,
  });
  //Interceptor Request
  const beforeSend = conf => {
    //Insert Authorization Header
    if (store.state.token) conf.headers.Authorization = store.state.token;
    return conf;
  };
  //Error Handle
  const onError = error => {
    if (error.code === 'ECONNABORTED') errorCallback('A conexão foi cancelada pelo dispositivo');
    else if (error.code === 'ERR_NETWORK') errorCallback(mensagemErroConexao);
    else if (error.response) {
      if (error.response.status && error.response.status === 410) store.dispatch('logout');
      if (error.response.data) {
        if (typeof error.response.data === 'object' && error.response.data instanceof Blob && error.response.data.type === 'application/json') {
          const fileReader = new FileReader();
          fileReader.onload = () => {
            const data = JSON.parse(fileReader.result);
            errorCallback(data.mensagem, error.response.status);
          };
          fileReader.readAsText(error.response.data);
        }
        else if (error.response.data.mensagem) errorCallback(error.response.data.mensagem, error.response.status);
        else if (error.message) errorCallback(error.message, error.response.status);
        else errorCallback('Ocorreu um erro ao tentar processar uma requisição', error.response.status);
      }
    }
    else if (error.message) errorCallback(error.message);
    else errorCallback('Ocorreu uma falha ao tentar realizar uma requisição');
    return Promise.reject(error);
  };
  //Apply settings
  axiosClient.interceptors.request.use(beforeSend, error => Promise.reject(error));
  axiosClient.interceptors.response.use(res => res, onError);
  return axiosClient;
};

export const http = (silent = false) => createWebClient(mensagem => {
  if (silent) return null;
  else window.alert(mensagem);
});

export default http;
