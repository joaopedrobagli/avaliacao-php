<?php

namespace App\Config;

/**
 * Configuração do envio de email (SMTP via Gmail).
 *
 * COMO CONFIGURAR:
 * 1) Ative a verificação em duas etapas na sua conta Google
 * 2) Gere uma "senha de app" em myaccount.google.com/apppasswords
 * 3) Preencha GMAIL_USER e GMAIL_APP_PASSWORD abaixo
 *
 * IMPORTANTE: isso é um projeto de teste local. Em produção de verdade,
 * essas credenciais NUNCA deveriam ficar direto no código-fonte (e sim
 * em variáveis de ambiente) - aqui foi simplificado porque não foi 
 * usando Composer/bibliotecas para gerenciar .env.
 */
class Mail
{
    public const SMTP_HOST = 'smtp.gmail.com';
    public const SMTP_PORT = 465; // 465 = SSL direto (o que o SmtpMailer usa)

    public const GMAIL_USER = 'joaobagli.pedro1506@gmail.com';
    public const GMAIL_APP_PASSWORD = 'gcfxcetexidxwzly';
}