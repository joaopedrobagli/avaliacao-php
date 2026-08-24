# Sistema de Ordem de Serviços - JM Informática

Teste técnico: PHP OO + MVC (sem framework, sem Composer) + PDO + JS puro/jQuery.

## Estrutura de pastas

```
avaliacao-php/
├── app/
│   ├── Config/          -> conexão com o banco (PDO)
│   ├── Controllers/     -> AuthController, DashboardController, ServiceController
│   ├── Models/          -> User, Service (falam com o banco)
│   ├── Views/           -> HTML/PHP de cada tela (auth, dashboard, services)
│   └── Core/            -> Router e classes base
├── public/
│   ├── index.php        -> front controller (único ponto de entrada)
│   └── assets/          -> css/js
├── database/
│   └── schema.sql        -> script de criação das tabelas (user, service)
└── README.md
```

## Telas previstas (conforme wireframe)
- Login
- Cadastro de usuário
- Dashboard (últimos serviços, pendentes, filtros, tabela)
- Cadastro de novo serviço

## Como rodar
1. Criar o banco: importar `database/schema.sql`
2. Configurar credenciais em `app/Config/Database.php`
3. Rodar `php -S localhost:8000 -t public` a partir da raiz do projeto
4. Acessar `http://localhost:8000`

## Observação técnica
O campo `password` da tabela `user` é `VARCHAR(45)` (fiel ao modelo enviado),
por isso a senha é armazenada com `MD5` ao invés de `password_hash()` (bcrypt),
já que o hash bcrypt (60 caracteres) não caberia na coluna.
