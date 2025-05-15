<?php
session_start();

// Verificar se o usuário está logado e é admin ou gestor
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'gestor')) {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $id_avaliacao = $_POST['id_avaliacao'];
        $id_utilizador = $_POST['id_utilizador'];
        $acao = $_POST['acao'];
        

        
        if ($acao === 'editar') {
        // Atualizar avaliação (sem alterar o status anônimo)
        $classificacao = $_POST['classificacao'];
        $comentario = $_POST['comentario'];
        
        $stmt = $conn->prepare("UPDATE Avaliacoes SET classificacao = ?, comentario = ? WHERE id_avaliacao = ?");
        $stmt->bind_param("isi", $classificacao, $comentario, $id_avaliacao);
        $stmt->execute();
        } elseif ($acao === 'remover') {
            // Remover avaliação
            $stmt = $conn->prepare("DELETE FROM Avaliacoes WHERE id_avaliacao = ?");
            $stmt->bind_param("i", $id_avaliacao);
            $stmt->execute();
            
        } elseif ($acao === 'banir') {
            // Banir utilizador (7 dias)
            $data_fim = date('Y-m-d H:i:s', strtotime('+7 days'));
            $motivo = "Avaliação inapropriada (ID: $id_avaliacao)";
            
            // 1. Registrar o banimento
            $stmt = $conn->prepare("INSERT INTO Banimentos (id_utilizador, id_gestor, motivo, data_inicio, data_fim, ativo) VALUES (?, ?, ?, NOW(), ?, TRUE)");
            $stmt->bind_param("iiss", $id_utilizador, $_SESSION['user_id'], $motivo, $data_fim);
            $stmt->execute();
            
            // 2. Desativar a conta do utilizador
            $stmt = $conn->prepare("UPDATE Utilizadores SET ativo = FALSE WHERE id_utilizador = ?");
            $stmt->bind_param("i", $id_utilizador);
            $stmt->execute();
            
            // 3. Remover a avaliação
            $stmt = $conn->prepare("DELETE FROM Avaliacoes WHERE id_avaliacao = ?");
            $stmt->bind_param("i", $id_avaliacao);
            $stmt->execute();
        }
        
        header("Location: gerir-avaliacoes.php");
        exit();
    }
}

// Obter avaliações
$avaliacoes = [];
$query = "SELECT a.*, u.nome_completo as nome_avaliador, u.id_utilizador as id_avaliador, 
          c.nome_condutor, v.origem, v.destino
          FROM Avaliacoes a
          JOIN Utilizadores u ON a.id_avaliador = u.id_utilizador
          JOIN Condutores c ON a.id_avaliado = c.id_condutor
          JOIN Viagens v ON a.id_viagem = v.id_viagem
          ORDER BY a.data_avaliacao DESC";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $avaliacoes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado - Gerir Avaliações</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <style>
    /* Main Content */
    .main-container {
      max-width: 95%;
      width: 1400px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .tab-container {
      background: rgb(27, 27, 27);
      border-radius: 20px;
      padding: 30px;
      width: 100%;
    }
    
    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-weight: bold;
      color: white;
    }
    
    /* Avaliação Items */
    .avaliacao-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #222;
      border-radius: 15px;
      padding: 25px 30px;
      margin-bottom: 25px;
      color: white;
    }
    
    .avaliacao-info {
      flex-grow: 1;
      margin-right: 40px;
    }
    
    .avaliacao-info h4 {
      color: #c2ff22;
      margin-top: 0;
      margin-bottom: 15px;
      font-size: 1.3em;
      display: flex;
      align-items: center;
    }
    
    .estrelas {
      color: #ffc107;
      font-size: 1.3em;
      margin-right: 15px;
    }
    
    .comentario {
      margin: 15px 0;
      font-size: 1.1em;
      line-height: 1.5;
    }
    
    .avaliacao-detalhes {
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      font-size: 0.95em;
      color: #ccc;
    }
    
    .avaliacao-detalhes span {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .anonima-badge {
      background: #555;
      color: #eee;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 0.8em;
      margin-left: 15px;
    }
    
    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 15px;
    }
    
    .btn-action {
      background-color: #333;
      border: none;
      border-radius: 10px;
      padding: 12px;
      color: white;
      font-size: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      min-width: 50px;
      transition: all 0.3s;
    }
    
    .btn-edit {
      color: #c2ff22;
    }
    
    .btn-remove {
      color: #ff6b6b;
    }
    
    .btn-ban {
      color: #ff9e3b;
    }
    
    .btn-action:hover {
      background-color: #444;
    }
    
    /* Modal Styles */
    .modal-content {
      background-color: #222;
      color: white;
      border-radius: 20px;
      border: none;
    }
    
    .modal-header {
      background-color: #c2ff22;
      color: #333;
      border-bottom: none;
      border-radius: 20px 20px 0 0;
      padding: 20px;
    }
    
    .modal-title {
      font-weight: bold;
    }
    
    .modal-body {
      padding: 25px;
    }
    
    .modal-footer {
      border-top: none;
      padding: 20px;
    }
    
    .btn-secondary {
      background-color: #555;
      border: none;
      border-radius: 10px;
    }
    
    .btn-primary {
      background-color: #c2ff22;
      color: #333;
      border: none;
      border-radius: 10px;
      font-weight: bold;
    }
    
    .form-control, .form-select {
      background-color: #333;
      color: white;
      border: 1px solid #444;
      border-radius: 10px;
      padding: 10px;
    }
    
    .form-control:focus, .form-select:focus {
      background-color: #333;
      color: white;
      border-color: #c2ff22;
      box-shadow: 0 0 0 0.25rem rgba(194, 255, 34, 0.25);
    }
    
    .form-check-input {
      background-color: #333;
      border-color: #555;
    }
    
    .form-check-input:checked {
      background-color: #c2ff22;
      border-color: #c2ff22;
    }
    
    .form-label {
      color: #c2ff22;
      margin-bottom: 8px;
    }
    
    /* No registros message */
    .no-registros {
      background-color: #222;
      color: white;
      padding: 20px;
      border-radius: 15px;
      text-align: center;
    }
  </style>
</head>
<body>
  <header>
    <a href="<?php
      switch($_SESSION['user_type']) {
        case 'admin':
          echo 'pagina-admin.php';
          break;
        case 'gestor':
          echo 'pagina-gestor.php';
          break;
      }
    ?>" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>   
  </header>

  <!-- Main Content -->
  <div class="main-container">
    <h1>Gerir Avaliações</h1>
    
    <div class="tab-container">
      <?php if (empty($avaliacoes)): ?>
        <div class="no-registros">
          Não há avaliações para gerir
        </div>
      <?php else: ?>
        <?php foreach ($avaliacoes as $avaliacao): ?>
          <div class="avaliacao-item">
            <div class="avaliacao-info">
              <h4>
                <span class="estrelas"><?= str_repeat('★', $avaliacao['classificacao']) . str_repeat('☆', 5 - $avaliacao['classificacao']) ?></span>
                <?= htmlspecialchars($avaliacao['origem']) ?> → <?= htmlspecialchars($avaliacao['destino']) ?>
                <?php if ($avaliacao['anonima']): ?>
                  <span class="anonima-badge">Anónima</span>
                <?php endif; ?>
              </h4>
              
              <div class="comentario"><?= htmlspecialchars($avaliacao['comentario']) ?></div>
              
              <div class="avaliacao-detalhes">
                <span><i class='bx bx-user'></i> 
                  <?= $avaliacao['anonima'] ? 'Anónimo' : htmlspecialchars($avaliacao['nome_avaliador']) ?>
                </span>
                <span><i class='bx bx-user-voice'></i> <?= htmlspecialchars($avaliacao['nome_condutor']) ?></span>
                <span><i class='bx bx-calendar'></i> <?= date('d/m/Y H:i', strtotime($avaliacao['data_avaliacao'])) ?></span>
              </div>
            </div>
            
            <div class="action-buttons">
              <!-- Botão Editar -->
              <button type="button" class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editarAvaliacaoModal"
                data-id="<?= $avaliacao['id_avaliacao'] ?>"
                data-classificacao="<?= $avaliacao['classificacao'] ?>"
                data-comentario="<?= htmlspecialchars($avaliacao['comentario']) ?>"
                data-anonima="<?= $avaliacao['anonima'] ?>">
                <i class='bx bx-edit'></i>
              </button>
              
              <!-- Botão Remover -->
              <form method="POST" style="display: inline;">
                <input type="hidden" name="id_avaliacao" value="<?= $avaliacao['id_avaliacao'] ?>">
                <input type="hidden" name="id_utilizador" value="<?= $avaliacao['id_avaliador'] ?>">
                <input type="hidden" name="acao" value="remover">
                <button type="submit" class="btn-action btn-remove" onclick="return confirm('Tem certeza que deseja remover esta avaliação?')">
                  <i class='bx bx-trash'></i>
                </button>
              </form>
              
              <!-- Botão Banir -->
              <form method="POST" style="display: inline;">
                <input type="hidden" name="id_avaliacao" value="<?= $avaliacao['id_avaliacao'] ?>">
                <input type="hidden" name="id_utilizador" value="<?= $avaliacao['id_avaliador'] ?>">
                <input type="hidden" name="acao" value="banir">
                <button type="submit" class="btn-action btn-ban" onclick="return confirm('Tem certeza que deseja banir este utilizador por 7 dias?')">
                  <i class='bx bx-block'></i>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal Editar Avaliação -->
  <div class="modal fade" id="editarAvaliacaoModal" tabindex="-1" aria-labelledby="editarAvaliacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editarAvaliacaoModalLabel">Editar Avaliação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="id_avaliacao" id="modalAvaliacaoId">
            <input type="hidden" name="acao" value="editar">
            
            <div class="mb-4">
              <label for="modalClassificacao" class="form-label">Classificação</label>
              <select class="form-select" id="modalClassificacao" name="classificacao" required>
                <option value="1">★☆☆☆☆</option>
                <option value="2">★★☆☆☆</option>
                <option value="3">★★★☆☆</option>
                <option value="4">★★★★☆</option>
                <option value="5">★★★★★</option>
              </select>
            </div>
            
            <div class="mb-4">
              <label for="modalComentario" class="form-label">Comentário</label>
              <textarea class="form-control" id="modalComentario" name="comentario" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <footer class="rodape">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <div class="rodape-sobre">
            <h3>Sobre a <span>EST</span>ransportado</h3>
            <p>A ESTransportado oferece soluções de transporte eficientes e acessíveis para estudantes, ligando-os com as suas instituições de ensino.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="rodape-links">
          </div>
        </div>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Boxicons JS -->
  <script src="https://unpkg.com/boxicons@latest/dist/boxicons.js"></script>
  
  <script>
    // Inicializar modal de edição
    const editarModal = document.getElementById('editarAvaliacaoModal');
    if (editarModal) {
      editarModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const classificacao = button.getAttribute('data-classificacao');
        const comentario = button.getAttribute('data-comentario');
        const anonima = button.getAttribute('data-anonima') === '1';
        
        document.getElementById('modalAvaliacaoId').value = id;
        document.getElementById('modalClassificacao').value = classificacao;
        document.getElementById('modalComentario').value = comentario;
        document.getElementById('modalAnonima').checked = anonima;
      });
    }
  </script>
</body>
</html>