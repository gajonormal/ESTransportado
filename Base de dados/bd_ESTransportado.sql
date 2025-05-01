
-- Criação da Base de Dados
CREATE DATABASE IF NOT EXISTS gestao_transportes;
USE gestao_transportes;

-- Tabela Utilizador
CREATE TABLE IF NOT EXISTS Utilizador (
    id_utilizador INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    sexo ENUM('Masculino', 'Feminino') NOT NULL,
    num_identificacao VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    palavra_passe VARCHAR(255) NOT NULL,
    tipo ENUM('Administrador', 'Gestor', 'Aluno') DEFAULT 'Aluno',
    estado_conta ENUM('Ativo', 'Banido') DEFAULT 'Ativo',
    motivo_banimento TEXT,
);
