<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Verificar se foi fornecido um ID de viagem
if (!isset($_GET['id_viagem'])) {
    header("Location: historico-aluno.php");
    exit();
}

$id_viagem = $_GET['id_viagem'];
$id_avaliador = $_SESSION['user_id'];

// Verificar se o usuário já avaliou esta viagem
$stmt = $conn->prepare("SELECT id_avaliacao FROM Avaliacoes WHERE id_avaliador = ? AND id_viagem = ?");
$stmt->bind_param("ii", $id_avaliador, $id_viagem);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('Você já avaliou esta viagem!'); window.location.href='historico-aluno.php';</script>";
    exit();
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classificacao = $_POST['classificacao'];
    $comentario = $_POST['comentario'];
    $anonima = isset($_POST['anonima']) ? 1 : 0;
    
    // Obter o ID do condutor C001-JoaoS
    $stmt = $conn->prepare("SELECT id_condutor FROM Condutores WHERE nome_condutor = 'C001-JoaoS'");
    $stmt->execute();
    $result = $stmt->get_result();
    $condutor = $result->fetch_assoc();
    
    if (!$condutor) {
        echo "<script>alert('Erro ao encontrar o condutor.'); window.location.href='historico-aluno.php';</script>";
        exit();
    }
    
    $id_avaliado = $condutor['id_condutor'];
    
    // Inserir a avaliação
    $stmt = $conn->prepare("INSERT INTO Avaliacoes (id_avaliador, id_avaliado, id_viagem, classificacao, comentario, anonima) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisis", $id_avaliador, $id_avaliado, $id_viagem, $classificacao, $comentario, $anonima);
    
    if ($stmt->execute()) {
        // Atualizar a média de avaliações do condutor
        $stmt = $conn->prepare("
            UPDATE Condutores c 
            SET 
                total_avaliacoes = total_avaliacoes + 1,
                media_avaliacoes = (
                    SELECT AVG(classificacao) 
                    FROM Avaliacoes 
                    WHERE id_avaliado = c.id_condutor
                )
            WHERE c.id_condutor = ?
        ");
        $stmt->bind_param("i", $id_avaliado);
        $stmt->execute();
        
        echo "<script>alert('Avaliação enviada com sucesso!'); window.location.href='historico-aluno.php';</script>";
    } else {
        echo "<script>alert('Erro ao enviar avaliação. Por favor, tente novamente.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Viagem - ESTransportado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .rating input {
            display: none;
        }
        .rating label {
            cursor: pointer;
            font-size: 30px;
            color: #ddd;
            padding: 5px;
        }
        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Avaliar Viagem</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label">Classificação:</label>
                                <div class="rating">
                                    <input type="radio" name="classificacao" value="5" id="5" required>
                                    <label for="5">★</label>
                                    <input type="radio" name="classificacao" value="4" id="4">
                                    <label for="4">★</label>
                                    <input type="radio" name="classificacao" value="3" id="3">
                                    <label for="3">★</label>
                                    <input type="radio" name="classificacao" value="2" id="2">
                                    <label for="2">★</label>
                                    <input type="radio" name="classificacao" value="1" id="1">
                                    <label for="1">★</label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="comentario" class="form-label">Comentário:</label>
                                <textarea class="form-control" id="comentario" name="comentario" rows="4" required></textarea>
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="anonima" name="anonima">
                                <label class="form-check-label" for="anonima">Avaliação anônima</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                                <a href="historico-aluno.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 