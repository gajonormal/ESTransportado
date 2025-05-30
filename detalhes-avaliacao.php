<?php
session_start();


// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Obter ID da avaliação da URL
$id_avaliacao = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_avaliacao <= 0) {
    header("Location: minhas_viagens.php");
    exit();
}

// Buscar detalhes da avaliação
$stmt = $conn->prepare("
    SELECT a.*, 
           v.origem, v.destino, v.data_partida,
           u.nome_completo as nome_avaliado,
           av.nome_completo as nome_avaliador
    FROM Avaliacoes a
    JOIN Viagens v ON a.id_viagem = v.id_viagem
    JOIN Utilizadores u ON a.id_avaliado = u.id_utilizador
    JOIN Utilizadores av ON a.id_avaliador = av.id_utilizador
    WHERE a.id_avaliacao = ?
");
$stmt->bind_param("i", $id_avaliacao);
$stmt->execute();
$avaliacao = $stmt->get_result()->fetch_assoc();

if (!$avaliacao) {
    header("Location: minhas_viagens.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalhes da Avaliação - ESTransportado</title>
  
  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  
  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">
  
  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  
  <style>
    /* Estilos específicos para a página de detalhes */
    .detail-container {
      max-width: 800px;
      margin: 50px auto;
      padding: 30px;
      background: black;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .detail-header {
      border-bottom: 2px solid #c2ff22;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    
    .detail-title {
      color: #a8e01e;
      font-weight: 700;
    }
    
    .detail-section {
      margin-bottom: 30px;
    }
    
    .detail-label {
      font-weight: 600;
      color: #a8e01e;
    }
    
    .rating {
      font-size: 24px;
      color: #ffc107;
      margin-bottom: 15px;
    }
    
    .comment-box {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      border-left: 4px solid #c2ff22;
      color:black 
    }
    
    .btn-voltar {
      background-color: #a8e01e;
      color: black;
      padding: 10px 25px;
      border-radius: 5px;
      text-decoration: none;
      display: inline-block;
      margin-top: 20px;
      transition: all 0.3s;
    }
    
    .btn-voltar:hover {
      background-color:rgba(169, 224, 30, 0.75);
      color: white;
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
  <main>
    <div class="detail-container">
      <div class="detail-header">
        <h1 class="detail-title">Detalhes da Avaliação</h1>
        <p>Viagem: <?= htmlspecialchars($avaliacao['origem']) ?> → <?= htmlspecialchars($avaliacao['destino']) ?></p>
      </div>
      
      <div class="detail-section">
        <h3>Informações Básicas</h3>
        <div class="row">
          <div class="col-md-6">
            <p><span class="detail-label">Avaliador:</span> <?= htmlspecialchars($avaliacao['nome_avaliador']) ?></p>
            <p><span class="detail-label">Avaliado:</span> <?= htmlspecialchars($avaliacao['nome_avaliado']) ?></p>
          </div>
          <div class="col-md-6">
            <p><span class="detail-label">Data da Viagem:</span> <?= date('d/m/Y H:i', strtotime($avaliacao['data_partida'])) ?></p>
            <p><span class="detail-label">Data da Avaliação:</span> <?= date('d/m/Y H:i', strtotime($avaliacao['data_avaliacao'])) ?></p>
          </div>
        </div>
      </div>
      
      <div class="detail-section">
        <h3>Avaliação</h3>
        <div class="rating">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <?php if ($i <= $avaliacao['classificacao']): ?>
              ★
            <?php else: ?>
              ☆
            <?php endif; ?>
          <?php endfor; ?>
          (<?= $avaliacao['classificacao'] ?> estrelas)
        </div>
        
        <?php if (!empty($avaliacao['comentario'])): ?>
          <div class="comment-box">
            <p><?= nl2br(htmlspecialchars($avaliacao['comentario'])) ?></p>
          </div>
        <?php else: ?>
          <p>Nenhum comentário foi adicionado.</p>
        <?php endif; ?>
      </div>
      
      <div class="detail-section">
        <h3>Anonimato</h3>
        <p>Esta avaliação foi feita <?= $avaliacao['anonima'] ? 'anonimamente' : 'publicamente' ?>.</p>
      </div>
      
      <a href="javascript:history.back()" class="btn-voltar">Voltar</a>
    </div>
  </main>

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
        <!-- Links Rápidos -->
        <div class="col-md-4">
          <div class="rodape-links">
            <h3>Links <span>Rápidos</span></h3>
            <ul>
              <li><a href="index.php">Início</a></li>
              <li><a href="servicos.php">Serviços</a></li>
              <li><a href="sobrenos.php">Sobre Nós</a></li>
              <li><a href="contactos.php">Contactos</a></li>
            </ul>
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
        <p>&copy; <?php echo date('Y'); ?> ESTransportado. Todos os direitos reservados.</p>
      </div>
    </div>
  </footer>

  <!-- BOOTSTRAP 5 JS (Sem jQuery) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>