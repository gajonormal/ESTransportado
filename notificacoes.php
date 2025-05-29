<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Buscar notificações do usuário
$userId = $_SESSION['user_id'];
$notificacoes = array(); // Inicializa a variável

$sql = "SELECT id_notificacao, titulo, mensagem, data_criacao, lida 
        FROM Notificacoes 
        WHERE id_utilizador = ? 
        ORDER BY data_criacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notificacoes[] = $row;
}

// Marcar todas como lidas ao acessar a página
$updateSql = "UPDATE Notificacoes SET lida = TRUE WHERE id_utilizador = ? AND lida = FALSE";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("i", $userId);
$updateStmt->execute();
$updateStmt->close();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - ESTransportado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --cor-primaria:rgb(181, 255, 10);
            --cor-secundaria:rgb(181, 255, 10);
            --cor-texto:rgb(255, 255, 255);
            --cor-fundo:rgb(255, 255, 255);
            --cor-borda: #e0e0e0;
        }
        
        .notifications-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: black;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(17, 17, 17, 0.64);
        }
        
        .notifications-header {
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--cor-secundaria);
        }
        
        .notifications-header h1 {
            color: var(--cor-primaria);
            font-weight: 700;
        }
        
        .notifications-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid var(--cor-borda);
            transition: background-color 0.3s;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background-color:rgba(32, 32, 32, 0.5);
        }
        
        .notification-title {
            font-weight: 600;
            color: var(--cor-primaria);
            margin-bottom: 5px;
        }
        
        .notification-message {
            color: var(--cor-texto);
            margin-bottom: 5px;
        }
        
        .notification-time {
            color: #666;
            font-size: 0.85em;
        }
        
        .empty-notifications {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        /* Estilo do header existente */
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
</head>
<body>
    <header>
    <a href="pagina-aluno.php" class="logo">
        <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
        <li><a href="as-minhas-reservas.php">As minhas reservas</a></li>
        <li><a href="consultar-horarios.php">Consultar horarios</a></li>
        <li><a href="listar-oferta.php">Minhas Ofertas</a></li>
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

    <main class="content">
        <div class="notifications-container">
            <div class="notifications-header">
                <h1>Notificações</h1>
                <p>Veja todas as suas notificações</p>
            </div>
            
            <?php if (!empty($notificacoes)): ?>
                <ul class="notifications-list">
                    <?php foreach ($notificacoes as $notificacao): ?>
                        <li class="notification-item">
                            <div class="notification-title"><?php echo htmlspecialchars($notificacao['titulo']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($notificacao['mensagem']); ?></div>
                            <div class="notification-time">
                                <?php echo date('d/m/Y H:i', strtotime($notificacao['data_criacao'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-notifications">
                    <p>Não tem nenhuma notificação</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Esconder contador pois todas foram marcadas como lidas
        document.getElementById('notification-count').style.display = 'none';
        
        // Função para atualizar contador
        function loadNotificationCount() {
            fetch('get_notification_count.php')
                .then(response => response.json())
                .then(data => {
                    const counter = document.getElementById('notification-count');
                    if (data.count > 0) {
                        counter.textContent = data.count;
                        counter.style.display = 'block';
                    } else {
                        counter.style.display = 'none';
                    }
                });
        }
        
        // Atualizar periodicamente
        setInterval(loadNotificationCount, 30000);
    });
    </script>
</body>
</html>