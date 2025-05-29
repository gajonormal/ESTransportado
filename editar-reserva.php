<?php
session_start();
require_once 'config.php';

// Verifica se o utilizador está logado
if (!isset($_SESSION['id_utilizador'])) {
    header("Location: login.php");
    exit();
}

$id_utilizador = $_SESSION['id_utilizador'];

// Buscar informações do utilizador
$query_utilizador = "SELECT * FROM Utilizadores WHERE id_utilizador = ?";
$stmt_utilizador = $conn->prepare($query_utilizador);
$stmt_utilizador->bind_param("i", $id_utilizador);
$stmt_utilizador->execute();
$result_utilizador = $stmt_utilizador->get_result();
$utilizador = $result_utilizador->fetch_assoc();

// Buscar reservas
$query_reservas = "SELECT r.*, v.origem, v.destino, v.data_partida, v.data_chegada 
                   FROM Reservas r
                   JOIN Viagens v ON r.id_viagem = v.id_viagem
                   JOIN Passageiros p ON r.id_passageiro = p.id_passageiro
                   WHERE p.id_utilizador = ?
                   ORDER BY v.data_partida DESC";
$stmt_reservas = $conn->prepare($query_reservas);
$stmt_reservas->bind_param("i", $id_utilizador);
$stmt_reservas->execute();
$result_reservas = $stmt_reservas->get_result();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Minhas Reservas - ESTransportado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .container { max-width: 700px; padding: 20px; border-radius: 20px; }
    .box { background: #111111; padding: 15px; margin-bottom: 15px; border-radius: 15px; color: white; }
    .box h5 { background: #bdf13b; font-weight: bold; color:#111111; padding: 10px; border-radius: 20px; display: inline-block; }
    .btn-add, .btn-editar, .btn-cancelar { border: none; font-weight: bold; border-radius: 10px; padding: 8px 16px; cursor: pointer; }
    .btn-add { background: #bdf13b; color: #111; }
    .btn-editar { background: #bdf13b; color: #2c2c2c; }
    .btn-cancelar { background: #ff4d4d; color: white; }
    .btn-editar:hover, .btn-cancelar:hover, .btn-add:hover { opacity: 0.9; transition: 0.2s; }
    .reserva-item { background: #2c2c2c; padding: 15px; margin-bottom: 10px; border-radius: 10px; }
    .reserva-actions { display: flex; gap: 10px; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: bold; }
    .alert-success { background: #d4edda; color: #155724; }
    .alert-error { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
  <header class="p-3 text-center">
    <a href="pagina_inicial.php"><img src="imagens/logo.png" alt="Logo" height="50"></a>
  </header>

  <div class="container">
    <?php if (isset($_SESSION['sucesso'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['sucesso']); unset($_SESSION['sucesso']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['erro']); unset($_SESSION['erro']); ?></div>
    <?php endif; ?>

    <div class="box">
      <h5>As tuas reservas</h5>

      <?php if ($result_reservas->num_rows > 0): ?>
        <?php while ($reserva = $result_reservas->fetch_assoc()): ?>
          <div class="reserva-item" id="reserva-<?php echo $reserva['id_reserva']; ?>">
            <p><strong>Viagem:</strong> <?php echo htmlspecialchars($reserva['origem']); ?> → <?php echo htmlspecialchars($reserva['destino']); ?></p>
            <p><strong>Partida:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['data_partida'])); ?></p>
            <p><strong>Chegada:</strong> <?php echo date('H:i', strtotime($reserva['data_chegada'])); ?></p>
            <p><strong>Lugar:</strong> <?php echo htmlspecialchars($reserva['lugar'] ?? 'Não atribuído'); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($reserva['estado']); ?></p>
            <div class="reserva-actions">
              <a href="editar_reserva.php?id=<?php echo $reserva['id_reserva']; ?>" class="btn-editar">Editar</a>
              <button class="btn-cancelar" data-id="<?php echo $reserva['id_reserva']; ?>">Cancelar</button>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>Não tens reservas ativas.</p>
        <a href="procurar_viagens.php" class="btn-add">Procurar Viagens</a>
      <?php endif; ?>
    </div>
  </div>

  <?php
  $stmt_utilizador->close();
  $stmt_reservas->close();
  $conn->close();
  ?>

  <!-- jQuery para AJAX -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  $(document).ready(function () {
    $('.btn-cancelar').click(function () {
      const id = $(this).data('id');
      if (confirm("Cancelar a reserva ID " + id + "?")) {
        $.post("cancelar_reserva.php", { id_reserva: id })
          .done(function (res) {
            if (res === "sucesso") {
              $('#reserva-' + id).fadeOut(300, function () { $(this).remove(); });
              alert("Reserva cancelada com sucesso.");
            } else {
              alert("Erro: " + res);
            }
          })
          .fail(function (xhr) {
            alert("Erro: " + xhr.responseText);
          });
      }
    });
  });
  </script>
</body>
</html>
