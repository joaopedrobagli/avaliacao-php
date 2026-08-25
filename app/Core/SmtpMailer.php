<?php

namespace App\Core;

use Exception;

/**
 * Cliente SMTP mínimo, escrito na mão com sockets (fsockopen/stream_socket_client).
 *
 * Por quê isso existe: a função mail() nativa do PHP NÃO consegue se
 * autenticar com usuário/senha (é o que servidores como Gmail exigem).
 * Como não podemos usar Composer (logo, nada de PHPMailer via lib),
 * implementamos aqui só o suficiente do protocolo SMTP pra:
 *   1) abrir conexão criptografada (SSL) com o servidor
 *   2) autenticar (comando "AUTH LOGIN")
 *   3) enviar os comandos MAIL FROM / RCPT TO / DATA
 *
 * Isso é o "handshake" real que qualquer client de email faz por trás
 * dos panos - só que sem a camada de conveniência de uma lib pronta.
 */
class SmtpMailer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;

    public function __construct(string $host, int $port, string $username, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Envia um email de texto simples.
     * Lança Exception se qualquer etapa do protocolo falhar - assim o
     * Controller decide o que fazer (nós vamos só logar, sem travar
     * o fluxo de finalizar o serviço).
     */
    public function send(string $to, string $subject, string $body, string $fromName = 'JM Informática'): void
    {
        // "ssl://" já abre a conexão criptografada direto (porta 465),
        // então não precisamos negociar STARTTLS manualmente.
        $socket = stream_socket_client(
            "ssl://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            10
        );

        if (!$socket) {
            throw new Exception("Falha ao conectar no SMTP: $errstr ($errno)");
        }

        try {
            $this->esperarResposta($socket, 220); // saudação do servidor

            $this->comando($socket, "EHLO localhost\r\n", 250);
            $this->comando($socket, "AUTH LOGIN\r\n", 334);
            $this->comando($socket, base64_encode($this->username) . "\r\n", 334);
            $this->comando($socket, base64_encode($this->password) . "\r\n", 235);

            $this->comando($socket, "MAIL FROM:<{$this->username}>\r\n", 250);
            $this->comando($socket, "RCPT TO:<{$to}>\r\n", 250);
            $this->comando($socket, "DATA\r\n", 354);

            // Cabeçalhos + corpo do email, terminando com uma linha só
            // com "." (é assim que o protocolo SMTP sabe que acabou).
            $mensagem = "Subject: {$subject}\r\n"
                . "From: {$fromName} <{$this->username}>\r\n"
                . "To: <{$to}>\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "\r\n"
                . $body . "\r\n"
                . ".\r\n";

            $this->comando($socket, $mensagem, 250);
            $this->comando($socket, "QUIT\r\n", 221);
        } finally {
            fclose($socket);
        }
    }

    /**
     * Envia um comando e confere se o servidor respondeu com o
     * código esperado (ex: 250 = "ok, comando aceito").
     */
    private function comando($socket, string $comando, int $codigoEsperado): void
    {
        fwrite($socket, $comando);
        $this->esperarResposta($socket, $codigoEsperado);
    }

    /**
     * Lê a resposta do servidor SMTP. Uma resposta pode vir em várias
     * linhas (ex: "250-linha1", "250-linha2", "250 última linha") -
     * o hífen depois do código indica "tem mais linha vindo".
     */
    private function esperarResposta($socket, int $codigoEsperado): string
    {
        $resposta = '';

        do {
            $linha = fgets($socket, 515);
            if ($linha === false) {
                throw new Exception('Conexão SMTP encerrada inesperadamente.');
            }
            $resposta .= $linha;
        } while (isset($linha[3]) && $linha[3] === '-');

        $codigoRecebido = (int) substr($resposta, 0, 3);

        if ($codigoRecebido !== $codigoEsperado) {
            throw new Exception("SMTP respondeu {$codigoRecebido}, esperava {$codigoEsperado}: {$resposta}");
        }

        return $resposta;
    }
}