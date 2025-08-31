<?php
require_once '../../includes/auth.php';
$auth->requerLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Nenhuma imagem foi enviada ou ocorreu um erro no upload');
    }

    $arquivo = $_FILES['imagem'];
    $nomeOriginal = $arquivo['name'];
    $tipo = $arquivo['type'];
    $tamanho = $arquivo['size'];
    $tempPath = $arquivo['tmp_name'];

    // Validar tipo de arquivo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($tipo, $tiposPermitidos)) {
        throw new Exception('Tipo de arquivo não permitido. Use apenas JPG, PNG, GIF ou WebP');
    }

    // Validar tamanho (máximo 5MB)
    $tamanhoMaximo = 5 * 1024 * 1024; // 5MB
    if ($tamanho > $tamanhoMaximo) {
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
    }

    // Gerar nome único para o arquivo
    $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
    $nomeUnico = uniqid() . '_' . time() . '.' . $extensao;
    
    // Caminho de destino
    $pastaDestino = '../../uploads/produtos/';
    $caminhoCompleto = $pastaDestino . $nomeUnico;

    // Verificar se a pasta existe
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    // Mover arquivo
    if (!move_uploaded_file($tempPath, $caminhoCompleto)) {
        throw new Exception('Erro ao salvar o arquivo');
    }

    // Retornar URL da imagem
    $urlImagem = 'uploads/produtos/' . $nomeUnico;

    echo json_encode([
        'success' => true,
        'url' => $urlImagem,
        'nome' => $nomeUnico,
        'message' => 'Imagem enviada com sucesso'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
