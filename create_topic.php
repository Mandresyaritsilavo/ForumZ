<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO topics (title, content, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $user_id]);
    
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nouveau sujet - TalkZone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Talk<span>Zone</span></h1>
            <div class="user-info">
                <span class="username">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="index.php" class="btn btn-outline">Retour</a>
                <a href="logout.php" class="btn btn-danger">Deconnexion</a>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px;">Creer un nouveau sujet</h2>
        
        <form method="POST">
            <div class="form-group">
                <label>Titre du sujet</label>
                <input type="text" name="title" class="form-control" placeholder="Donnez un titre a votre sujet" required>
            </div>
            <div class="form-group">
                <label>Contenu</label>
                <textarea name="content" class="form-control" placeholder="Ecrivez votre message ici..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Publier le sujet</button>
            <a href="index.php" class="btn btn-outline" style="margin-left:10px;">Annuler</a>
        </form>
    </div>
</body>
</html>