<?php
/**
 * Modelo de Pedido
 * Freydy Restaurant App
 */

require_once __DIR__ . '/../config/database.php';

class Pedido {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Cria um novo pedido
     */
    public function criar($dados) {
        try {
            $this->db->beginTransaction();
            
            // Gera número único do pedido
            $numeroPedido = $this->gerarNumeroPedido($dados['restaurante_id']);
            
            // Insere o pedido
            $sql = "INSERT INTO pedidos (restaurante_id, mesa_id, usuario_id, numero_pedido, observacoes) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $this->db->query($sql, [
                $dados['restaurante_id'],
                $dados['mesa_id'],
                $dados['usuario_id'],
                $numeroPedido,
                $dados['observacoes'] ?? ''
            ]);
            
            $pedidoId = $this->db->lastInsertId();
            
            // Insere os itens do pedido
            $valorTotal = 0;
            foreach ($dados['itens'] as $item) {
                $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario, observacoes) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $this->db->query($sql, [
                    $pedidoId,
                    $item['produto_id'],
                    $item['quantidade'],
                    $item['preco_unitario'],
                    $item['observacoes'] ?? ''
                ]);
                
                $valorTotal += $item['quantidade'] * $item['preco_unitario'];
            }
            
            // Atualiza o valor total do pedido
            $sql = "UPDATE pedidos SET valor_total = ? WHERE id = ?";
            $this->db->query($sql, [$valorTotal, $pedidoId]);
            
            // Registra no histórico
            $this->registrarHistorico($pedidoId, null, 'pendente', $dados['usuario_id']);
            
            $this->db->commit();
            
            return $pedidoId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Atualiza status do pedido
     */
    public function atualizarStatus($pedidoId, $novoStatus, $usuarioId, $observacao = '') {
        try {
            $this->db->beginTransaction();
            
            // Busca status atual
            $pedido = $this->buscarPorId($pedidoId);
            $statusAnterior = $pedido['status'];
            
            // Atualiza status
            $sql = "UPDATE pedidos SET status = ?, data_atualizacao = NOW() WHERE id = ?";
            $this->db->query($sql, [$novoStatus, $pedidoId]);
            
            // Registra no histórico
            $this->registrarHistorico($pedidoId, $statusAnterior, $novoStatus, $usuarioId, $observacao);
            
            $this->db->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Busca pedido por ID com detalhes completos
     */
    public function buscarPorId($id) {
        $sql = "SELECT p.*, m.numero as numero_mesa, u.nome as nome_usuario, r.nome as nome_restaurante
                FROM pedidos p
                INNER JOIN mesas m ON p.mesa_id = m.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                INNER JOIN restaurantes r ON p.restaurante_id = r.id
                WHERE p.id = ?";
        
        $pedido = $this->db->fetch($sql, [$id]);
        
        if ($pedido) {
            $pedido['itens'] = $this->buscarItensPedido($id);
        }
        
        return $pedido;
    }
    
    /**
     * Lista pedidos por restaurante e status
     */
    public function listarPorRestaurante($restauranteId, $status = null, $limit = 50) {
        $sql = "SELECT p.*, m.numero as numero_mesa, u.nome as nome_usuario
                FROM pedidos p
                INNER JOIN mesas m ON p.mesa_id = m.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.restaurante_id = ?";
        
        $params = [$restauranteId];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY p.data_pedido DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lista pedidos por mesa
     */
    public function listarPorMesa($mesaId, $status = null) {
        $sql = "SELECT p.*, u.nome as nome_usuario
                FROM pedidos p
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.mesa_id = ?";
        
        $params = [$mesaId];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY p.data_pedido DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Busca itens de um pedido
     */
    public function buscarItensPedido($pedidoId) {
        $sql = "SELECT ip.*, p.nome as nome_produto, p.descricao as descricao_produto, c.nome as categoria
                FROM itens_pedido ip
                INNER JOIN produtos p ON ip.produto_id = p.id
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE ip.pedido_id = ?
                ORDER BY c.nome, p.nome";
        
        return $this->db->fetchAll($sql, [$pedidoId]);
    }
    
    /**
     * Atualiza status de um item específico
     */
    public function atualizarStatusItem($itemId, $novoStatus) {
        $sql = "UPDATE itens_pedido SET status = ? WHERE id = ?";
        $this->db->query($sql, [$novoStatus, $itemId]);
        
        return true;
    }
    
    /**
     * Busca histórico de um pedido
     */
    public function buscarHistorico($pedidoId) {
        $sql = "SELECT h.*, u.nome as nome_usuario
                FROM historico_pedidos h
                INNER JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.pedido_id = ?
                ORDER BY h.data_mudanca DESC";
        
        return $this->db->fetchAll($sql, [$pedidoId]);
    }
    
    /**
     * Registra mudança no histórico
     */
    private function registrarHistorico($pedidoId, $statusAnterior, $statusNovo, $usuarioId, $observacao = '') {
        $sql = "INSERT INTO historico_pedidos (pedido_id, status_anterior, status_novo, usuario_id, observacao) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $pedidoId,
            $statusAnterior,
            $statusNovo,
            $usuarioId,
            $observacao
        ]);
    }
    
    /**
     * Gera número único do pedido
     */
    private function gerarNumeroPedido($restauranteId) {
        $ano = date('Y');
        $mes = date('m');
        
        $sql = "SELECT COUNT(*) as total FROM pedidos 
                WHERE restaurante_id = ? AND YEAR(data_pedido) = ? AND MONTH(data_pedido) = ?";
        
        $resultado = $this->db->fetch($sql, [$restauranteId, $ano, $mes]);
        $numero = $resultado['total'] + 1;
        
        return sprintf("%d%02d%04d", $ano, $mes, $numero);
    }
    
    /**
     * Calcula estatísticas de pedidos
     */
    public function calcularEstatisticas($restauranteId, $dataInicio = null, $dataFim = null) {
        $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(valor_total) as valor_total,
                    AVG(valor_total) as valor_medio,
                    COUNT(CASE WHEN status = 'entregue' THEN 1 END) as pedidos_entregues,
                    COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as pedidos_cancelados
                FROM pedidos 
                WHERE restaurante_id = ?";
        
        $params = [$restauranteId];
        
        if ($dataInicio) {
            $sql .= " AND data_pedido >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND data_pedido <= ?";
            $params[] = $dataFim;
        }
        
        return $this->db->fetch($sql, $params);
    }

    /**
     * Cria um novo pedido com itens (transacional)
     */
    public function criarComItens($dadosPedido, $itens) {
        try {
            $this->db->beginTransaction();
            // Gera número único do pedido
            $numeroPedido = $this->gerarNumeroPedido($dadosPedido['restaurante_id']);
            // Insere o pedido
            $sql = "INSERT INTO pedidos (restaurante_id, mesa_id, usuario_id, numero_pedido, valor_total, observacoes, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, [
                $dadosPedido['restaurante_id'],
                $dadosPedido['mesa_id'],
                $dadosPedido['usuario_id'],
                $numeroPedido,
                $dadosPedido['valor_total'],
                $dadosPedido['observacao'] ?? '',
                $dadosPedido['status'] ?? 'pendente'
            ]);
            $pedidoId = $this->db->lastInsertId();
            // Insere os itens
            foreach ($itens as $item) {
                $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
                $this->db->query($sql, [
                    $pedidoId,
                    $item['produto_id'],
                    $item['quantidade'],
                    $item['preco_unitario']
                ]);
            }
            // Registra no histórico
            $this->registrarHistorico($pedidoId, null, 'pendente', $dadosPedido['usuario_id']);
            $this->db->commit();
            return $pedidoId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Busca pedido por ID com verificação de restaurante
     */
    public function buscarPorIdComRestaurante($id, $restauranteId) {
        $sql = "SELECT p.*, m.numero as numero_mesa, u.nome as nome_usuario, r.nome as nome_restaurante
                FROM pedidos p
                INNER JOIN mesas m ON p.mesa_id = m.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                INNER JOIN restaurantes r ON p.restaurante_id = r.id
                WHERE p.id = ? AND p.restaurante_id = ?";
        
        $pedido = $this->db->fetch($sql, [$id, $restauranteId]);
        
        if ($pedido) {
            $pedido['itens'] = $this->buscarItensPedido($id);
            $pedido['historico'] = $this->buscarHistoricoCompleto($id, $restauranteId);
        }
        
        return $pedido;
    }
    
    /**
     * Busca histórico completo de um pedido (método público)
     */
    public function buscarHistoricoCompleto($pedidoId, $restauranteId) {
        $sql = "SELECT h.*, u.nome as usuario_nome 
                FROM historico_pedidos h 
                LEFT JOIN usuarios u ON h.usuario_id = u.id 
                WHERE h.pedido_id = ? 
                AND EXISTS (SELECT 1 FROM pedidos p WHERE p.id = h.pedido_id AND p.restaurante_id = ?)
                ORDER BY h.data_alteracao DESC";
        
        return $this->db->fetchAll($sql, [$pedidoId, $restauranteId]);
    }
}
