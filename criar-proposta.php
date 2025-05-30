<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h'; // deve definir $conn

// Padrão Post-Redirect-Get
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usa o user_id da sessão como id_aluno
    $id_aluno = (int) $_SESSION['user_id'];

    // Sanitização de inputs
    $origem       = mysqli_real_escape_string($conn, trim($_POST['origem'] ?? ''));
    $destino      = mysqli_real_escape_string($conn, trim($_POST['destino'] ?? ''));
    $data         = trim($_POST['data'] ?? '');   // YYYY-MM-DD
    $hora         = trim($_POST['hora'] ?? '');   // HH:MM
    $data_partida = "$data $hora:00";
    $lotacao      = isset($_POST['lugares']) ? (int) $_POST['lugares'] : 0;
    $preco        = isset($_POST['preco']) ? number_format((float) $_POST['preco'], 2, '.', '') : '0.00';
    $tipo         = mysqli_real_escape_string($conn, $_POST['tipo'] ?? 'publico');

    // Validação básica
    if ($origem === '' || $destino === '' || $data === '' || $hora === '' || $lotacao < 1 || $preco < 0) {
        $_SESSION['erro'] = 'Todos os campos são obrigatórios e válidos.';
    } else  {
        // Preparar e executar INSERT
        $sql = "INSERT INTO PropostasTransporte
            (id_aluno, data_partida, origem, destino, lotacao_maxima, preco, tipo)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'isssids',
                $id_aluno,
                $data_partida,
                $origem,
                $destino,
                $lotacao,
                $preco,
                $tipo
            );
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['sucesso'] = 'Proposta criada com sucesso!';
            } else {
                $_SESSION['erro'] = 'Erro ao criar a proposta: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['erro'] = 'Erro ao preparar query: ' . mysqli_error($conn);
        }
    }

    // Redireciona para limpar POST
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Recupera mensagens da sessão para exibir
$mensagem = $_SESSION['sucesso'] ?? '';
$erro     = $_SESSION['erro'] ?? '';
unset($_SESSION['sucesso'], $_SESSION['erro']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Criar Proposta | ESTransportado</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .wrapper { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: calc(100vh - 300px); padding: 50px 20px; }
    .proposta-container { background-color: #111; border-radius: 15px; padding: 30px; max-width: 600px; width: 100%; text-align: center; }
    .proposta-container h2 { color: #c2ff22; margin-bottom: 10px; }
    .proposta-container p { margin-bottom: 30px; color: #ccc; }
    .form-group { text-align: left; margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; color: #c2ff22; font-weight: 500; }
    .form-group input { width: 100%; padding: 10px; border: none; border-radius: 8px; background-color: #2a2a2a; color: #fff; }
    .form-group input::placeholder { color: #aaa; }
    .btn-enviar { background-color: #c2ff22; color: #000; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background-color 0.3s; }
    .btn-enviar:hover { background-color: #b6f500; }
    .trip-type { display: flex; gap: 10px; margin-bottom: 20px; }
    .trip-type button { flex: 1; padding: 10px; border: none; cursor: pointer; background: #333; color: #fff; border-radius: 5px; }
    .trip-type button.active { background: #c2ff22; color: #000; }
  </style>
</head>
<body>
<header class="mb-4">
  <div class="container">
    <a href="pagina-aluno.php" class="logo"><img src="imagens/logo.png" alt="ESTransportado"></a>
  </div>
</header>

<div class="wrapper">
  <div class="proposta-container">
    <h2>Criar Proposta de Transporte</h2>
    <p>Preenche os dados de uma viagem que achas que deveriamos adicionar e ainda não esteja disponível!</p>

    <?php if ($mensagem): ?>
      <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif ?>
    <?php if ($erro): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif ?>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
      <div class="form-group">
        <label for="origem">De</label>
        <input type="text" id="origem" name="origem" placeholder="Ex: Lisboa" required>
      </div>

      <div class="form-group">
        <label for="destino">Para</label>
        <input type="text" id="destino" name="destino" placeholder="Ex: Castelo Branco" required>
      </div>

      <div class="trip-type">
        <button type="button" class="active" data-tipo="publico">Público</button>
        <button type="button" data-tipo="privado">Privado</button>
      </div>
      <input type="hidden" name="tipo" id="tipo" value="publico">

      <div class="form-group">
        <label for="data">Data</label>
        <input type="date" id="data" name="data" required>
      </div>

      <div class="form-group">
        <label for="hora">Hora</label>
        <input type="time" id="hora" name="hora" required>
      </div>

      <div class="form-group">
        <label for="lugares">Nº de Lugares Disponíveis</label>
        <input type="number" id="lugares" name="lugares" min="1" max="50" placeholder="Ex: 4" required>
      </div>

      <div class="form-group">
        <label for="preco">Preço por pessoa (€)</label>
        <input type="number" id="preco" name="preco" step="0.01" placeholder="Ex: 5.00" required>
      </div>

      <button type="submit" class="btn-enviar">Submeter proposta</button>
    </form>
  </div>
</div>

<footer class="rodape mt-5">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <h3>Sobre a <span>EST</span>ransportado</h3>
        <p>A ESTransportado oferece soluções de transporte eficientes e acessíveis para estudantes, ligando-os com as suas instituições de ensino.</p>
      </div>
      <div class="col-md-4">
        <h3>Contacte-nos</h3>
        <ul class="list-unstyled">
          <li><strong>Email:</strong> info@estransportado.pt</li>
          <li><strong>Telefone:</strong> +351 123 456 789</li>
          <li><strong>Endereço:</strong> Rua da Universidade, 1000 - Castelo Branco, Portugal</li>
        </ul>
      </div>
    </div>
    <div class="text-center mt-3">
      <p>&copy; 2025 ESTransportado. Todos os direitos reservados.</p>
    </div>
  </div>
</footer>

<script>
  document.querySelectorAll('.trip-type button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.trip-type button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('tipo').value = btn.dataset.tipo;
    });
  });
</script>

</body>
</html>
