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
        <a href="pagina-admin.php" class="logo">
          <img src="imagens/logo.png" alt="ESTransportado">
        </a>
      </header>
   
    <main>
        
        <section class="form-section" id="perfil-aluno">
            <h2>Gestor</h2>
            <form>
                <label>Nome Completo</label>
                <div class="box-inserir">
                    António Manuel da Silva
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
                    255 333 829
                </div>
                
                
                <div class="d-flex gap-2 mt-3">
                    <a class="btn-registo"> Remover Gestor</a>
                   
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