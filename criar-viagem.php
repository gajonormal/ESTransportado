<?php
session_start();
// Apenas admin/gestor
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['gestor'])) {
    header('Location: pagina-login.php');
    exit();
}

// Conexão direta ao MySQL
$mysqli = new mysqli('localhost','root','','Estransportado');
if ($mysqli->connect_error) die('Erro de ligação à BD: '.$mysqli->connect_error);
$mysqli->set_charset('utf8mb4');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['origem','destino','data_partida','data_chegada','preco','lotacao_maxima','tipo'];
    foreach ($fields as $f) {
        if (!isset($_POST[$f]) || trim($_POST[$f]) === '') {
            $errors[] = ucfirst(str_replace('_',' ',$f)).' é obrigatório.';
        }
    }
    if (empty($errors)) {
        $dp = strtotime($_POST['data_partida']);
        $dc = strtotime($_POST['data_chegada']);
        if ($dp === false || $dc === false) {
            $errors[] = 'Formato de data/hora inválido.';
        } elseif ($dc <= $dp) {
            $errors[] = 'A data de chegada deve ser posterior à de partida.';
        }
    }
    if (empty($errors)) {
        $stmt = $mysqli->prepare(
            "INSERT INTO Viagens 
             (origem, destino, data_partida, data_chegada, preco, lotacao_maxima, tipo) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'ssssdis',
            $_POST['origem'],
            $_POST['destino'],
            $_POST['data_partida'],
            $_POST['data_chegada'],
            $_POST['preco'],
            $_POST['lotacao_maxima'],
            $_POST['tipo']
        );
        if ($stmt->execute()) {
            header('Location: gerir-viagens.php');
            exit();
        } else {
            $errors[] = 'Erro ao inserir viagem: '.$stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ESTransportado - Criar Viagem</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <style>
    :root {
      --cor-primaria: #c6f31c;
      --cor-fundo: #1e1e1e;
      --cor-texto: #fff;
    }
    body {
      background: var(--cor-fundo);
      color: var(--cor-texto);
      font-family: 'Segoe UI', sans-serif;
    }
    header {
      background: var(--cor-primaria);
      padding: 15px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    header .logo img {
      height: 50px;
    }
    .navbar {
      list-style: none;
      display: flex;
      gap: 30px;
      margin: 0;
      padding: 0;
    }
    .navbar li {
      position: relative;
    }
    .navbar li a {
      color: #000;
      text-decoration: none;
      font-weight: 600;
      padding: 5px 10px;
      transition: background 0.2s, color 0.2s;
      border-radius: 5px;
    }
    .navbar li a:hover {
      background: #000;
      color: var(--cor-primaria);
    }
    #btn-entrar {
      background: #000;
      color: var(--cor-primaria);
      padding: 10px 25px;
      border-radius: 30px;
      font-weight: 600;
      text-decoration: none;
    }
    #btn-entrar:hover {
      background: #222;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: rgba(0,0,0,0.12);
      padding: 30px;
      border-radius: 15px;
    }
    h2 {
      color: var(--cor-primaria);
      text-align: center;
      margin-bottom: 30px;
      font-weight: 700;
    }
    .form-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .form-group {
      flex: 1;
      min-width: 200px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--cor-primaria);
      font-weight: 600;
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px;
      background: #2a2a2a;
      border: 1px solid #333;
      border-radius: 8px;
      color: #fff;
    }
    .btn-registo {
      background: var(--cor-primaria);
      color: #000;
      padding: 12px 25px;
      border-radius: 30px;
      font-weight: 600;
      border: none;
    }
    .btn-registo:hover {
      background: #a8d810;
    }
    .btn-cancelar {
      background: #333;
      color: #fff;
      padding: 12px 25px;
      border-radius: 30px;
      margin-left: 10px;
    }
    .btn-cancelar:hover {
      background: #444;
    }
    footer.rodape {
      background: #111;
      padding: 50px 0 20px;
      color: #aaa;
      text-align: center;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <header>
    <a href="pagina-gestor.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>
    <ul class="navbar">
      <li><a href="gerir-viagens.php">Gerir Viagens</a></li>
      <li><a href="pagina-gerir-Utilizadores.php">Gerir Utilizadores</a></li>
      <li><a href="perfil.php" id="btn-entrar">Perfil</a></li>
    </ul>
  </header>

  <div class="container">
    <h2>Criar Nova Viagem</h2>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?=htmlspecialchars($e)?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-grid">
        <div class="form-group">
          <label for="origem">Origem</label>
          <input type="text" id="origem" name="origem" value="<?=htmlspecialchars($_POST['origem']??'')?>">
        </div>
        <div class="form-group">
          <label for="destino">Destino</label>
          <input type="text" id="destino" name="destino" value="<?=htmlspecialchars($_POST['destino']??'')?>">
        </div>
        <div class="form-group">
          <label for="data_partida">Data de Partida</label>
          <input type="datetime-local" id="data_partida" name="data_partida" value="<?=htmlspecialchars($_POST['data_partida']??'')?>">
        </div>
        <div class="form-group">
          <label for="data_chegada">Data de Chegada</label>
          <input type="datetime-local" id="data_chegada" name="data_chegada" value="<?=htmlspecialchars($_POST['data_chegada']??'')?>">
        </div>
        <div class="form-group">
          <label for="preco">Preço (€)</label>
          <input type="number" step="0.01" id="preco" name="preco" value="<?=htmlspecialchars($_POST['preco']??'')?>">
        </div>
        <div class="form-group">
          <label for="lotacao_maxima">Lotação Máxima</label>
          <input type="number" id="lotacao_maxima" name="lotacao_maxima" value="<?=htmlspecialchars($_POST['lotacao_maxima']??'')?>">
        </div>
        <div class="form-group">
          <label for="tipo">Tipo</label>
          <select id="tipo" name="tipo">
            <option value="">Seleccione...</option>
            <option value="publico" <?=($_POST['tipo']??'')==='publico'?'selected':''?>>Público</option>
            <option value="privado" <?=($_POST['tipo']??'')==='privado'?'selected':''?>>Privado</option>
          </select>
        </div>
      </div>

      <div class="text-center" style="margin-top:30px;">
        <button type="submit" class="btn-registo">Guardar Viagem</button>
        <a href="gerir-viagens.php" class="btn-cancelar">Cancelar</a>
      </div>
    </form>
  </div>

  <footer class="rodape">
    &copy; 2025 ESTransportado. Todos os direitos reservados.
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
