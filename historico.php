<?php
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    // Se não estiver autenticado, redireciona para a página de login
    header('Location: logout.php');
    exit;
}

// Configurações do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'projeto_devops';

// Conexão com o MySQL
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obtém o ID do usuário atual
$id_usuario = $_SESSION['id_usuario'];

// Verifica se foi enviado um pedido para buscar o histórico de pausas para uma data específica
if (isset($_POST['data'])) {
    $data_selecionada = $_POST['data'];
    
    // Consulta SQL para selecionar o histórico de pausas do usuário atual para a data selecionada
    $sql = "SELECT tipo, data_hora FROM pausas WHERE id_usuario = $id_usuario AND DATE(data_hora) = '$data_selecionada' ORDER BY data_hora DESC";
    $result = $conn->query($sql);
    
    // Mostra o histórico de pausas para a data selecionada
    if ($result->num_rows > 0) {
        echo "<h3>Histórico de Pausas em $data_selecionada</h3>";
        echo "<table>";
        echo "<tr><th>Tipo</th><th>Data e Hora</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["tipo"] . "</td><td>" . $row["data_hora"] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Nenhum registro de pausa encontrado para esta data.";
    }
    exit; // Encerra o script após exibir o histórico para a data selecionada
}

// Verifica se foi enviado um pedido para navegar entre os meses
if (isset($_GET['mes_anterior'])) {
    // Decrementa o mês atual
    $_SESSION['mes_atual']--;
    if ($_SESSION['mes_atual'] < 1) {
        $_SESSION['mes_atual'] = 12;
        $_SESSION['ano_atual']--;
    }
} elseif (isset($_GET['mes_seguinte'])) {
    // Incrementa o mês atual
    $_SESSION['mes_atual']++;
    if ($_SESSION['mes_atual'] > 12) {
        $_SESSION['mes_atual'] = 1;
        $_SESSION['ano_atual']++;
    }
}

// Verifica se foi enviado um pedido para alterar o ano
if (isset($_GET['ano'])) {
    $_SESSION['ano_atual'] = $_GET['ano'];
}

// Define o mês e o ano atual
if (!isset($_SESSION['mes_atual'])) {
    $_SESSION['mes_atual'] = date('n');
}
if (!isset($_SESSION['ano_atual'])) {
    $_SESSION['ano_atual'] = date('Y');
}

// Número do mês e ano atual
$mes_atual = $_SESSION['mes_atual'];
$ano_atual = $_SESSION['ano_atual'];

// Início do calendário
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário e Histórico de Pausas</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .calendar-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<h1>Histórico de Pausas</h1> <br>

    <h2>Calendário</h2>
    <div class="calendar-container">
        <?php
        echo "<div>";
        echo "<a href='?mes_anterior'>&lt;</a>";
        echo date("F Y", mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
        echo "<a href='?mes_seguinte'>&gt;</a>";
        echo "</div>";

        // Adiciona um menu suspenso para selecionar o ano
        echo "<div>";
        echo "<form method='get'>";
        echo "<select name='ano' onchange='this.form.submit()'>";
        for ($ano = date('Y') - 5; $ano <= date('Y') + 5; $ano++) {
            echo "<option value='$ano' " . ($ano == $ano_atual ? 'selected' : '') . ">$ano</option>";
        }
        echo "</select>";
        echo "</form>";
        echo "</div>";

        echo "<table>";
        echo "<tr>";
        echo "<th>Seg</th>";
        echo "<th>Ter</th>";
        echo "<th>Qua</th>";
        echo "<th>Qui</th>";
        echo "<th>Sex</th>";
        echo "<th>Sáb</th>";
        echo "<th>Dom</th>";
        echo "</tr>";

        // Total de dias no mês e o dia da semana do primeiro dia do mês
        $total_dias = date('t', mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
        $primeiro_dia = date('N', mktime(0, 0, 0, $mes_atual, 1, $ano_atual));

        // Contador de dias
        $contador_dias = 1;

        // Loop para preencher o calendário
        for ($i = 1; $i <= 6; $i++) {
            echo "<tr>";
            for ($j = 1; $j <= 7; $j++) {
                if ($contador_dias <= $total_dias && ($i > 1 || $j >= $primeiro_dia)) {
                    $data = "$ano_atual-$mes_atual-" . str_pad($contador_dias, 2, "0", STR_PAD_LEFT);
                    echo "<td><a href='#' onclick=\"buscarPausas('$data')\">$contador_dias</a></td>";
                    $contador_dias++;
                } else {
                    echo "<td></td>";
                }
            }
            echo "</tr>";
        }

        echo "</table>";
        ?>
    </div>
    
    <div id="historico"></div> <!-- Aqui será exibido o histórico de pausas -->
    
    <br><br><a href='pausas.php'>Retornar ao Menu de Pausas</a>

    <script>
        function buscarPausas(data) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("historico").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("POST", "historico.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("data=" + data);
        }
    </script>
</body>
</html>

<?php
// Fecha a conexão
$conn->close();
?>
