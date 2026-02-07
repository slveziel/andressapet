<?php
require_once __DIR__ . '/../config.php';

class Consulta {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listar($filtros = []) {
        $sql = "
            SELECT c.*, p.nome AS pet_nome, p.especie, p.raca,
                   d.nome AS dono_nome, d.telefone AS dono_telefone
            FROM consultas c
            JOIN pets p ON p.id = c.pet_id
            JOIN donos d ON d.id = p.dono_id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filtros['pet_id'])) {
            $sql .= " AND c.pet_id = ?";
            $params[] = $filtros['pet_id'];
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['data'])) {
            $sql .= " AND DATE(c.data_consulta) = ?";
            $params[] = $filtros['data'];
        }
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND c.data_consulta >= ?";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND c.data_consulta <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        $sql .= " ORDER BY c.data_consulta DESC";
        
        if (!empty($filtros['limit'])) {
            $sql .= " LIMIT " . (int)$filtros['limit'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscar($id) {
        $stmt = $this->db->prepare("SELECT * FROM consultas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarCompleto($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.nome AS pet_nome, p.especie, p.raca, p.peso AS pet_peso,
                   d.nome AS dono_nome, d.telefone AS dono_telefone
            FROM consultas c
            JOIN pets p ON p.id = c.pet_id
            JOIN donos d ON d.id = p.dono_id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $consulta = $stmt->fetch();
        
        if ($consulta) {
            $pront = $this->db->prepare("SELECT * FROM prontuarios WHERE consulta_id = ?");
            $pront->execute([$id]);
            $consulta['prontuario'] = $pront->fetch();
        }
        
        return $consulta;
    }

    public function criar($dados) {
        $stmt = $this->db->prepare("
            INSERT INTO consultas (pet_id, data_consulta, tipo, status, valor, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dados['pet_id'],
            $dados['data_consulta'],
            $dados['tipo'],
            $dados['status'] ?? 'agendada',
            $dados['valor'] ?? null,
            $dados['observacoes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function atualizar($id, $dados) {
        $campos = [];
        $valores = [];
        
        $perm = ['pet_id', 'data_consulta', 'tipo', 'status', 'valor', 'observacoes'];
        foreach ($perm as $p) {
            if (isset($dados[$p])) {
                $campos[] = "$p = ?";
                $valores[] = $dados[$p];
            }
        }
        
        if (empty($campos)) return false;
        
        $valores[] = $id;
        $sql = "UPDATE consultas SET " . implode(', ', $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($valores);
    }

    public function cancelar($id) {
        return $this->atualizar($id, ['status' => 'cancelada']);
    }

    public function finalizar($id) {
        return $this->atualizar($id, ['status' => 'finalizada']);
    }

    public function getAgendaDia($data = null) {
        $data = $data ?: date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT c.*, p.nome AS pet_nome, p.especie, p.raca,
                   d.nome AS dono_nome, d.telefone AS dono_telefone
            FROM consultas c
            JOIN pets p ON p.id = c.pet_id
            JOIN donos d ON d.id = p.dono_id
            WHERE DATE(c.data_consulta) = ? AND c.status NOT IN ('cancelada')
            ORDER BY c.data_consulta
        ");
        $stmt->execute([$data]);
        return $stmt->fetchAll();
    }

    public function getProximas($dias = 7) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.nome AS pet_nome, p.especie,
                   d.nome AS dono_nome, d.telefone AS dono_telefone
            FROM consultas c
            JOIN pets p ON p.id = c.pet_id
            JOIN donos d ON d.id = p.dono_id
            WHERE c.status = 'agendada' AND c.data_consulta BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
            ORDER BY c.data_consulta
        ");
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    }
}
