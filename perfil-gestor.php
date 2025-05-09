<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body>
    <header>
        <a href="#.html" class="logo">
          <img src="imagens/logo.png" alt="ESTransportado">
        </a>
    
        <ul class="navbar">
          <li><a href="historico-aluno.html">Historico</a></li>
          
          </div>
    
        
    
        <a href="perfil-gestor.php" class="btn btn-primary" id="btn-entrar">Terminar sessão</a>
      </header>
   
    <main>
        
        <section class="form-section" id="perfil-aluno">
            <h2>Dados Pessoais</h2>
            <form>
                <label>Nome Completo</label>
                <div class="box-inserir">
                    Nome Completo
                </div>
                <label>Data de Nascimento</label>
                <div class="box-inserir">
                    <input type="date">
                </div>
                <label>Sexo</label>
                <div class="radio-group">
                    <button type="button" class="selected">Masculino</button>
                    <button type="button">Feminino</button>
                </div>
                <label>Nº Identificação</label>
                <div class="box-inserir">
                    Cartão de Cidadão
                </div>
                <label>Password</label>
                <div class="box-inserir">
                    Password
                </div>
                
                <button type="submit" class="btn-registo">Editar</button>
            </form>
        </section>
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
              <li><a href="historico-aluno.html">Historico</a></li>
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
        <p>&copy; 2025 ESTransportado. Todos os direitos reservados.</p>
      </div>
    </div>
  </footer>
</body>
</html>