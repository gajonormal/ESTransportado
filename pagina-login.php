<?php
session_start();

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    // Redirecionar de acordo com o tipo de usuário
    switch ($_SESSION['user_type']) {
        case 'admin':
            header("Location: pagina-admin.php");
            break;
        case 'gestor':
            header("Location: pagina-gestor.php");
            break;
        case 'aluno':
            header("Location: pagina-aluno.php");
            break;
        default:
            header("Location: pagina-inicial.php");
    }
    exit();
}

// Verificar se há mensagens de erro/sucesso
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);

$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - ESTransportado</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body class="pagina-login">
  <header>
    <a href="pagina-inicial.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>
  </header>

  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="box-login">
      <h2>Login</h2>
      
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>
      
      <form action="login.php" method="POST">
        <div class="box-inserir">
          <i class="bx bx-envelope"></i>
          <input type="email" name="email" placeholder="Introduza o seu email institucional (@ipcb.pt)" required>
        </div>
        <p class="email-info">Utiliza apenas emails institucionais (@ipcb.pt ou @ipcbcampus.pt).</p>
        <div class="box-inserir">
          <i class="bx bx-lock"></i>
          <input type="password" name="password" placeholder="Introduza a sua password" required>
        </div>

        <div class="opcoes">
          <a href="esquece-password.php">Esqueceu-se da password?</a>
        </div>
        <button type="submit" class="btn btn-primary" id="btn-login">Entrar</button>

        <p class="link-registo">Ainda não tens conta? <a href="registo.php">Regista-te agora!</a></p>
      </form>
    </div>
  </div>
</body>
</html>