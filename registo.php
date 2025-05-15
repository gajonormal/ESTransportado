<?php
// registo.php
session_start();
require_once 'basedados/basedados.h'; // Arquivo de conexão com o banco de dados

$erros = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar e sanitizar dados do formulário
    $nome_completo = trim($_POST['nome_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validações
    if (empty($nome_completo)) {
        $erros[] = "O nome completo é obrigatório.";
    }

    if (empty($email)) {
        $erros[] = "O e-mail é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O e-mail fornecido não é válido.";
    } elseif (!preg_match('/@(ipcb\.pt|ipcbcampus\.pt)$/', $email)) {
        $erros[] = "Apenas e-mails institucionais (@ipcb.pt ou @ipcbcampus.pt) são permitidos.";
    } else {
        // Verificar se e-mail já existe
        $stmt = $conn->prepare("SELECT id_utilizador FROM Utilizadores WHERE email_institucional = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $erros[] = "Este e-mail já está registado.";
        }
        $stmt->close();
    }

    if (empty($data_nascimento)) {
        $erros[] = "A data de nascimento é obrigatória.";
    } else {
        $data_nasc = new DateTime($data_nascimento);
        $hoje = new DateTime();
        $idade = $hoje->diff($data_nasc)->y;
        if ($idade < 16) {
            $erros[] = "É necessário ter pelo menos 16 anos para se registar.";
        }
    }

    if (empty($sexo) || !in_array($sexo, ['masculino', 'feminino'])) {
        $erros[] = "O sexo é obrigatório.";
    }

    if (empty($password)) {
        $erros[] = "A password é obrigatória.";
    } elseif (strlen($password) < 8) {
        $erros[] = "A password deve ter pelo menos 8 caracteres.";
    } elseif ($password !== $confirm_password) {
        $erros[] = "As passwords não coincidem.";
    }

    // Se não houver erros, proceder com o registo
    if (empty($erros)) {
        // Gerar o próximo número de matrícula automaticamente
        $query = "SELECT numero_matricula FROM Utilizadores ORDER BY id_utilizador DESC LIMIT 1";
        $resultado = $conn->query($query);
        
        if ($resultado && $resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $ultimo_numero = $row['numero_matricula'];
            
            // Extrair o número da parte alfanumérica (assumindo formato A12345)
            preg_match('/A(\d+)/', $ultimo_numero, $matches);
            $numero_atual = intval($matches[1]);
            $proximo_numero = $numero_atual + 1;
            $numero_matricula = 'A' . $proximo_numero;
        } else {
            // Se não houver registros ou ocorrer um erro, começar com A10000
            $numero_matricula = 'A10000';
        }
        
        // Hash da password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Determinar tipo de utilizador com base no e-mail
        $tipo = 'aluno'; // Padrão para alunos
        if (strpos($email, '@ipcb.pt') !== false) {
            $tipo = 'aluno'; // Mudar conforme necessário
        }

        // Inserir no banco de dados
        $stmt = $conn->prepare("INSERT INTO Utilizadores (
            email_institucional, 
            password_hash, 
            nome_completo, 
            data_nascimento, 
            numero_matricula, 
            sexo, 
            tipo, 
            conta_ativa
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $conta_ativa = 0; // Definir como false/inativo inicialmente
        $stmt->bind_param(
            "sssssssi", 
            $email, 
            $password_hash, 
            $nome_completo, 
            $data_nascimento, 
            $numero_matricula, 
            $sexo, 
            $tipo, 
            $conta_ativa
        );

        if ($stmt->execute()) {
            // Fechar conexão antes do redirecionamento
            $stmt->close();
            $conn->close();
            
            // Armazenar mensagem de sucesso na sessão
            $_SESSION['registro_sucesso'] = true;
            
            // Redirecionar para a mesma página com método GET
            header("Location: registo.php");
            exit();
        } else {
            $erros[] = "Ocorreu um erro ao registar. Por favor, tente novamente.";
            $stmt->close();
        }
    }
    
    // Se houver erros, armazená-los na sessão para exibição após redirecionamento
    if (!empty($erros)) {
        $_SESSION['registro_erros'] = $erros;
        $_SESSION['registro_dados'] = $_POST;
        
        // Fechar conexão antes do redirecionamento
        $conn->close();
        
        // Redirecionar para a mesma página com método GET
        header("Location: registo.php");
        exit();
    }
}

// Verificar se há mensagens na sessão para exibir (após redirecionamento)
if (isset($_SESSION['registro_sucesso'])) {
    $sucesso = true;
    unset($_SESSION['registro_sucesso']);
}

if (isset($_SESSION['registro_erros'])) {
    $erros = $_SESSION['registro_erros'];
    unset($_SESSION['registro_erros']);
    
    // Recuperar dados do formulário para preenchimento automático
    if (isset($_SESSION['registro_dados'])) {
        $_POST = $_SESSION['registro_dados'];
        unset($_SESSION['registro_dados']);
    }
}

// Se não for um redirecionamento PRG, fechar a conexão normalmente
if (!isset($conn)) {
    require_once 'basedados/basedados.h';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado - Registo</title>
  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- CSS Personalizado -->
     <link rel="stylesheet" href="style.css">
  
  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  
  <script>
    function selectSexo(value) {
      // Atualizar o input hidden
      document.getElementById('sexo_input').value = value;
      
      // Remover a classe 'selected' de todos os botões
      document.querySelectorAll('.sexo-btn').forEach(btn => {
        btn.classList.remove('selected');
      });
      
      // Adicionar a classe 'selected' ao botão clicado
      document.getElementById(value).classList.add('selected');
    }
  </script>
</head>
<body>
  <header>
    <a href="pagina-inicial.php" class="logo">
      <img src="imagens/logo.png" alt="ESTransportado">
    </a>
  </header>
  
  <banner class="banner-registo">
    <img src="imagens/imagembanner.jpg">
    <h1>Regista-te para poderes começar a realizar inúmeras viagens</h1>
  </banner> 
  
  <main>
    <section class="benefits">
      <ul>
        <li>&#10004; Controla as viagens que realizas no teu perfil</li>
        <li>&#10004; Recebe informação personalizada e os teus bilhetes por e-mail</li>
        <li>&#10004; Cria novas propostas de transporte</li>
        <li>&#10004; Avalia viagens para tornar o nosso serviço mais seguro</li>
      </ul>
    </section>
    
    <section class="form-section">
      <h2>Dados Pessoais</h2>
      
      <?php if ($sucesso): ?>
        <div class="message-box success-message">
          <i class='bx bx-check-circle' style="font-size: 24px; vertical-align: middle;"></i>
          Registo realizado com sucesso! A sua conta será ativada após verificação.
        </div>
      <?php elseif (!empty($erros)): ?>
        <div class="message-box error-message">
          <i class='bx bx-error-circle' style="font-size: 24px; vertical-align: middle;"></i>
          Por favor, corrija os seguintes erros:
          <ul style="text-align: left; margin-top: 10px; padding-left: 20px;">
            <?php foreach ($erros as $erro): ?>
              <li><?php echo htmlspecialchars($erro); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="registo.php">
        <label>Nome Completo*</label>
        <div class="box-inserir">
          <input type="text" name="nome_completo" placeholder="Nome completo" required
                 value="<?php echo isset($_POST['nome_completo']) ? htmlspecialchars($_POST['nome_completo']) : ''; ?>">
        </div>
        
        <label>E-mail Institucional*</label>
        <div class="box-inserir">
          <input type="email" name="email" placeholder="E-mail institucional (@ipcb.pt ou @ipcbcampus.pt)" required
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        
        <label>Data de Nascimento*</label>
        <div class="box-inserir">
          <input type="date" name="data_nascimento" required
                 value="<?php echo isset($_POST['data_nascimento']) ? htmlspecialchars($_POST['data_nascimento']) : ''; ?>">
        </div>
        
        <label>Sexo*</label>
        <div class="radio-group">
          <button type="button" id="masculino" class="sexo-btn <?php echo (isset($_POST['sexo']) && $_POST['sexo'] === 'masculino') ? 'selected' : ''; ?>" 
                  onclick="selectSexo('masculino')">
            Masculino
          </button>
          <button type="button" id="feminino" class="sexo-btn <?php echo (isset($_POST['sexo']) && $_POST['sexo'] === 'feminino') ? 'selected' : ''; ?>"
                  onclick="selectSexo('feminino')">
            Feminino
          </button>
          <input type="hidden" name="sexo" id="sexo_input" value="<?php echo isset($_POST['sexo']) ? htmlspecialchars($_POST['sexo']) : ''; ?>" required>
        </div>
        
        <label>Password*</label>
        <div class="box-inserir">
          <input type="password" name="password" placeholder="Password (mínimo 8 caracteres)" required>
        </div>
        
        <label>Confirmar password*</label>
        <div class="box-inserir">
          <input type="password" name="confirm_password" placeholder="Confirmar password" required>
        </div>
        
        <button type="submit" class="btn-registo">Registar</button>
      </form>
    </section>
  </main>
</body>
</html>