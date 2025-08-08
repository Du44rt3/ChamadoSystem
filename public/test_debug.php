<?php
// Teste ultra simples
echo "<h1>TESTE DEBUG SIMPLES</h1>";
echo "<h3>Método: " . $_SERVER['REQUEST_METHOD'] . "</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST DATA:</h3>";
    echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
    
    echo "<h3>FILES:</h3>";
    echo "<pre>";
    var_dump($_FILES);
    echo "</pre>";
    
    echo "<h3>REQUEST URI:</h3>";
    echo "<pre>" . $_SERVER['REQUEST_URI'] . "</pre>";
    
    echo "<h3>RAW POST DATA:</h3>";
    echo "<pre>" . file_get_contents('php://input') . "</pre>";
    
} else {
    echo "<p>Não é POST - teste não executado</p>";
    echo "<h3>GET DATA:</h3>";
    echo "<pre>";
    var_dump($_GET);
    echo "</pre>";
}

echo "<hr>";
echo "<p>Voltar para <a href='view.php?id=256'>view.php?id=256</a></p>";
?>
