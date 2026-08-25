# Sistema de Ordem de Serviços - JM Informática

Teste técnico: PHP OO + MVC (sem framework, sem Composer) + PDO + JavaScript puro.

## Tecnologias e restrições seguidas
- PHP Orientado a Objetos, arquitetura MVC feita à mão (sem framework)
- PDO com prepared statements (proteção contra SQL Injection)
- JavaScript puro (sem jQuery)
- Nenhuma dependência via Composer (autoload manual com `spl_autoload_register`)

## Estrutura de pastas

```
avaliacao-php/
├── app/
│   ├── Config/
│   │   ├── Database.php   -> conexão PDO (Singleton)
│   │   └── Mail.php       -> credenciais SMTP (Gmail)
│   ├── Controllers/
│   │   ├── AuthController.php       -> login, cadastro de usuário, logout
│   │   ├── DashboardController.php  -> tela principal, finalizar/excluir serviço
│   │   └── ServiceController.php    -> cadastro/edição de serviço
│   ├── Models/
│   │   ├── User.php     -> acesso a dados de usuário
│   │   └── Service.php  -> acesso a dados de serviço, filtros, comissão
│   ├── Core/
│   │   ├── Router.php      -> roteamento simples (?rota=controller/acao)
│   │   ├── Controller.php  -> classe base (render, redirect, requireLogin)
│   │   └── SmtpMailer.php  -> cliente SMTP com autenticação, escrito na mão
│   └── Views/
│       ├── auth/         -> login.php, register.php
│       ├── dashboard/    -> index.php
│       ├── services/     -> create.php, edit.php
│       └── layout/       -> sidebar.php (partial reutilizável)
├── public/
│   ├── index.php   -> front controller (único ponto de entrada)
│   └── assets/
│       ├── css/style.css
│       └── js/dashboard.js
├── database/
│   └── schema.sql  -> criação das tabelas (user, service) + usuário de teste
└── README.md
```

## Como rodar (XAMPP)

1. **Banco de dados**: abra o phpMyAdmin (`http://localhost/phpmyadmin`), aba SQL,
   cole o conteúdo de `database/schema.sql` e execute. Isso cria o banco `os_jm`,
   as tabelas e um usuário de teste.

2. **Projeto**: copie a pasta inteira `avaliacao-php` para dentro da pasta
   `htdocs` da sua instalação do XAMPP.

3. **Credenciais do banco**: confira `app/Config/Database.php` — o padrão
   (`root` / senha vazia) já funciona na maioria das instalações XAMPP.

4. **Email (opcional)**: em `app/Config/Mail.php`, preencha `GMAIL_USER` e
   `GMAIL_APP_PASSWORD` com uma conta Gmail e uma
   [senha de app](https://myaccount.google.com/apppasswords) (exige verificação
   em duas etapas ativada). Sem isso preenchido, o sistema continua funcionando
   normalmente — só o email de finalização não é enviado (o erro fica registrado
   no log do PHP, sem quebrar a tela).

5. **Acessar**: `http://localhost/avaliacao-php/public/`

### Usuário de teste
- Email: `jose@teste.com`
- Senha: `123456`

## Funcionalidades implementadas
- Login com validação de email/senha
- Cadastro de novo usuário (com checagem de email duplicado)
- Dashboard: dados do usuário logado, data atual, valor total de serviços
  prestados, lista de serviços pendentes
- Tabela de serviços com filtro por nome, usuário, status e período
- Cadastro, edição e exclusão de serviço
- Finalização de serviço: grava data de finalização, calcula comissão e
  tenta enviar email ao usuário responsável
- Proteção de rotas: dashboard e cadastro de serviço exigem login
  (`Controller::requireLogin()`)
- Validações no back-end (preço numérico e positivo, campos obrigatórios),
  além das validações HTML5 no front

## Decisões técnicas e suposições documentadas

**Senha em `VARCHAR(45)`**: o modelo de banco enviado define a coluna
`password` como `VARCHAR(45)`, o que não comporta um hash `bcrypt` (60
caracteres, o padrão do `password_hash()` do PHP). Optamos por manter a
coluna fiel ao modelo e usar `MD5()` (32 caracteres) para armazenar a senha.

**Faixa de comissão não especificada (R$ 250,01 a R$ 1.000,00)**: o
enunciado define comissão de 5% até R$ 250, 10% acima de R$ 1.000 e 20%
acima de R$ 10.000, mas não define o intervalo entre R$ 250,01 e R$
1.000,00. Adotamos 5% nessa faixa (leitura mais conservadora do ponto de
vista financeiro). A regra está isolada em `Service::calcularComissao()`,
com o comentário da suposição, para fácil ajuste se necessário.

**Status do serviço é derivado, não uma coluna**: seguindo o modelo de
banco enviado, não existe coluna `status`. Um serviço é "Pendente" quando
`finished_at` é `NULL`, e "Finalizado" quando tem uma data preenchida.

**Envio de email via SMTP feito na mão**: como a função `mail()` nativa do
PHP não autentica com usuário/senha (necessário para Gmail e a maioria dos
provedores atuais), e não é permitido usar Composer para trazer uma
biblioteca pronta (como o PHPMailer), foi implementado um cliente SMTP
mínimo em `app/Core/SmtpMailer.php`, usando sockets diretamente
(`stream_socket_client`) para negociar o protocolo SMTP com autenticação
`AUTH LOGIN` sobre conexão SSL.

## Testes manuais realizados
- Login com credenciais inválidas / válidas
- Cadastro de usuário com email duplicado
- Cadastro de serviço com campos vazios e com preço negativo
- Acesso direto às rotas protegidas (`dashboard/index`, `service/create`)
  sem estar logado
- Simulação de sessão expirada (remoção manual do cookie de sessão)
- Filtros combinados (nome, usuário, status, período)
- Fluxo completo: cadastrar → finalizar (cálculo de comissão) → editar → excluir