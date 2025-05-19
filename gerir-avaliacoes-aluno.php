<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Obter o ID do aluno logado
$id_aluno = $_SESSION['user_id'];

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $acao = $_POST['acao'];
        
        if ($acao === 'adicionar') {
            // Adicionar nova avaliação
            $id_viagem = $_POST['id_viagem'];
            $id_condutor = $_POST['id_condutor'];
            $classificacao = $_POST['classificacao'];
            $comentario = $_POST['comentario'];
            $anonima = isset($_POST['anonima']) ? 1 : 0;
            
            // Verificar se o aluno já avaliou esta viagem
            $stmt = $conn->prepare("SELECT * FROM Avaliacoes WHERE id_avaliador = ? AND id_viagem = ?");
            $stmt->bind_param("ii", $id_aluno, $id_viagem);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $erro = "Você já avaliou esta viagem.";
            } else {
                // Inserir avaliação
                $stmt = $conn->prepare("INSERT INTO Avaliacoes (id_avaliador, id_avaliado, id_viagem, classificacao, comentario, anonima) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiisd", $id_aluno, $id_condutor, $id_viagem, $classificacao, $comentario, $anonima);
                $stmt->execute();
                
                // Atualizar média de avaliações do condutor
                atualizarMediaCondutor($id_condutor, $conn);
                
                header("Location: minhas-avaliacoes.php");
                exit();
            }
        } elseif ($acao === 'editar') {
            // Editar avaliação existente
            $id_avaliacao = $_POST['id_avaliacao'];
            $classificacao = $_POST['classificacao'];
            $comentario = $_POST['comentario'];
            $anonima = isset($_POST['anonima']) ? 1 : 0;
            
            // Verificar se a avaliação pertence ao aluno logado
            $stmt = $conn->prepare("SELECT id_avaliado FROM Avaliacoes WHERE id_avaliacao = ? AND id_avaliador = ?");
            $stmt->bind_param("ii", $id_avaliacao, $id_aluno);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id_condutor = $row['id_avaliado'];
                
                // Atualizar avaliação
                $stmt = $conn->prepare("UPDATE Avaliacoes SET classificacao = ?, comentario = ?, anonima = ? WHERE id_avaliacao = ?");
                $stmt->bind_param("isii", $classificacao, $comentario, $anonima, $id_avaliacao);
                $stmt->execute();
                
                // Atualizar média de avaliações do condutor
                atualizarMediaCondutor($id_condutor, $conn);
                
                header("Location: minhas-avaliacoes.php");
                exit();
            }
        } elseif ($acao === 'remover') {
            // Remover avaliação
            $id_avaliacao = $_POST['id_avaliacao'];
            
            // Verificar se a avaliação pertence ao aluno logado e obter id_condutor
            $stmt = $conn->prepare("SELECT id_avaliado FROM Avaliacoes WHERE id_avaliacao = ? AND id_avaliador = ?");
            $stmt->bind_param("ii", $id_avaliacao, $id_aluno);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id_condutor = $row['id_avaliado'];
                
                // Remover avaliação
                $stmt = $conn->prepare("DELETE FROM Avaliacoes WHERE id_avaliacao = ?");
                $stmt->bind_param("i", $id_avaliacao);
                $stmt->execute();
                
                // Atualizar média de avaliações do condutor
                atualizarMediaCondutor($id_condutor, $conn);
                
                header("Location: minhas-avaliacoes.php");
                exit();
            }
        }
    }
}

// Função para atualizar a média de avaliações do condutor
function atualizarMediaCondutor($id_condutor, $conn) {
    // Calcular nova média e total de avaliações
    $stmt = $conn->prepare("SELECT AVG(classificacao) as media, COUNT(*) as total FROM Avaliacoes WHERE id_avaliado = ?");
    $stmt->bind_param("i", $id_condutor);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $media = $row['media'] ? round($row['media'], 2) : 0;
    $total = $row['total'];
    
    // Atualizar dados do condutor
    $stmt = $conn->prepare("UPDATE Condutores SET total_avaliacoes = ?, media_avaliacoes = ? WHERE id_condutor = ?");
    $stmt->bind_param("idi", $total, $media, $id_condutor);
    $stmt->execute();
}

// Obter viagens completadas pelo aluno que ainda não foram avaliadas
$viagens_nao_avaliadas = [];
$query = "SELECT v.id_viagem, v.origem, v.destino, v.data_partida, c.id_condutor, c.nome_condutor
          FROM Viagens v
          JOIN Reservas r ON v.id_viagem = r.id_viagem
          JOIN Passageiros p ON r.id_passageiro = p.id_passageiro
          JOIN Condutores c ON true  -- Precisamos obter o condutor associado
          WHERE p.id_utilizador = ? 
          AND v.estado = 'completo'
          AND NOT EXISTS (
            SELECT 1 FROM Avaliacoes a 
            WHERE a.id_avaliador = ? 
            AND a.id_viagem = v.id_viagem
          )
          ORDER BY v.data_partida DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_aluno, $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $viagens_nao_avaliadas[] = $row;
}

// Obter avaliações feitas pelo aluno
$avaliacoes = [];
$query = "SELECT a.*, c.nome_condutor, v.origem, v.destino, v.data_partida
          FROM Avaliacoes a
          JOIN Condutores c ON a.id_avaliado = c.id_condutor
          JOIN Viagens v ON a.id_viagem = v.id_viagem
          WHERE a.id_avaliador = ?
          ORDER BY a.data_avaliacao DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $avaliacoes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado - Minhas Avaliações</title>
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

    /* Adicionar avaliação button */
    .btn-adicionar {
      background-color: #c2ff22;
      color: #333;
      font-weight: bold;
      padding: 12px 25px;
      border-radius: 12px;
      border: none;
      margin-bottom: 30px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .btn-adicionar:hover {
      background-color: #b3ef13;
    }

    /* Viagens para avaliar section */
    .section-title {
      color: white;
      margin-top: 40px;
      margin-bottom: 20px;
      font-weight: bold;
      font-size: 1.5em;
    }
  </style>
</head>
<body>
  <header>
    <a href="pagina-aluno.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>   
  </header>

  <!-- Main Content -->
  <div class="main-container">
    <h1>Minhas Avaliações</h1>

    <div class="tab-container">
      <!-- Botão para adicionar avaliação -->
      <?php if (!empty($viagens_nao_avaliadas)): ?>
      <button type="button" class="btn-adicionar" data-bs-toggle="modal" data-bs-target="#adicionarAvaliacaoModal">
        <i class='bx bx-plus'></i> Adicionar Avaliação
      </button>
      <?php endif; ?>

      <?php if (!empty($avaliacoes)): ?>
        <!-- Listagem de avaliações do aluno -->
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
                <span><i class='bx bx-user-voice'></i> <?= htmlspecialchars($avaliacao['nome_condutor']) ?></span>
                <span><i class='bx bx-calendar'></i> <?= date('d/m/Y H:i', strtotime($avaliacao['data_avaliacao'])) ?></span>
                <span><i class='bx bx-car'></i> <?= date('d/m/Y', strtotime($avaliacao['data_partida'])) ?></span>
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
                <input type="hidden" name="acao" value="remover">
                <button type="submit" class="btn-action btn-remove" onclick="return confirm('Tem certeza que deseja remover esta avaliação?')">
                  <i class='bx bx-trash'></i>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-registros">
          Você ainda não fez nenhuma avaliação
        </div>
      <?php endif; ?>
      
      <!-- Viagens que podem ser avaliadas, se houver -->
      <?php if (!empty($viagens_nao_avaliadas) && empty($avaliacoes)): ?>
        <div class="section-title">Viagens disponíveis para avaliar:</div>
        <p class="text-white">Selecione "Adicionar Avaliação" para avaliar uma das suas viagens recentes.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal Adicionar Avaliação -->
  <div class="modal fade" id="adicionarAvaliacaoModal" tabindex="-1" aria-labelledby="adicionarAvaliacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="adicionarAvaliacaoModalLabel">Adicionar Avaliação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="acao" value="adicionar">
            
            <div class="mb-4">
              <label for="selectViagem" class="form-label">Selecione a Viagem</label>
              <select class="form-select" id="selectViagem" name="id_viagem" required>
                <?php foreach ($viagens_nao_avaliadas as $viagem): ?>
                  <option value="<?= $viagem['id_viagem'] ?>" data-condutor="<?= $viagem['id_condutor'] ?>">
                    <?= date('d/m/Y', strtotime($viagem['data_partida'])) ?> - 
                    <?= htmlspecialchars($viagem['origem']) ?> → <?= htmlspecialchars($viagem['destino']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="id_condutor" id="hiddenCondutor">
            </div>
            
            <div class="mb-4">
              <label for="selectClassificacao" class="form-label">Classificação</label>
              <select class="form-select" id="selectClassificacao" name="classificacao" required>
                <option value="1">★☆☆☆☆</option>
                <option value="2">★★☆☆☆</option>
                <option value="3">★★★☆☆</option>
                <option value="4">★★★★☆</option>
                <option value="5" selected>★★★★★</option>
              </select>
            </div>
            
            <div class="mb-4">
              <label for="textareaComentario" class="form-label">Comentário</label>
              <textarea class="form-control" id="textareaComentario" name="comentario" rows="4" required></textarea>
            </div>
            
            <div class="mb-4 form-check">
              <input type="checkbox" class="form-check-input" id="checkboxAnonima" name="anonima">
              <label class="form-check-label" for="checkboxAnonima">Avaliação anónima</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Adicionar Avaliação</button>
          </div>
        </form>
      </div>
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
            
            <div class="mb-4 form-check">
              <input type="checkbox" class="form-check-input" id="modalAnonima" name="anonima">
              <label class="form-check-label" for="modalAnonima">Avaliação anónima</label>
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
            <h3>Links <span>Rápidos</span></h3>
            <ul>
              <li><a href="minhas-viagens.php">Minhas viagens</a></li>
              <li><a href="procurar-viagens.php">Procurar viagens</a></li>
              <li><a href="minhas-avaliacoes.php">Minhas avaliações</a></li>
              <li><a href="ajuda.php">Ajuda</a></li>
            </ul>
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
    // Selecionar condutor quando a viagem for selecionada
    document.getElementById('selectViagem').addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const condutorId = selectedOption.getAttribute('data-condutor');
      document.getElementById('hiddenCondutor').value = condutorId;
    });

    // Inicializar valor do condutor ao carregar a página
    window.addEventListener('DOMContentLoaded', function() {
      const viagemSelect = document.getElementById('selectViagem');
      if (viagemSelect && viagemSelect.options.length > 0) {
        const condutorId = viagemSelect.options[0].getAttribute('data-condutor');
        document.getElementById('hiddenCondutor').value = condutorId;
      }
    });

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