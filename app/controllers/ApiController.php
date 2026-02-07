<?php
require_once __DIR__ . '/../models/Dono.php';
require_once __DIR__ . '/../models/Pet.php';
require_once __DIR__ . '/../models/Consulta.php';
require_once __DIR__ . '/../models/Prontuario.php';

class ApiController {
    
    public function handle($method, $path, $data) {
        $parts = explode('/', trim($path, '/'));
        $resource = $parts[0] ?? '';
        $id = $parts[1] ?? null;
        $action = $parts[2] ?? null;

        try {
            switch ($resource) {
                case 'donos':
                    return $this->handleDonos($method, $id, $data);
                case 'pets':
                    return $this->handlePets($method, $id, $action, $data);
                case 'consultas':
                    return $this->handleConsultas($method, $id, $action, $data);
                case 'prontuarios':
                    return $this->handleProntuarios($method, $id, $data);
                case 'agenda':
                    return $this->handleAgenda($method, $data);
                case 'dashboard':
                    return $this->handleDashboard($method);
                default:
                    return ['error' => 'Recurso não encontrado', 'code' => 404];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'code' => 400];
        }
    }

    private function handleDonos($method, $id, $data) {
        $model = new Dono();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $model->buscar($id);
                    if (!$result) return ['error' => 'Não encontrado', 'code' => 404];
                    return ['data' => $result];
                }
                return ['data' => $model->listar()];
                
            case 'POST':
                $id = $model->criar($data);
                return ['data' => ['id' => $id], 'code' => 201];
                
            case 'PUT':
                $model->atualizar($id, $data);
                return ['data' => ['success' => true]];
                
            case 'DELETE':
                $model->excluir($id);
                return ['data' => ['success' => true]];
        }
        
        return ['error' => 'Método não permitido', 'code' => 405];
    }

    private function handlePets($method, $id, $action, $data) {
        $model = new Pet();
        
        if ($action === 'historico' && $id) {
            return ['data' => $model->getHistorico($id)];
        }
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $model->buscar($id);
                    if (!$result) return ['error' => 'Não encontrado', 'code' => 404];
                    return ['data' => $result];
                }
                return ['data' => $model->listar($data)];
                
            case 'POST':
                $id = $model->criar($data);
                return ['data' => ['id' => $id], 'code' => 201];
                
            case 'PUT':
                $model->atualizar($id, $data);
                return ['data' => ['success' => true]];
                
            case 'DELETE':
                $model->excluir($id);
                return ['data' => ['success' => true]];
        }
        
        return ['error' => 'Método não permitido', 'code' => 405];
    }

    private function handleConsultas($method, $id, $action, $data) {
        $model = new Consulta();
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $model->buscarCompleto($id);
                    if (!$result) return ['error' => 'Não encontrado', 'code' => 404];
                    return ['data' => $result];
                }
                return ['data' => $model->listar($data)];
                
            case 'POST':
                $id = $model->criar($data);
                return ['data' => ['id' => $id], 'code' => 201];
                
            case 'PUT':
                $model->atualizar($id, $data);
                return ['data' => ['success' => true]];
                
            case 'DELETE':
                $model->cancelar($id);
                return ['data' => ['success' => true]];
        }
        
        return ['error' => 'Método não permitido', 'code' => 405];
    }

    private function handleProntuarios($method, $consultaId, $data) {
        $model = new Prontuario();
        
        if ($method === 'GET') {
            if ($consultaId) {
                $result = $model->buscarPorConsulta($consultaId);
                return ['data' => $result ?: null];
            }
        }
        
        if ($method === 'POST' || $method === 'PUT') {
            $model->criar($consultaId, $data);
            return ['data' => ['success' => true]];
        }
        
        return ['error' => 'Método não permitido', 'code' => 405];
    }

    private function handleAgenda($method, $data) {
        $model = new Consulta();
        
        if ($method === 'GET') {
            $dataStr = $data['data'] ?? date('Y-m-d');
            return ['data' => $model->getAgendaDia($dataStr)];
        }
        
        return ['error' => 'Método não permitido', 'code' => 405];
    }

    private function handleDashboard($method) {
        if ($method !== 'GET') {
            return ['error' => 'Método não permitido', 'code' => 405];
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Stats básicas
        $stats = [];
        
        // Total de pets
        $stmt = $db->query("SELECT COUNT(*) FROM pets WHERE ativo = TRUE");
        $stats['total_pets'] = $stmt->fetchColumn();
        
        // Total de donos
        $stmt = $db->query("SELECT COUNT(*) FROM donos WHERE ativo = TRUE");
        $stats['total_donos'] = $stmt->fetchColumn();
        
        // Consultas hoje
        $stmt = $db->query("SELECT COUNT(*) FROM consultas WHERE DATE(data_consulta) = CURDATE() AND status != 'cancelada'");
        $stats['consultas_hoje'] = $stmt->fetchColumn();
        
        // Consultas da semana
        $stmt = $db->query("SELECT COUNT(*) FROM consultas WHERE WEEK(data_consulta) = WEEK(CURDATE()) AND YEAR(data_consulta) = YEAR(CURDATE())");
        $stats['consultas_semana'] = $stmt->fetchColumn();
        
        // Próximas consultas
        $model = new Consulta();
        $stats['proximas'] = $model->getProximas(7);
        
        return ['data' => $stats];
    }
}
