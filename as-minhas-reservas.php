<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: pagina-login.php");
  exit();
}

require_once 'basedados/basedados.h';

$user_id = $_SESSION['user_id'];

// Obter filtros únicos (origens e destinos)
$filtros_query = "SELECT DISTINCT origem, destino FROM Viagens";
$filtros_result = $conn->query($filtros_query);
$filtros = $filtros_result->fetch_all(MYSQLI_ASSOC);

// Aplicar filtro se existir
$filtro_origem = $_GET['origem'] ?? null;
$filtro_destino = $_GET['destino'] ?? null;

$query = "SELECT r.*, v.origem, v.destino, 
                 TIME_FORMAT(v.data_partida, '%H:%i') as hora_partida, 
                 TIME_FORMAT(v.data_chegada, '%H:%i') as hora_chegada, 
                 TIME_FORMAT(TIMEDIFF(v.data_chegada, v.data_partida), '%H:%i') as duracao
          FROM Reservas r
          JOIN Viagens v ON r.id_viagem = v.id_viagem
          JOIN Passageiros p ON r.id_passageiro = p.id_passageiro
          WHERE p.id_utilizador = ? AND r.estado != 'cancelado'";

$params = [$user_id];
$types = "i";

if ($filtro_origem) {
  $query .= " AND v.origem = ?";
  $types .= "s";
  $params[] = $filtro_origem;
}
if ($filtro_destino) {
  $query .= " AND v.destino = ?";
  $types .= "s";
  $params[] = $filtro_destino;
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$reservas = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <style>
    .btn-cancelar-reserva {
    margin-top: 10px;
    background: #c2ff22;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    color: black;
    padding: 8px 16px;
    cursor: pointer;
}

.btn-cancelar-reserva:hover {
    background: #c2ff22;
    color: black;
}
    .container {
      max-width: 800px;
      margin: 30px auto;
      background: #111;
      padding: 20px;
      border-radius: 10px;
    }
    .container h1 {
      text-align: center;
      color: #fff;
    }
    .viagem {
      background: #222;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 15px;
      color: #fff;
    }
    .viagem strong {
      color: #c2ff22;
    }
    .viagem a.btn {
      margin-top: 10px;
      background: #c2ff22;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      color: #000;
    }
    .form-select {
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <header>
    <a href="pagina-aluno.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>
  </header>

  <div class="container">
    <h1>As minhas reservas</h1>
    <form method="GET" class="mb-3">
      <div class="row">
        <div class="col-md-6">
          <select name="origem" class="form-select">
            <option value="">Filtrar por origem</option>
            <?php foreach ($filtros as $f): if (!empty($f['origem'])): ?>
              <option value="<?= htmlspecialchars($f['origem']) ?>" <?= $filtro_origem === $f['origem'] ? 'selected' : '' ?>><?= htmlspecialchars($f['origem']) ?></option>
            <?php endif; endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <select name="destino" class="form-select">
            <option value="">Filtrar por destino</option>
            <?php foreach ($filtros as $f): if (!empty($f['destino'])): ?>
              <option value="<?= htmlspecialchars($f['destino']) ?>" <?= $filtro_destino === $f['destino'] ? 'selected' : '' ?>><?= htmlspecialchars($f['destino']) ?></option>
            <?php endif; endforeach; ?>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-2">Aplicar Filtros</button>
    </form>
  </div>

  <div class="container">
    <div class="results-section">
      <?php if (empty($reservas)): ?>
        <div class="text-white">Não existem reservas ativas.</div>
      <?php else: ?>
        <?php foreach ($reservas as $r): ?>
          <div class="viagem">
            <p><strong><?= $r['hora_partida'] ?></strong> - <?= htmlspecialchars($r['origem']) ?> → <strong><?= $r['hora_chegada'] ?></strong> - <?= htmlspecialchars($r['destino']) ?></p>
            <p>Duração: <?= $r['duracao'] ?>h | Preço: €<?= number_format($r['preco_total'], 2) ?></p>
            <a href="editar-reserva.php?id=<?= $r['id_reserva'] ?>" class="btn">Editar</a>
            <button class="btn btn-cancelar-reserva" data-id-reserva="<?= $r['id_reserva'] ?>" style="background-color: #c2ff22; color: black ;">Cancelar reserva</button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
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
    // Adicionar evento de clique aos botões de cancelar
    document.querySelectorAll('.btn-cancelar-reserva').forEach(button => {
        button.addEventListener('click', function() {
            const idReserva = this.getAttribute('data-id-reserva');
            if (!idReserva) return;
            
            if (!confirm('Tem certeza que deseja cancelar esta reserva?')) {
                return;
            }
            
            // Mostrar loader
            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cancelando...';
            this.disabled = true;
            
            // Fazer a chamada AJAX
            fetch('cancelar-reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_reserva=' + encodeURIComponent(idReserva)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Encontrar e remover o elemento da reserva
                    const reservaItem = this.closest('.viagem');
                    if (reservaItem) {
                        reservaItem.remove();
                    }
                    
                    // Mostrar mensagem de sucesso (opcional)
                    alert('Reserva cancelada com sucesso!');
                } else {
                    alert('Erro ao cancelar reserva: ' + (data.error || 'Erro desconhecido'));
                    // Restaurar botão
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocorreu um erro ao comunicar com o servidor');
                // Restaurar botão
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });
});
</script>
</body>
</html>
