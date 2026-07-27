<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$topic_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT t.*, u.username FROM topics t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$topic_id]);
$topic = $stmt->fetch();

if (!$topic) {
    die("Sujet non trouve");
}

$stmt = $pdo->prepare("SELECT r.*, u.username FROM replies r JOIN users u ON r.user_id = u.id WHERE r.topic_id = ? ORDER BY r.created_at ASC");
$stmt->execute([$topic_id]);
$replies = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO replies (content, topic_id, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$content, $topic_id, $user_id]);
    
    header("Location: view_topic.php?id=$topic_id");
    exit();
}

if (isset($_GET['delete_topic']) && $_GET['delete_topic'] == $topic_id) {
    if ($_SESSION['user_id'] == $topic['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM topics WHERE id = ?");
        $stmt->execute([$topic_id]);
        header('Location: index.php');
        exit();
    }
}

if (isset($_GET['edit_topic']) && $_GET['edit_topic'] == $topic_id) {
    if ($_SESSION['user_id'] == $topic['user_id']) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_topic'])) {
            $new_title = $_POST['title'];
            $new_content = $_POST['content'];
            $stmt = $pdo->prepare("UPDATE topics SET title = ?, content = ? WHERE id = ?");
            $stmt->execute([$new_title, $new_content, $topic_id]);
            header("Location: view_topic.php?id=$topic_id");
            exit();
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Modifier - TalkZone</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Talk<span>Zone</span></h1>
                    <div class="user-info">
                        <span class="username">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="view_topic.php?id=<?php echo $topic_id; ?>" class="btn btn-outline">Retour</a>
                        <a href="logout.php" class="btn btn-danger">Deconnexion</a>
                    </div>
                </div>
                
                <h2>Modifier le sujet</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($topic['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contenu</label>
                        <textarea name="content" class="form-control" required><?php echo htmlspecialchars($topic['content']); ?></textarea>
                    </div>
                    <button type="submit" name="update_topic" class="btn btn-success">Mettre a jour</button>
                    <a href="view_topic.php?id=<?php echo $topic_id; ?>" class="btn btn-outline" style="margin-left:10px;">Annuler</a>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

if (isset($_GET['delete_reply'])) {
    $reply_id = $_GET['delete_reply'];
    $stmt = $pdo->prepare("SELECT user_id FROM replies WHERE id = ?");
    $stmt->execute([$reply_id]);
    $reply = $stmt->fetch();
    
    if ($_SESSION['user_id'] == $reply['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM replies WHERE id = ?");
        $stmt->execute([$reply_id]);
        header("Location: view_topic.php?id=$topic_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($topic['title']); ?> - TalkZone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Talk<span>Zone</span></h1>
            <div class="user-info">
                <span class="username">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="index.php" class="btn btn-outline">Retour au forum</a>
                <a href="logout.php" class="btn btn-danger">Deconnexion</a>
            </div>
        </div>
        
        <div class="topic-view">
            <h2><?php echo htmlspecialchars($topic['title']); ?></h2>
            <div class="topic-meta">
                Par <?php echo htmlspecialchars($topic['username']); ?> - 
                Le <?php echo date('d/m/Y H:i', strtotime($topic['created_at'])); ?>
            </div>
            <div class="topic-content">
                <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
            </div>
            
            <?php if ($_SESSION['user_id'] == $topic['user_id']): ?>
                <div class="topic-actions">
                    <a href="view_topic.php?id=<?php echo $topic_id; ?>&edit_topic=<?php echo $topic_id; ?>" class="btn btn-success btn-sm">Modifier</a>
                    <a href="view_topic.php?id=<?php echo $topic_id; ?>&delete_topic=<?php echo $topic_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce sujet?')">Supprimer</a>
                </div>
            <?php endif; ?>
        </div>
        
        <h3 style="margin: 20px 0;">Reponses (<?php echo count($replies); ?>)</h3>
        
        <?php if (empty($replies)): ?>
            <div class="empty-state" style="padding:30px;">
                <p>Aucune reponse pour le moment. Soyez le premier a repondre!</p>
            </div>
        <?php else: ?>
            <?php foreach ($replies as $reply): ?>
                <div class="reply-item">
                    <div class="reply-content">
                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                    </div>
                    <div class="reply-meta">
                        <span>Par <?php echo htmlspecialchars($reply['username']); ?> - 
                        Le <?php echo date('d/m/Y H:i', strtotime($reply['created_at'])); ?></span>
                        <?php if ($_SESSION['user_id'] == $reply['user_id']): ?>
                            <a href="view_topic.php?id=<?php echo $topic_id; ?>&delete_reply=<?php echo $reply['id']; ?>" 
                               class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette reponse?')">Supprimer</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="reply-section">
            <h3>Ajouter une reponse</h3>
            <form method="POST">
                <div class="form-group">
                    <textarea name="content" class="form-control" placeholder="Votre reponse..." required></textarea>
                </div>
                <button type="submit" name="reply" class="btn btn-primary">Repondre</button>
            </form>
        </div>
    </div>
</body>
</html>