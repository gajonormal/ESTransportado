CREATE DATABASE IF NOT EXISTS Estransportado;
USE Estransportado;

-- Tabela de Utilizadores (completa com novos campos)
CREATE TABLE Utilizadores (
    id_utilizador INT PRIMARY KEY AUTO_INCREMENT,
    email_institucional VARCHAR(255) UNIQUE NOT NULL 
        CHECK (email_institucional LIKE '%@ipcb.pt' OR email_institucional LIKE '%@ipcbcampus.pt'),
    password_hash VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    numero_matricula VARCHAR(20) UNIQUE NOT NULL,
    sexo ENUM('masculino', 'feminino') NOT NULL,
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('admin', 'gestor', 'aluno') NOT NULL DEFAULT 'aluno',
    conta_ativa BOOLEAN DEFAULT TRUE
);

-- Tabela de Condutores (entidade mínima)
CREATE TABLE Condutores (
    id_condutor INT PRIMARY KEY AUTO_INCREMENT,
    nome_condutor VARCHAR(50) NOT NULL, -- Código ou nome simples
    total_avaliacoes INT DEFAULT 0,
    media_avaliacoes DECIMAL(3,2) DEFAULT 0.00,
    UNIQUE KEY uk_referencia (nome_condutor)
);

-- Tabela de Viagens (precisa ser criada antes de PropostasTransporte e Reservas)
CREATE TABLE Viagens (
    id_viagem INT PRIMARY KEY AUTO_INCREMENT,
    origem VARCHAR(255) NOT NULL,
    destino VARCHAR(255) NOT NULL,
    data_partida DATETIME NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    lotacao_maxima INT NOT NULL CHECK (lotacao_maxima > 0),
    lotacao_atual INT DEFAULT 0,
    estado ENUM('ativo', 'cancelado', 'completo') DEFAULT 'ativo',
    tipo ENUM('publico', 'privado') NOT NULL,
    data_chegada DATETIME NOT NULL,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de Propostas de Transporte (com preço)
CREATE TABLE PropostasTransporte (
    id_proposta INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno INT NOT NULL,
    data_partida DATETIME NOT NULL,
    origem VARCHAR(255) NOT NULL,
    destino VARCHAR(255) NOT NULL,
    lotacao_maxima INT NOT NULL CHECK (lotacao_maxima > 0),
    preco DECIMAL(10,2) NOT NULL COMMENT 'Preço por passageiro em EUR',
    tipo ENUM('publico', 'privado') NOT NULL,
    estado ENUM('ativo', 'cancelado', 'completo') DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_preco CHECK (preco >= 0),
    FOREIGN KEY (id_aluno) REFERENCES Utilizadores(id_utilizador)
);

-- Tabela de Passageiros (informações adicionais para reservas)
CREATE TABLE Passageiros (
    id_passageiro INT PRIMARY KEY AUTO_INCREMENT,
    id_utilizador INT NULL,
    primeiro_nome VARCHAR(50) NOT NULL,
    sobrenome VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_utilizador) REFERENCES Utilizadores(id_utilizador)
);

-- Tabela de Reservas (corrigida)
CREATE TABLE Reservas (
    id_reserva INT PRIMARY KEY AUTO_INCREMENT,
    id_viagem INT NOT NULL,
    id_passageiro INT NOT NULL,
    id_passageiro_associado INT NULL,
    data_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lugar VARCHAR(10), -- Ex: "A1", "B3" etc.
    preco_total DECIMAL(10,2) NOT NULL,
    estado ENUM('confirmado', 'cancelado', 'pendente') DEFAULT 'confirmado',
    FOREIGN KEY (id_viagem) REFERENCES Viagens(id_viagem),
    FOREIGN KEY (id_passageiro) REFERENCES Passageiros(id_passageiro),
    FOREIGN KEY (id_passageiro_associado) REFERENCES Passageiros(id_passageiro)
);

-- Tabela de Avaliações (inclui comentários)
CREATE TABLE Avaliacoes (
    id_avaliacao INT PRIMARY KEY AUTO_INCREMENT,
    id_avaliador INT NOT NULL,
    id_avaliado INT NOT NULL,
    id_viagem INT NOT NULL,
    classificacao INT NOT NULL CHECK (classificacao BETWEEN 1 AND 5),
    comentario TEXT,
    anonima BOOLEAN DEFAULT FALSE,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_avaliador) REFERENCES Utilizadores(id_utilizador),
    FOREIGN KEY (id_avaliado) REFERENCES Condutores(id_condutor),
    FOREIGN KEY (id_viagem) REFERENCES Viagens(id_viagem),
    CONSTRAINT uc_avaliacao UNIQUE (id_avaliador, id_viagem),
    CONSTRAINT chk_comentario_requer_avaliacao CHECK (
    (comentario IS NULL) OR (classificacao IS NOT NULL)
    )
);

-- Tabela de Denúncias
CREATE TABLE Denuncias (
    id_denuncia INT PRIMARY KEY AUTO_INCREMENT,
    id_denunciante INT NOT NULL,
    id_denunciado INT NOT NULL,
    tipo_conteudo ENUM('avaliacao', 'proposta') NOT NULL,
    motivo TEXT NOT NULL,
    estado ENUM('pendente', 'resolvida', 'rejeitada') DEFAULT 'pendente',
    data_denuncia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_denunciante) REFERENCES Utilizadores(id_utilizador),
    FOREIGN KEY (id_denunciado) REFERENCES Condutores(id_condutor)
);

-- Tabela de Banimentos
CREATE TABLE Banimentos (
    id_banimento INT PRIMARY KEY AUTO_INCREMENT,
    id_utilizador INT NOT NULL,
    id_gestor INT NOT NULL,
    motivo TEXT NOT NULL,
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fim TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_utilizador) REFERENCES Utilizadores(id_utilizador),
    FOREIGN KEY (id_gestor) REFERENCES Utilizadores(id_utilizador)
);

-- Tabela de Notificações
CREATE TABLE Notificacoes (
    id_notificacao INT PRIMARY KEY AUTO_INCREMENT,
    id_utilizador INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('reserva', 'avaliacao', 'alerta') NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizador) REFERENCES Utilizadores(id_utilizador)
);

-- Tabela de Estatísticas (para dashboard)
CREATE TABLE Estatisticas (
    id_estatistica INT PRIMARY KEY AUTO_INCREMENT,
    data_referencia DATE NOT NULL,
    total_utilizadores INT NOT NULL,
    viagens_ativas INT NOT NULL,
    viagens_concluidas INT NOT NULL,
    denuncias_pendentes INT NOT NULL,
    avaliacao_media DECIMAL(3,2),
    rotas_populares TEXT,
    horarios_pico TEXT
);

-- Tabela de Tentativas de Login (para segurança)
CREATE TABLE tentativas_login (
    id_tentativa INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    sucesso TINYINT(1) NOT NULL DEFAULT 0,
    data_tentativa TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Inserir dados de teste para Utilizadores
INSERT INTO Utilizadores (email_institucional, password_hash, nome_completo, data_nascimento, numero_matricula, sexo, tipo) VALUES
-- Administradores
('admin@ipcb.pt', 'admin123', 'Admin Principal', '1985-05-15', 'ADM001', 'masculino', 'admin'),
('admin2@ipcb.pt', 'admin123', 'Admin Secundário', '1990-10-20', 'ADM002', 'feminino', 'admin'),

-- Gestores
('gestor1@ipcb.pt', 'gestor123', 'Gestor Transportes', '1988-03-12', 'GES001', 'masculino', 'gestor'),
('gestor2@ipcb.pt', 'gestor123', 'Gestor Sistema', '1991-07-22', 'GES002', 'feminino', 'gestor'),

-- Alunos
('aluno1@ipcbcampus.pt', 'aluno123', 'João Silva', '2000-01-15', 'A12345', 'masculino', 'aluno'),
('aluno2@ipcbcampus.pt', 'aluno123', 'Maria Santos', '2001-05-20', 'A12346', 'feminino', 'aluno'),
('aluno3@ipcbcampus.pt', 'aluno123', 'Pedro Costa', '1999-11-10', 'A12347', 'masculino', 'aluno'),
('aluno4@ipcbcampus.pt', 'aluno123', 'Ana Ferreira', '2002-03-25', 'A12348', 'feminino', 'aluno'),
('aluno5@ipcbcampus.pt', 'aluno123', 'Miguel Oliveira', '2000-07-30', 'A12349', 'masculino', 'aluno'),
('aluno6@ipcbcampus.pt', 'aluno123', 'Sofia Martins', '2001-09-05', 'A12350', 'feminino', 'aluno'),
('aluno7@ipcbcampus.pt', 'aluno123', 'Tiago Rocha', '1999-12-18', 'A12351', 'masculino', 'aluno'),
('aluno8@ipcbcampus.pt', 'aluno123', 'Carolina Dias', '2002-02-10', 'A12352', 'feminino', 'aluno'),
('aluno9@ipcbcampus.pt', 'aluno123', 'Daniel Sousa', '2000-06-15', 'A12353', 'masculino', 'aluno'),
('aluno10@ipcbcampus.pt', 'aluno123', 'Mariana Lopes', '2001-04-22', 'A12354', 'feminino', 'aluno');

-- Inserir dados de teste para Condutores
INSERT INTO Condutores (nome_condutor, total_avaliacoes, media_avaliacoes) VALUES
('C001-JoaoS', 15, 4.75),
('C002-PedroC', 8, 4.25),
('C003-MiguelO', 12, 4.50),
('C004-TiagoR', 5, 3.80),
('C005-DanielS', 3, 4.00),
('C006-MariaS', 10, 4.60),
('C007-AnaF', 6, 4.30),
('C008-SofiaM', 4, 3.90),
('C009-CarolinaD', 9, 4.20),
('C010-MarianaL', 7, 4.10);

-- Inserir dados de teste para Viagens
INSERT INTO Viagens (origem, destino, data_partida, preco, lotacao_maxima, lotacao_atual, estado, tipo, data_chegada) VALUES
('IPCB Campus', 'Castelo Branco Centro', '2025-05-12 08:00:00', 2.50, 4, 3, 'ativo', 'privado', '2025-05-12 08:20:00'),
('Castelo Branco Centro', 'IPCB Campus', '2025-05-12 17:30:00', 2.50, 4, 2, 'ativo', 'privado', '2025-05-12 17:50:00'),
('IPCB Campus', 'Covilhã', '2025-05-13 09:00:00', 6.00, 3, 3, 'ativo', 'privado', '2025-05-13 09:45:00'),
('Covilhã', 'IPCB Campus', '2025-05-13 18:00:00', 6.00, 3, 2, 'ativo', 'privado', '2025-05-13 18:45:00'),
('IPCB Campus', 'Lisboa', '2025-05-15 07:00:00', 15.00, 5, 4, 'ativo', 'privado', '2025-05-15 09:30:00'),
('Lisboa', 'IPCB Campus', '2025-05-17 18:00:00', 15.00, 5, 3, 'ativo', 'privado', '2025-05-17 20:30:00'),
('IPCB Campus', 'Porto', '2025-05-16 08:00:00', 20.00, 4, 3, 'ativo', 'privado', '2025-05-16 11:00:00'),
('Porto', 'IPCB Campus', '2025-05-18 17:00:00', 20.00, 4, 4, 'ativo', 'privado', '2025-05-18 20:00:00'),
('IPCB Campus', 'Fundão', '2025-05-14 08:30:00', 4.50, 3, 2, 'ativo', 'privado', '2025-05-14 09:15:00'),
('Fundão', 'IPCB Campus', '2025-05-14 17:30:00', 4.50, 3, 3, 'ativo', 'privado', '2025-05-14 18:15:00');

-- Inserir histórico de viagens (completas)
INSERT INTO Viagens (origem, destino, data_partida, preco, lotacao_maxima, lotacao_atual, estado, tipo, data_chegada) VALUES
('IPCB Campus', 'Castelo Branco Centro', '2025-05-01 08:00:00', 2.50, 4, 4, 'completo', 'privado', '2025-05-01 08:20:00'),
('Castelo Branco Centro', 'IPCB Campus', '2025-05-01 17:30:00', 2.50, 4, 3, 'completo', 'privado', '2025-05-01 17:50:00'),
('IPCB Campus', 'Covilhã', '2025-05-02 09:00:00', 6.00, 3, 3, 'completo', 'privado', '2025-05-02 09:45:00'),
('Covilhã', 'IPCB Campus', '2025-05-02 18:00:00', 6.00, 3, 3, 'completo', 'privado', '2025-05-02 18:45:00'),
('IPCB Campus', 'Lisboa', '2025-05-03 07:00:00', 15.00, 5, 5, 'completo', 'privado', '2025-05-03 09:30:00');

-- Inserir dados de teste para PropostasTransporte
INSERT INTO PropostasTransporte (id_aluno, data_partida, origem, destino, lotacao_maxima, preco, tipo) VALUES
(5, '2025-05-20 08:00:00', 'IPCB Campus', 'Castelo Branco Centro', 4, 2.50, 'privado'),
(7, '2025-05-20 17:30:00', 'Castelo Branco Centro', 'IPCB Campus', 4, 2.50, 'privado'),
(9, '2025-05-21 09:00:00', 'IPCB Campus', 'Covilhã', 3, 6.00, 'privado'),
(5, '2025-05-21 18:00:00', 'Covilhã', 'IPCB Campus', 3, 6.00, 'privado'),
(6, '2025-05-22 07:00:00', 'IPCB Campus', 'Lisboa', 5, 15.00, 'privado'),
(8, '2025-05-24 18:00:00', 'Lisboa', 'IPCB Campus', 5, 15.00, 'privado'),
(10, '2025-05-23 08:00:00', 'IPCB Campus', 'Porto', 4, 20.00, 'privado'),
(6, '2025-05-25 17:00:00', 'Porto', 'IPCB Campus', 4, 20.00, 'privado');

-- Inserir dados de teste para Passageiros
INSERT INTO Passageiros (id_utilizador, primeiro_nome, sobrenome, email, telefone) VALUES
(5, 'João', 'Silva', 'aluno1@ipcbcampus.pt', '912345678'),
(6, 'Maria', 'Santos', 'aluno2@ipcbcampus.pt', '923456789'),
(7, 'Pedro', 'Costa', 'aluno3@ipcbcampus.pt', '934567890'),
(8, 'Ana', 'Ferreira', 'aluno4@ipcbcampus.pt', '945678901'),
(9, 'Miguel', 'Oliveira', 'aluno5@ipcbcampus.pt', '956789012'),
(10, 'Sofia', 'Martins', 'aluno6@ipcbcampus.pt', '967890123'),
(11, 'Tiago', 'Rocha', 'aluno7@ipcbcampus.pt', '978901234'),
(12, 'Carolina', 'Dias', 'aluno8@ipcbcampus.pt', '989012345'),
(13, 'Daniel', 'Sousa', 'aluno9@ipcbcampus.pt', '990123456'),
(14, 'Mariana', 'Lopes', 'aluno10@ipcbcampus.pt', '901234567');

-- Inserir alguns passageiros adicionais (não cadastrados como utilizadores)
INSERT INTO Passageiros (id_utilizador, primeiro_nome, sobrenome, email, telefone) VALUES
(NULL, 'Ricardo', 'Mendes', 'ricardomendes@gmail.com', '912345671'),
(NULL, 'Catarina', 'Alves', 'catarina.alves@gmail.com', '923456782'),
(NULL, 'André', 'Pinto', 'andrepinto@hotmail.com', '934567893'),
(NULL, 'Beatriz', 'Gomes', 'beatriz.gomes@gmail.com', '945678904'),
(NULL, 'Diogo', 'Fernandes', 'diogo.fernandes@outlook.com', '956789015');

-- Corrigir a definição da tabela Reservas para que possamos inserir dados
-- Nota: O script original tinha um erro na definição da FOREIGN KEY
-- Vamos assumir que este comando foi corrigido e podemos inserir dados

-- Inserir dados de teste para Reservas (supondo que a tabela foi corrigida)
-- Adaptando para ID corretos baseados nos dados inseridos
INSERT INTO Reservas (id_viagem, id_passageiro, id_passageiro_associado, lugar, preco_total, estado) VALUES
(1, 1, NULL, 'A1', 2.50, 'confirmado'),
(1, 2, 1, 'A2', 2.50, 'confirmado'),
(1, 3, NULL, 'B1', 2.50, 'confirmado'),
(2, 4, NULL, 'A1', 2.50, 'confirmado'),
(2, 5, NULL, 'A2', 2.50, 'confirmado'),
(3, 6, NULL, 'A1', 6.00, 'confirmado'),
(3, 7, 6, 'A2', 6.00, 'confirmado'),
(3, 8, NULL, 'B1', 6.00, 'confirmado'),
(4, 9, NULL, 'A1', 6.00, 'confirmado'),
(4, 10, NULL, 'A2', 6.00, 'confirmado');

-- Inserir mais reservas para outras viagens
INSERT INTO Reservas (id_viagem, id_passageiro, id_passageiro_associado, lugar, preco_total, estado) VALUES
(5, 1, NULL, 'A1', 15.00, 'confirmado'),
(5, 2, 1, 'A2', 15.00, 'confirmado'),
(5, 3, NULL, 'B1', 15.00, 'confirmado'),
(5, 11, NULL, 'B2', 15.00, 'confirmado'),
(6, 4, NULL, 'A1', 15.00, 'confirmado'),
(6, 5, NULL, 'A2', 15.00, 'confirmado'),
(6, 12, NULL, 'B1', 15.00, 'confirmado'),
(7, 6, NULL, 'A1', 20.00, 'confirmado'),
(7, 7, NULL, 'A2', 20.00, 'confirmado'),
(7, 8, NULL, 'B1', 20.00, 'confirmado'),
(8, 9, NULL, 'A1', 20.00, 'confirmado'),
(8, 10, NULL, 'A2', 20.00, 'confirmado'),
(8, 13, NULL, 'B1', 20.00, 'confirmado'),
(8, 14, NULL, 'B2', 20.00, 'confirmado');

-- Inserir algumas reservas pendentes e canceladas
INSERT INTO Reservas (id_viagem, id_passageiro, id_passageiro_associado, lugar, preco_total, estado) VALUES
(9, 1, NULL, 'A1', 4.50, 'pendente'),
(9, 2, NULL, 'A2', 4.50, 'pendente'),
(10, 3, NULL, 'A1', 4.50, 'confirmado'),
(10, 4, NULL, 'A2', 4.50, 'confirmado'),
(10, 5, NULL, 'B1', 4.50, 'cancelado');

-- Inserir dados de teste para Avaliacoes
-- Relacionando com os utilizadores 5-14 (alunos) e condutores 1-10
INSERT INTO Avaliacoes (id_avaliador, id_avaliado, id_viagem, classificacao, comentario, anonima) VALUES
(5, 1, 11, 5, 'Excelente condutor, muito pontual e atencioso.', FALSE),
(6, 1, 11, 4, 'Viagem tranquila e segura.', FALSE),
(7, 1, 11, 5, 'Muito boa experiência, recomendo!', FALSE),
(8, 1, 11, 5, 'Condutor cuidadoso e simpático.', FALSE),
(9, 2, 13, 4, 'Viagem correu bem, condutor pontual.', FALSE),
(10, 2, 13, 5, 'Ótima experiência, voltarei a viajar.', TRUE),
(11, 2, 13, 4, 'Condutor atencioso e cuidadoso.', FALSE),
(12, 3, 15, 5, 'Excelente viagem, muito confortável.', FALSE),
(13, 3, 15, 4, 'Pontual e seguro, recomendo!', TRUE),
(14, 3, 15, 5, 'Melhor experiência de partilha até agora!', FALSE);

-- Inserir mais avaliações para outros condutores
INSERT INTO Avaliacoes (id_avaliador, id_avaliado, id_viagem, classificacao, comentario, anonima) VALUES
(5, 4, 12, 4, 'Viagem tranquila, recomendo.', FALSE),
(6, 4, 12, 3, 'Boa viagem, mas um pouco atrasado.', TRUE),
(7, 5, 14, 4, 'Condutor simpático e prestativo.', FALSE),
(8, 5, 14, 4, 'Viagem confortável e segura.', FALSE),
(9, 6, 14, 5, 'Excelente experiência!', TRUE);

-- Inserir dados de teste para Denuncias
INSERT INTO Denuncias (id_denunciante, id_denunciado, tipo_conteudo, motivo, estado) VALUES
(5, 3, 'avaliacao', 'Comentário ofensivo na avaliação.', 'pendente'),
(7, 5, 'proposta', 'Proposta com informações incorretas.', 'pendente'),
(9, 2, 'avaliacao', 'Avaliação injusta e não condizente com o serviço.', 'resolvida'),
(11, 4, 'proposta', 'Preço abusivo para a rota indicada.', 'rejeitada'),
(13, 7, 'avaliacao', 'Linguagem inapropriada no comentário.', 'pendente');

-- Inserir dados de teste para Banimentos
INSERT INTO Banimentos (id_utilizador, id_gestor, motivo, data_inicio, data_fim, ativo) VALUES
(9, 3, 'Comportamento abusivo com outros utilizadores.', '2025-05-01 10:00:00', '2025-05-15 10:00:00', TRUE),
(11, 4, 'Múltiplas denúncias confirmadas.', '2025-04-28 15:30:00', '2025-05-28 15:30:00', TRUE),
(13, 3, 'Criação de propostas falsas.', '2025-05-05 09:15:00', '2025-05-12 09:15:00', FALSE);

-- Inserir dados de teste para Notificacoes
INSERT INTO Notificacoes (id_utilizador, titulo, mensagem, tipo) VALUES
(5, 'Nova Reserva Confirmada', 'Sua reserva para Castelo Branco Centro foi confirmada.', 'reserva'),
(6, 'Nova Avaliação', 'Você recebeu uma nova avaliação com 5 estrelas!', 'avaliacao'),
(7, 'Alerta de Proximidade', 'Seu transporte está chegando em 10 minutos.', 'alerta'),
(8, 'Avaliação Pendente', 'Não esqueça de avaliar sua última viagem.', 'avaliacao'),
(9, 'Status de Banimento', 'Seu banimento termina em 4 dias.', 'alerta'),
(10, 'Nova Proposta Disponível', 'Nova proposta de transporte para seu destino frequente.', 'alerta'),
(11, 'Status de Banimento', 'Seu banimento termina em 17 dias.', 'alerta'),
(12, 'Nova Reserva Confirmada', 'Sua reserva para Lisboa foi confirmada.', 'reserva'),
(13, 'Fim de Banimento', 'Seu período de banimento terminou.', 'alerta'),
(14, 'Nova Avaliação', 'Você recebeu uma nova avaliação com 4 estrelas!', 'avaliacao');

-- Inserir dados de teste para Estatisticas
INSERT INTO Estatisticas (data_referencia, total_utilizadores, viagens_ativas, viagens_concluidas, denuncias_pendentes, avaliacao_media, rotas_populares, horarios_pico) VALUES
('2025-05-01', 12, 8, 2, 3, 4.40, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa', '8:00, 17:30'),
('2025-05-02', 13, 8, 4, 2, 4.45, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa', '8:00, 17:30'),
('2025-05-03', 13, 9, 5, 3, 4.42, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa', '8:00, 17:30'),
('2025-05-04', 14, 10, 5, 4, 4.43, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa, IPCB Campus - Porto', '8:00, 17:30'),
('2025-05-05', 14, 10, 5, 5, 4.41, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa, IPCB Campus - Porto', '8:00, 17:30'),
('2025-05-06', 14, 10, 5, 4, 4.44, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa, IPCB Campus - Porto', '8:00, 17:30'),
('2025-05-07', 14, 10, 5, 3, 4.46, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa, IPCB Campus - Porto', '8:00, 17:30'),
('2025-05-08', 14, 10, 5, 3, 4.47, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa, IPCB Campus - Porto', '8:00, 17:30'),
('2025-05-09', 14, 10, 5, 3, 4.48, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa, IPCB Campus - Porto', '8:00, 17:30'),
('2025-05-10', 14, 10, 5, 3, 4.50, 'IPCB Campus - Castelo Branco Centro, IPCB Campus - Lisboa, IPCB Campus - Porto', '8:00, 17:30');
