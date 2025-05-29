<?php
session_start();
require_once 'basedados/basedados.h';

if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Obter ID da reserva da URL
$id_reserva = $_GET['id'] ?? null;
if (!$id_reserva) {
    header("Location: as-minhas-reservas.php");
    exit();
}

// Buscar dados da reserva
$query = "SELECT r.id_reserva, r.id_viagem, r.lugar, r.preco_total, r.estado,
                 v.origem, v.destino, v.preco as preco_por_lugar,
                 DATE_FORMAT(v.data_partida, '%Y-%m-%d %H:%i') as data_partida,
                 v.lotacao_maxima, v.lotacao_atual
          FROM Reservas r
          JOIN Viagens v ON r.id_viagem = v.id_viagem
          JOIN Passageiros p ON r.id_passageiro = p.id_passageiro
          WHERE r.id_reserva = ? AND p.id_utilizador = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_reserva, $_SESSION['user_id']);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();

if (!$reserva) {
    header("Location: as-minhas-reservas.php");
    exit();
}

// Processar o formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_lugar = trim($_POST['lugar'] ?? '');
    
    // Validação básica do lugar
    if (empty($novo_lugar)) {
        $error = "O lugar não pode estar vazio.";
    } else {
        // Atualizar a reserva
        $update_query = "UPDATE Reservas SET lugar = ? WHERE id_reserva = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $novo_lugar, $id_reserva);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Reserva atualizada com sucesso!";
            header("Location: as-minhas-reservas.php");
            exit();
        } else {
            $error = "Erro ao atualizar a reserva. Por favor, tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reserva - ESTransportado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #111;
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        .container h1 {
            color: #c2ff22;
            text-align: center;
        }
        .form-control {
            background-color: #333;
            color: #fff;
            border: 1px solid #444;
        }
        .btn-primary {
            background-color: #c2ff22;
            color: #000;
            border: none;
        }
    </style>
</head>
<body>
    <header>
    <a href="pagina-aluno.php" class="logo">
        <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
        <li><a href="as-minhas-reservas.php">As minhas reservas</a></li>
        <li><a href="consultar-horarios.php">Consultar horarios</a></li>
        <li><a href="ajuda.php">Ajuda</a></li>
    </ul>

    <!-- Botão de Notificações com contador -->
    <a href="notificacoes.php" class="notification-button">
        <i class="bell-icon">🔔</i>
        <span id="notification-count" class="notification-count">0</span>
    </a>

    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>

    <style>
    /* Estilo para o botão de notificações */
    .notification-button {
        position: relative;
        display: inline-block;
        padding: 8px 12px;
        margin-right: 15px;
        color: #333;
        text-decoration: none;
        font-size: 1.2em;
    }

    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        display: none;
    }
    </style>

    <script>
    // Função para carregar o contador de notificações não lidas
    function loadNotificationCount() {
        fetch('get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const notificationCount = document.getElementById('notification-count');
                if (data.count > 0) {
                    notificationCount.textContent = data.count;
                    notificationCount.style.display = 'block';
                } else {
                    notificationCount.style.display = 'none';
                }
            });
    }

    // Carregar contador quando a página é carregada
    document.addEventListener('DOMContentLoaded', function() {
        loadNotificationCount();
        
        // Atualizar contador periodicamente (a cada 30 segundos)
        setInterval(loadNotificationCount, 30000);
    });
    </script>
    </header>

    <div class="container">
        <h1>Editar Reserva</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Origem</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($reserva['origem'] ?? '') ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Destino</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($reserva['destino'] ?? '') ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Data e Hora de Partida</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($reserva['data_partida'] ?? '') ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label for="lugar" class="form-label">Lugar</label>
                <input type="text" class="form-control" id="lugar" name="lugar" 
                       value="<?= htmlspecialchars($reserva['lugar'] ?? '') ?>" 
                       pattern="[A-Za-z][0-9]+" title="Formato do lugar: Letra seguida de número (ex: A1, B2)" required>
                <small class="text-muted">Formato: Letra seguida de número (ex: A1, B2)</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Preço Total</label>
                <input type="text" class="form-control" value="€<?= isset($reserva['preco_total']) ? number_format($reserva['preco_total'], 2) : '0.00' ?>" readonly>
                <small class="text-muted">Preço por lugar: €<?= isset($reserva['preco_por_lugar']) ? number_format($reserva['preco_por_lugar'], 2) : '0.00' ?></small>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    Atualizar Reserva
                </button>
                <a href="as-minhas-reservas.php" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</body>
</html>