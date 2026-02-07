-- Sistema Andressa Pet - Clínica Veterinária
-- Schema do Banco de Dados

CREATE DATABASE IF NOT EXISTS andressapet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE andressapet;

-- Donos dos pets
CREATE TABLE donos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(200),
    endereco TEXT,
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Pets
CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dono_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL, -- Cachorro, Gato, etc.
    raca VARCHAR(100),
    sexo ENUM('Macho', 'Fêmea') NOT NULL,
    data_nascimento DATE,
    peso DECIMAL(5,2),
    cor VARCHAR(100),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dono_id) REFERENCES donos(id) ON DELETE CASCADE
);

-- Consultas
CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    data_consulta DATETIME NOT NULL,
    tipo ENUM('retorno', 'vacina', 'exame', 'emergencia', 'consulta_geral', 'cirurgia') NOT NULL,
    status ENUM('agendada', 'confirmada', 'em_andamento', 'finalizada', 'cancelada') DEFAULT 'agendada',
    valor DECIMAL(10,2),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- Prontuário (evolução/atestado por consulta)
CREATE TABLE prontuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL UNIQUE,
    queixa TEXT,
    historico TEXT,
    exame_fisico TEXT,
    hipoteses_diagnosticas TEXT,
    diagnostico TEXT,
    prescricao TEXT,
    exames_solicitados TEXT,
    atestado TEXT,
    orientacoes TEXT,
    peso_atual DECIMAL(5,2),
    temperatura DECIMAL(4,1),
    fc INT, -- frequência cardíaca
    fr INT, -- frequência respiratória
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE CASCADE
);

-- Vacinas
CREATE TABLE Vacinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    consulta_id INT, -- pode ser NULL se não houve consulta
    nome VARCHAR(100) NOT NULL,
    data_aplicacao DATE NOT NULL,
    data_proxima DATE,
    lote VARCHAR(50),
    laboratorio VARCHAR(100),
    veterinario VARCHAR(200),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE SET NULL
);

-- Exames
CREATE TABLE exames (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    consulta_id INT,
    nome VARCHAR(200) NOT NULL,
    tipo ENUM('sangue', 'urina', 'fezes', 'imagem', 'citologia', 'histopatologico', 'outro') NOT NULL,
    data_solicitacao DATE NOT NULL,
    data_resultado DATE,
    resultado TEXT,
    arquivo_url VARCHAR(500),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE SET NULL
);

-- Procedimentos/Cirurgias
CREATE TABLE procedimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    consulta_id INT,
    nome VARCHAR(200) NOT NULL,
    tipo ENUM('cirurgia', 'procedimento', 'internacao') NOT NULL,
    data_procedimento DATETIME NOT NULL,
    duracao_minutos INT,
    valor DECIMAL(10,2),
    status ENUM('agendado', 'em_andamento', 'finalizado', 'cancelado') DEFAULT 'agendado',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE SET NULL
);

-- Índices para performance
CREATE INDEX idx_pets_dono ON pets(dono_id);
CREATE INDEX idx_consultas_data ON consultas(data_consulta);
CREATE INDEX idx_consultas_status ON consultas(status);
CREATE INDEX idx_consultas_pet ON consultas(pet_id);
CREATE INDEX idx_vacinas_pet ON Vacinas(pet_id);
CREATE INDEX idx_exames_pet ON exames(pet_id);

-- View para agenda do dia
CREATE VIEW agenda_dia AS
SELECT 
    c.id,
    c.data_consulta,
    c.tipo,
    c.status,
    c.valor,
    p.nome AS pet_nome,
    p.especie,
    d.nome AS dono_nome,
    d.telefone
FROM consultas c
JOIN pets p ON p.id = c.pet_id
JOIN donos d ON d.id = p.dono_id
WHERE DATE(c.data_consulta) = CURDATE()
ORDER BY c.data_consulta;

-- View para histórico do pet
CREATE VIEW historico_pet AS
SELECT 
    p.id AS pet_id,
    p.nome AS pet_nome,
    p.especie,
    p.raca,
    d.nome AS dono_nome,
    d.telefone AS dono_telefone,
    c.id AS consulta_id,
    c.data_consulta,
    c.tipo AS consulta_tipo,
    c.status AS consulta_status,
    pr.diagnostico,
    pr.prescricao,
    pr.atestado
FROM pets p
JOIN donos d ON d.id = p.dono_id
LEFT JOIN consultas c ON c.pet_id = p.id
LEFT JOIN prontuarios pr ON pr.consulta_id = c.id
ORDER BY c.data_consulta DESC;
