<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        header('Location: login.php?success=1');
        exit();
    } catch(PDOException $e) {
        $error = "Ce nom d'utilisateur existe deja";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inscription - TalkZone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-box">
        <h2>Talk<span>Zone</span></h2>
        <h3 style="text-align:center; color:#666; margin-bottom:20px;">Inscription</h3>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        <div class="links">
            Deja inscrit? <a href="login.php">Connectez-vous</a>
        </div>
    </div>
</body>
</html>