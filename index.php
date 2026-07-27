<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query("
    SELECT t.*, u.username, 
           (SELECT COUNT(*) FROM replies WHERE topic_id = t.id) as reply_count
    FROM topics t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$topics = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forum</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Talk<span>Zone</span></h1>
            <div class="user-info">
                <span class="username">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="create_topic.php" class="btn btn-primary">Nouveau sujet</a>
                <a href="logout.php" class="btn btn-danger">Deconnexion</a>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px;">Sujets du forum</h2>
        
        <?php if (empty($topics)): ?>
            <div class="empty-state">
                <h3>Aucun sujet pour le moment</h3>
                <p>Soyez le premier a creer un sujet dans le forum</p>
                <a href="create_topic.php" class="btn btn-primary">Creer un sujet</a>
            </div>
        <?php else: ?>
            <div class="topic-list">
                <?php foreach ($topics as $topic): ?>
                    <div class="topic-item">
                        <h3><a href="view_topic.php?id=<?php echo $topic['id']; ?>">
                            <?php echo htmlspecialchars($topic['title']); ?>
                        </a></h3>
                        <div class="topic-excerpt">
                            <?php echo htmlspecialchars(substr($topic['content'], 0, 150)); ?>...
                        </div>
                        <div class="topic-meta">
                            <span>Par <?php echo htmlspecialchars($topic['username']); ?></span>
                            <span>Reponses: <?php echo $topic['reply_count']; ?></span>
                            <span>Le <?php echo date('d/m/Y H:i', strtotime($topic['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>