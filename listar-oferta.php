<?php
session_start();
require_once 'basedados/basedados.h';

if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Buscar propostas do usuário logado
$query = "SELECT p.*, 
                 DATE_FORMAT(p.data_partida, '%H:%i') as hora_partida,
                 DATE_FORMAT(p.data_partida, '%H:%i') as hora_chegada_estimada
          FROM PropostasTransporte p
          JOIN Utilizadores u ON p.id_aluno = u.id_utilizador
          WHERE u.id_utilizador = ? AND p.estado = 'ativo'
          ORDER BY p.data_partida ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$propostas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Minhas Propostas - ESTransportado</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

  <style>
    .container {
      max-width: 800px;
      margin: 30px auto;
      background: #111;
      padding: 20px;
      border-radius: 10px;
    }
    .form-section, .results-section {
      margin-bottom: 30px;
    }
    .trip-type {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }
    .trip-type button {
      flex: 1;
      padding: 10px;
      border: none;
      cursor: pointer;
      background: #333;
      color: white;
      border-radius: 5px;
    }
    .trip-type button.active {
      background: #c2ff22;
      color: black;
    }
    .form-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .form-group {
      flex: 1;
      min-width: 150px;
    }
    .form-group input {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      background: #333;
      color: white;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
    }
    
    .viagem {
      background: #222;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 15px;
    }
    .viagem strong {
      color: #c2ff22;
    }
    .viagem .btn-container {
      margin-top: 10px;
      display: flex;
      gap: 10px;
    }
    .viagem button {
      padding: 10px;
      background: #c2ff22;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      color: black;
    }

    .proposta-viagem {
      background: #111;
      border: 2px dashed #c2ff22;
      text-align: center;
      margin: 20px auto 0 auto;
      margin-bottom: 20px;
      padding: 20px;
      border-radius: 10px;
      max-width: 800px;
    }

    .proposta-viagem p {
      color: white;
      margin: 10px 0;
      font-size: 1rem;
    }

    .proposta-viagem p strong {
      color: #c2ff22;
      font-size: 1.1rem;
    }

    .proposta-viagem button {
      background: #c2ff22;
      color: black;
      font-weight: bold;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 15px;
    }

    .page-title {
      color: #c2ff22;
      text-align: center;
      margin-bottom: 30px;
    }

    .btn-primary {
      background-color: #c2ff22;
      color: black;
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

    <section class="contactos">
        <h2 class="page-title">Suas Propostas de Transporte</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" style="max-width: 800px; margin: 0 auto 20px auto;">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <div class="container">
            <div class="results-section">
                <?php if (empty($propostas)): ?>
                    <div class="proposta-viagem">
                        <p>Você ainda não criou nenhuma proposta de transporte.</p>
                        <a href="criar-proposta.php" class="btn btn-primary">Criar Proposta</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($propostas as $proposta): ?>
                        <div class="viagem">
                            <p>
                                <strong><?= htmlspecialchars($proposta['hora_partida']) ?></strong> - 
                                <?= htmlspecialchars($proposta['origem']) ?> → 
                                <strong><?= htmlspecialchars($proposta['hora_chegada_estimada']) ?></strong> - 
                                <?= htmlspecialchars($proposta['destino']) ?>
                            </p>
                            <p>
                                Lotação: <?= htmlspecialchars($proposta['lotacao_maxima']) ?> lugares | 
                                Preço: €<?= number_format($proposta['preco'], 2) ?> | 
                                Tipo: <?= $proposta['tipo'] === 'publico' ? 'Público' : 'Privado' ?>
                            </p>
                            <div class="btn-container">
                                <a href="editar-proposta.php?id=<?= $proposta['id_proposta'] ?>" class="btn btn-primary">Editar</a>
                                <button onclick="eliminarProposta(<?= $proposta['id_proposta'] ?>)" class="btn btn-primary">Eliminar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Rodapé -->
    <footer class="rodape">
        <div class="container">
            <div class="row">
                <!-- Sobre -->
                <div class="col-md-4">
                    <div class="rodape-sobre">
                        <h3>Sobre a <span>EST</span>ransportado</h3>
                        <p>A ESTransportado oferece soluções de transporte eficientes e acessíveis para estudantes, ligando-os com as suas instituições de ensino.</p>
                    </div>
                </div>
                
                <!-- Contacto -->
                <div class="col-md-4">
                    <div class="rodape-contactos">
                        <h3>Contacte-nos</h3>
                        <ul>
                            <li><strong>Email:</strong> info@estransportado.pt</li>
                            <li><strong>Telefone:</strong> +351 123 456 789</li>
                            <li><strong>Endereço:</strong> Rua da Universidade, 1000 - Castelo Branco, Portugal</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="rodape-direitos">
                <p>&copy; 2025 ESTransportado. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
    async function eliminarProposta(id_proposta) {
        if (!id_proposta || isNaN(id_proposta)) {
            console.error('ID de proposta inválido:', id_proposta);
            showAlert('danger', 'ID de proposta inválido');
            return;
        }

        if (!confirm('Tem certeza que deseja eliminar esta proposta?\nEsta ação não pode ser desfeita.')) {
            return;
        }
        
        try {
            // Mostrar feedback visual
            const btn = document.querySelector(`button[onclick="eliminarProposta(${id_proposta})"]`);
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Eliminando...';
            btn.disabled = true;

            // Enviar requisição
            const response = await fetch('eliminar-proposta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_proposta=${id_proposta}`
            });
            
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Erro ao processar a solicitação');
            }

            // Sucesso - animar remoção
            const propostaElement = btn.closest('.viagem');
            propostaElement.style.transition = 'all 0.3s ease';
            propostaElement.style.opacity = '0';
            propostaElement.style.height = '0';
            propostaElement.style.margin = '0';
            propostaElement.style.padding = '0';
            propostaElement.style.overflow = 'hidden';
            
            setTimeout(() => {
                propostaElement.remove();
                
                // Verificar se não há mais propostas
                if (document.querySelectorAll('.viagem').length === 0) {
                    document.querySelector('.results-section').innerHTML = `
                        <div class="proposta-viagem">
                            <p>Você não tem propostas ativas no momento.</p>
                            <a href="criar-proposta.php" class="btn btn-primary">Criar Nova Proposta</a>
                        </div>`;
                }
                
                showAlert('success', result.message || 'Proposta eliminada com sucesso');
            }, 300);

        } catch (error) {
            console.error('Erro:', error);
            showAlert('danger', error.message);
            
            // Restaurar botão
            const btn = document.querySelector(`button[onclick="eliminarProposta(${id_proposta})"]`);
            if (btn) {
                btn.innerHTML = 'Eliminar';
                btn.disabled = false;
            }
        }
    }

    function showAlert(type, message) {
        // Remover alertas existentes
        const existingAlerts = document.querySelectorAll('.alert-dynamic');
        existingAlerts.forEach(alert => alert.remove());

        // Criar novo alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dynamic`;
        alertDiv.style.maxWidth = '800px';
        alertDiv.style.margin = '0 auto 20px auto';
        alertDiv.textContent = message;
        
        // Inserir antes do container
        const container = document.querySelector('.contactos');
        container.insertBefore(alertDiv, document.querySelector('.container'));
        
        // Remover após 5 segundos
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
    </script>
</body>
</html>