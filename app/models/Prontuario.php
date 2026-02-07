<?php
require_once __DIR__ . '/../config.php';

class Prontuario {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorConsulta($consultaId) {
        $stmt = $this->db->prepare("SELECT * FROM prontuarios WHERE consulta_id = ?");
        $stmt->execute([$consultaId]);
        return $stmt->fetch();
    }

    public function criar($consultaId, $dados) {
        // Verificar se já existe
        $existente = $this->buscarPorConsulta($consultaId);
        if ($existente) {
            return $this->atualizar($consultaId, $dados);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO prontuarios (
                consulta_id, queixa, historico, exame_fisico, 
                hipoteses_diagnosticas, diagnostico, prescricao, 
                exames_solicitados, atestado, orientacoes,
                peso_atual, temperatura, fc, fr
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $consultaId,
            $dados['queixa'] ?? null,
            $dados['historico'] ?? null,
            $dados['exame_fisico'] ?? null,
            $dados['hipoteses_diagnosticas'] ?? null,
            $dados['diagnostico'] ?? null,
            $dados['prescricao'] ?? null,
            $dados['exames_solicitados'] ?? null,
            $dados['atestado'] ?? null,
            $dados['orientacoes'] ?? null,
            $dados['peso_atual'] ?? null,
            $dados['temperatura'] ?? null,
            $dados['fc'] ?? null,
            $dados['fr'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function atualizar($consultaId, $dados) {
        $campos = [];
        $valores = [];
        
        $perm = [
            'queixa', 'historico', 'exame_fisico', 'hipoteses_diagnosticas',
            'diagnostico', 'prescricao', 'exames_solicitados', 'atestado',
            'orientacoes', 'peso_atual', 'temperatura', 'fc', 'fr'
        ];
        
        foreach ($perm as $p) {
            if (isset($dados[$p])) {
                $campos[] = "$p = ?";
                $valores[] = $dados[$p];
            }
        }
        
        if (empty($campos)) return false;
        
        $valores[] = $consultaId;
        $sql = "UPDATE prontuarios SET " . implode(', ', $campos) . " WHERE consulta_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($valores);
    }

    public function gerarAtestado($consultaId, $texto) {
        return $this->atualizar($consultaId, ['atestado' => $texto]);
    }

    public function gerarReceita($consultaId, $texto) {
        return $this->atualizar($consultaId, ['prescricao' => $texto]);
    }
}
