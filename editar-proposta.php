<?php
session_start();
require_once 'basedados/basedados.h';

if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Obter ID da proposta da URL
$id_proposta = $_GET['id'] ?? null;
if (!$id_proposta) {
    header("Location: as-minhas-propostas.php");
    exit();
}

// Buscar dados da proposta
$query = "SELECT p.*, DATE_FORMAT(p.data_partida, '%Y-%m-%d') as data, 
                 DATE_FORMAT(p.data_partida, '%H:%i') as hora
          FROM PropostasTransporte p
          JOIN Utilizadores u ON p.id_aluno = u.id_utilizador
          WHERE p.id_proposta = ? AND u.id_utilizador = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_proposta, $_SESSION['user_id']);
$stmt->execute();
$proposta = $stmt->get_result()->fetch_assoc();

if (!$proposta) {
    header("Location: as-minhas-propostas.php");
    exit();
}

// Inicializar variáveis de mensagem
$success_message = '';
$errors = [];

// Processar o formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origem = trim($_POST['origem'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    $data = trim($_POST['data'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $lotacao_maxima = intval($_POST['lotacao_maxima'] ?? 1);
    $preco = floatval($_POST['preco'] ?? 0);
    $tipo = $_POST['tipo'] ?? 'privado';
    
    // Validações
    if (empty($origem)) $errors[] = "A origem não pode estar vazia.";
    if (empty($destino)) $errors[] = "O destino não pode estar vazio.";
    if (empty($data)) $errors[] = "A data não pode estar vazia.";
    if (empty($hora)) $errors[] = "A hora não pode estar vazia.";
    if ($lotacao_maxima < 1) $errors[] = "A lotação máxima deve ser pelo menos 1.";
    if ($preco < 0) $errors[] = "O preço não pode ser negativo.";
    
    if (empty($errors)) {
        // Combinar data e hora para o formato DATETIME
        $data_partida = "$data $hora:00";
        
        // Atualizar a proposta
        $update_query = "UPDATE PropostasTransporte 
                        SET origem = ?, destino = ?, data_partida = ?, 
                            lotacao_maxima = ?, preco = ?, tipo = ?
                        WHERE id_proposta = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssdsi", $origem, $destino, $data_partida, 
                          $lotacao_maxima, $preco, $tipo, $id_proposta);
        
        if ($stmt->execute()) {
            $success_message = "Proposta atualizada com sucesso!";
            
            // Atualizar os dados locais para mostrar as alterações
            $proposta['origem'] = $origem;
            $proposta['destino'] = $destino;
            $proposta['data'] = $data;
            $proposta['hora'] = $hora;
            $proposta['lotacao_maxima'] = $lotacao_maxima;
            $proposta['preco'] = $preco;
            $proposta['tipo'] = $tipo;
        } else {
            $errors[] = "Erro ao atualizar a proposta. Por favor, tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Proposta - ESTransportado</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

  <style>
    body {
      background-color: #000;
      color: #fff;
      font-family: 'Arial', sans-serif;
    }
    
    .wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: calc(100vh - 300px); 
      padding: 50px 20px;
    }

    .proposta-container {
      background-color: #111111;
      border-radius: 15px;
      padding: 30px;
      max-width: 600px;
      width: 100%;
      text-align: center;
      box-shadow: 0 0 20px rgba(194, 255, 34, 0.1);
    }

    .proposta-container h2 {
      color: #c2ff22;
      margin-bottom: 10px;
      font-size: 1.8rem;
    }

    .proposta-container p {
      margin-bottom: 30px;
      color: #ccc;
      font-size: 1rem;
    }

    .form-group {
      text-align: left;
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #c2ff22;
      font-weight: 500;
      font-size: 0.9rem;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background-color: #2a2a2a;
      color: white;
      font-size: 1rem;
      transition: all 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      box-shadow: 0 0 0 2px #c2ff22;
    }

    .form-group input::placeholder {
      color: #aaa;
    }

    .btn-enviar {
      background-color: #c2ff22;
      color: black;
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
      transition: background-color 0.3s;
      font-size: 1rem;
      width: 100%;
    }

    .btn-enviar:hover {
      background-color: #b6f500;
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
      transition: all 0.3s;
    }
    
    .trip-type button.active {
      background: #c2ff22;
      color: black;
    }

    .alert {
      margin-bottom: 20px;
      padding: 15px;
      border-radius: 8px;
      font-size: 0.9rem;
    }
    
    .alert-danger {
      background-color: #ff4444;
      color: white;
    }
    
    .alert-success {
      background-color: #00C851;
      color: white;
    }
    
    .btn-voltar {
      display: inline-block;
      margin-top: 15px;
      color: #c2ff22;
      text-decoration: none;
      font-size: 0.9rem;
    }
    
    .btn-voltar:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <header>
    <a href="pagina-inicial.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>
  </header>

  <div class="wrapper">
    <div class="proposta-container">
      <h2>Editar Proposta de Transporte</h2>
      <p>Atualize os detalhes da sua proposta de transporte</p>
      
      <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
      <?php endif; ?>
      
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" id="editarPropostaForm">
        <div class="form-group">
          <label for="origem">De</label>
          <input type="text" id="origem" name="origem" 
                 value="<?= htmlspecialchars($proposta['origem'] ?? '') ?>" 
                 placeholder="Av. Dr. Augusto Duarte Beirão" required>
        </div>

        <div class="form-group">
          <label for="destino">Para</label>
          <input type="text" id="destino" name="destino" 
                 value="<?= htmlspecialchars($proposta['destino'] ?? '') ?>" 
                 placeholder="ESTCB" required>
        </div>

        <div class="form-group">
          <label for="data">Data</label>
          <input type="date" id="data" name="data" 
                 value="<?= htmlspecialchars($proposta['data'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="hora">Hora de Partida</label>
          <input type="time" id="hora" name="hora" 
                 value="<?= htmlspecialchars($proposta['hora'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="lotacao_maxima">Nº de Lugares Disponíveis</label>
          <input type="number" id="lotacao_maxima" name="lotacao_maxima" 
                 min="1" max="50" 
                 value="<?= htmlspecialchars($proposta['lotacao_maxima'] ?? 1) ?>" 
                 required>
        </div>

        <div class="form-group">
          <label for="preco">Preço por pessoa (€)</label>
          <input type="number" id="preco" name="preco" step="0.01" 
                 value="<?= htmlspecialchars($proposta['preco'] ?? '0.00') ?>" 
                 placeholder="2.00" required>
        </div>

        <div class="form-group">
          <label for="tipo">Tipo de Transporte</label>
          <select id="tipo" name="tipo" class="form-control" required>
            <option value="privado" <?= ($proposta['tipo'] ?? '') === 'privado' ? 'selected' : '' ?>>Privado</option>
            <option value="publico" <?= ($proposta['tipo'] ?? '') === 'publico' ? 'selected' : '' ?>>Público</option>
          </select>
        </div>

        <button type="submit" class="btn-enviar">Guardar Alterações</button>
        <a href="listar-oferta.php" class="btn-voltar">← Voltar às minhas propostas</a>
      </form>
    </div>
  </div>

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
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('editarPropostaForm');
      
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar feedback visual de carregamento
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'A guardar...';
        submitBtn.disabled = true;
        
        // Enviar dados via Fetch API
        fetch(window.location.href, {
          method: 'POST',
          body: new FormData(form),
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.text())
        .then(html => {
          // Criar um elemento temporário para parsear o HTML
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = html;
          
          // Atualizar apenas o conteúdo do container
          const newContent = tempDiv.querySelector('.proposta-container').innerHTML;
          document.querySelector('.proposta-container').innerHTML = newContent;
          
          // Reaplicar o event listener ao novo formulário
          document.getElementById('editarPropostaForm').addEventListener('submit', arguments.callee);
        })
        .catch(error => {
          console.error('Erro:', error);
          alert('Ocorreu um erro ao atualizar a proposta. Por favor, tente novamente.');
        })
        .finally(() => {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        });
      });
    });
  </script>
</body>
</html>