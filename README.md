# 🗳️ Urna Eletrônica — Sistema de Votação

Sistema de urna eletrônica desenvolvido como projeto acadêmico para a disciplina de **Auditoria e Segurança da Informação**, com o objetivo de simular um processo eleitoral e aplicar conceitos de autenticação, autorização, integridade de dados e auditoria.

## 📌 Sobre o projeto

O projeto consiste em uma aplicação web que simula o funcionamento de uma urna eletrônica, permitindo a autenticação de eleitores e mesários, a seleção de candidatos e, futuramente, o registro seguro dos votos.

O sistema utiliza **Symfony** no backend, **MySQL** para persistência dos dados e **HTML, CSS e JavaScript** na interface.

## 🛠️ Tecnologias utilizadas

- PHP e Symfony
- MySQL
- Doctrine ORM
- Twig
- HTML5, CSS3 e JavaScript
- Composer
- Git e GitHub

## ⚙️ Funcionalidades

### Implementadas
- Interface visual de urna eletrônica.
- Autenticação por título de eleitor e senha.
- Senhas armazenadas com hash.
- Controle de acesso por permissões (`ROLE_ELEITOR` e `ROLE_ADMIN`).
- Rotas protegidas.
- Login e logout.
- Painel inicial do mesário.

### Em desenvolvimento
- Cadastro e gerenciamento de candidatos.
- Integração dos candidatos com a urna.
- Autenticação adicional por OTP simulado.
- Registro de votos com prevenção de duplicidade.
- Auditoria de operações com encadeamento de hashes.
- Assinatura digital e verificação de integridade.
- Apuração dos resultados.

## 📁 Estrutura do projeto

```text
urna-eletronica/
├── config/
├── migrations/
├── public/
│   ├── script.js
│   └── styles.css
├── src/
│   ├── Command/
│   ├── Controller/
│   │   ├── Admin/
│   │   │   └── DashboardController.php
│   │   ├── SecurityController.php
│   │   └── UrnaController.php
│   ├── Entity/
│   ├── Repository/
│   └── Security/
├── templates/
│   ├── admin/
│   │   └── dashboard.html.twig
│   ├── security/
│   │   └── login.html.twig
│   └── index.html.twig
├── .env
├── composer.json
└── symfony.lock
```

## 🚀 Como executar

### Pré-requisitos

- PHP compatível com a versão do Symfony utilizada
- Composer
- MySQL
- Symfony CLI (recomendado)

### 1. Clonar o repositório

```bash
git clone https://github.com/SEU-USUARIO/urna-eletronica.git
cd urna-eletronica
```

### 2. Instalar as dependências

```bash
composer install
```

### 3. Configurar o banco de dados

Crie um banco de dados MySQL chamado `urna_eletronica`.

Configure a variável `DATABASE_URL` no arquivo `.env.local`, utilizando suas credenciais locais:

```dotenv
DATABASE_URL="mysql://USUARIO:SENHA@127.0.0.1:3306/urna_eletronica"
```

### 4. Executar as migrações

```bash
php bin/console doctrine:migrations:migrate
```

### 5. Criar usuários de teste

Quando o comando de cadastro estiver disponível:

```bash
php bin/console app:criar-usuario
```

Cadastre um usuário com perfil **Eleitor** e outro com perfil **Mesário**.

### 6. Iniciar o servidor

```bash
symfony server:start
```

A aplicação estará disponível em:

`http://127.0.0.1:8000`

## 🔐 Perfis de acesso

| Perfil | Permissões |
|---|---|
| Eleitor | Acessar a urna eletrônica |
| Mesário | Acessar a urna e o painel administrativo |

O painel administrativo está disponível na rota `/admin`, exclusivamente para usuários autorizados.

## 🛡️ Segurança

O projeto contempla mecanismos de segurança como autenticação com hash de senha, controle de acesso baseado em papéis, proteção CSRF e limitação de tentativas de login.

Também estão previstas funcionalidades de integridade e auditoria para o registro de votos.

> **Aviso:** este sistema é um protótipo acadêmico e não deve ser utilizado em eleições oficiais ou processos eleitorais reais.

## 📚 Objetivo acadêmico

Demonstrar a aplicação prática de conceitos de segurança da informação em sistemas eleitorais, com foco em autenticação, autorização, confidencialidade, integridade e rastreabilidade.

## 📄 Licença

Projeto desenvolvido para fins acadêmicos e educacionais.
