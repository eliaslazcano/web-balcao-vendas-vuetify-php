# balcao-venda-vue-php

Sistema de Venda em balcão para conveniências e bares

## Tecnologias
FrontEnd com utilização de Vue 2 e Vuetify.
Backend com utilização de PHP 7 para servir API's JSON.
Banco de dados em MySQL.

## Instalação de dependências do JavaScript
```
npm install
```

## Instalação de dependências do PHP

No diretorio ``public\api``:

```
composer install
composer dump-autoload
```

### Servidor de desenvolvimento front-end
```
npm run serve
```

### Servidor de desenvolvimento back-end
```
php -S localhost:3000 -t public/api
```

### Compilar o front-end para produção
```
npm run build
```