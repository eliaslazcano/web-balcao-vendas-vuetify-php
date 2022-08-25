export const config = {
  http: {
    requestBaseUrl: 'https://barjk.eliaslazcano.dev.br/api/', //Ultimo caractere tem que ser a barra;
    requestTimeout: 60000,
    biometriaBaseUrl: 'http://localhost:9000/api/public/v1/captura/' //URL para se comunicar com o backend do leitor da Nitgen;
  },
  nome_empresa: 'Bar JK', //string. Nome exibido na barra de ferramentas;
  login: true, //boolean. Habilita o sistema de login para acessar o app;
  biometria: false, //boolean. Habilita a interface para interagir com os leitores da Nitgen;
  rfid: false, //boolean. Habilita a interface para interagir com leitores de RFID (aproximacao);
};
