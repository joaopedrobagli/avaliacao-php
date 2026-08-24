-- =============================================================
-- Sistema de Ordem de Serviços - JM Informática
-- Script de criação das tabelas (fiel à modelagem enviada)
-- =============================================================

CREATE DATABASE IF NOT EXISTS os_jm
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE os_jm;

-- -------------------------------------------------------------
-- Tabela: user
-- Observação: password é VARCHAR(45) por definição do modelo.
-- Isso não comporta um hash bcrypt (60 chars), então a senha
-- é armazenada com MD5 (32 chars) neste projeto de teste.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user (
    id_user     BIGINT(20)      NOT NULL AUTO_INCREMENT,
    name        VARCHAR(150)    NOT NULL,
    email       VARCHAR(100)    NOT NULL,
    password    VARCHAR(45)     NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_at   DATETIME        NULL ON UPDATE CURRENT_TIMESTAMP,
    ativo       TINYINT(1)      NOT NULL DEFAULT 1,
    PRIMARY KEY (id_user),
    UNIQUE KEY uq_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Tabela: service
-- status é derivado (finished_at IS NULL => Pendente),
-- então não existe coluna "status" física, conforme o modelo.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS service (
    id_service       BIGINT(20)     NOT NULL AUTO_INCREMENT,
    description      VARCHAR(45)    NOT NULL,
    price            DECIMAL(11,3)  NOT NULL,
    created_at       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_at        DATETIME       NULL ON UPDATE CURRENT_TIMESTAMP,
    finished_at      DATETIME       NULL,
    commission_user  DECIMAL(11,3)  NULL,
    user_id_user     BIGINT(20)     NOT NULL,
    PRIMARY KEY (id_service),
    KEY idx_service_user (user_id_user),
    CONSTRAINT fk_service_user
        FOREIGN KEY (user_id_user) REFERENCES user (id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Usuário de exemplo para testes (senha: 123456 em MD5)
-- -------------------------------------------------------------
INSERT INTO user (name, email, password, ativo) VALUES
('José Silva', 'jose@teste.com', MD5('123456'), 1);
