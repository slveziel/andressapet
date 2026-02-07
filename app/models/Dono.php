<?php
require_once __DIR__ . '/../config.php';

class Dono {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listar() {
        $stmt = $this->db->query("SELECT * FROM donos WHERE ativo = TRUE ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function buscar($id) {
        $stmt = $this->db->prepare("SELECT * FROM donos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function criar($dados) {
        $stmt = $this->db->prepare("
            INSERT INTO donos (nome, telefone, email, endereco, observacoes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $dados['nome'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null,
            $dados['endereco'] ?? null,
            $dados['observacoes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function atualizar($id, $dados) {
        $stmt = $this->db->prepare("
            UPDATE donos SET nome = ?, telefone = ?, email = ?, endereco = ?, observacoes = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null,
            $dados['endereco'] ?? null,
            $dados['observacoes'] ?? null,
            $id
        ]);
    }

    public function excluir($id) {
        $stmt = $this->db->prepare("UPDATE donos SET ativo = FALSE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarComPets($id) {
        $dono = $this->buscar($id);
        if (!$dono) return null;
        
        $stmt = $this->db->prepare("SELECT * FROM pets WHERE dono_id = ? AND ativo = TRUE");
        $stmt->execute([$id]);
        $dono['pets'] = $stmt->fetchAll();
        
        return $dono;
    }
}
