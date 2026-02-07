<?php
require_once __DIR__ . '/../config.php';

class Pet {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listar($filtros = []) {
        $sql = "SELECT p.*, d.nome AS dono_nome, d.telefone AS dono_telefone 
                FROM pets p 
                JOIN donos d ON d.id = p.dono_id 
                WHERE p.ativo = TRUE";
        $params = [];
        
        if (!empty($filtros['dono_id'])) {
            $sql .= " AND p.dono_id = ?";
            $params[] = $filtros['dono_id'];
        }
        if (!empty($filtros['especie'])) {
            $sql .= " AND p.especie = ?";
            $params[] = $filtros['especie'];
        }
        if (!empty($filtros['busca'])) {
            $sql .= " AND (p.nome LIKE ? OR d.nome LIKE ?)";
            $params[] = '%' . $filtros['busca'] . '%';
            $params[] = '%' . $filtros['busca'] . '%';
        }
        
        $sql .= " ORDER BY p.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscar($id) {
        $stmt = $this->db->prepare("SELECT * FROM pets WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function criar($dados) {
        $stmt = $this->db->prepare("
            INSERT INTO pets (dono_id, nome, especie, raca, sexo, data_nascimento, peso, cor, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dados['dono_id'],
            $dados['nome'],
            $dados['especie'],
            $dados['raca'] ?? null,
            $dados['sexo'],
            $dados['data_nascimento'] ?? null,
            $dados['peso'] ?? null,
            $dados['cor'] ?? null,
            $dados['observacoes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function atualizar($id, $dados) {
        $stmt = $this->db->prepare("
            UPDATE pets SET 
                nome = ?, especie = ?, raca = ?, sexo = ?, 
                data_nascimento = ?, peso = ?, cor = ?, observacoes = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['especie'],
            $dados['raca'] ?? null,
            $dados['sexo'],
            $dados['data_nascimento'] ?? null,
            $dados['peso'] ?? null,
            $dados['cor'] ?? null,
            $dados['observacoes'] ?? null,
            $id
        ]);
    }

    public function excluir($id) {
        $stmt = $this->db->prepare("UPDATE pets SET ativo = FALSE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getHistorico($id) {
        $pet = $this->buscar($id);
        if (!$pet) return null;
        
        // Consultas
        $stmt = $this->db->prepare("
            SELECT c.*, pr.diagnostico, pr.prescricao 
            FROM consultas c
            LEFT JOIN prontuarios pr ON pr.consulta_id = c.id
            WHERE c.pet_id = ?
            ORDER BY c.data_consulta DESC
        ");
        $stmt->execute([$id]);
        $pet['consultas'] = $stmt->fetchAll();
        
        // Vacinas
        $stmt = $this->db->prepare("SELECT * FROM Vacinas WHERE pet_id = ? ORDER BY data_aplicacao DESC");
        $stmt->execute([$id]);
        $pet['vacinas'] = $stmt->fetchAll();
        
        return $pet;
    }

    public function getVacinasVencidas($id) {
        $stmt = $this->db->prepare("
            SELECT * FROM Vacinas 
            WHERE pet_id = ? AND data_proxima IS NOT NULL AND data_proxima < CURDATE()
            ORDER BY data_proxima
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
}
