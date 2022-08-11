# Web Balcão De Vendas
Sistema para cadastrar produtos e registrar a venda deles.

## Tecnologias
- FrontEnd com utilização de framework **VueJs 2** e biblioteca **Vuetify 2**.
- BackEnd em linguagem **PHP 7** com a extensão PDO, servindo API's JSON sem depender de frameworks de terceiros.
- Banco de dados do tipo relacional feito com o **MySQL**.

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