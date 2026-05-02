<?php
/**
 * Dashboard Admin - E-Graphisme
 */
require_once 'config.php';
require_login();

// Vérifier l'expiration de session (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Récupérer les messages (simulés - à remplacer par une vraie base de données)
$messages_file = __DIR__ . '/../php/submissions.log';
$messages = [];
if (file_exists($messages_file)) {
    $lines = file($messages_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach (array_reverse($lines) as $line) {
        $messages[] = $line;
    }
}

// Statistiques
$stats = [
    'total_messages' => count($messages),
    'today_messages' => 0,
    'this_week' => 0,
];

$today = date('Y-m-d');
$this_week_start = date('Y-m-d', strtotime('-7 days'));

foreach ($messages as $msg) {
    if (strpos($msg, $today) !== false) {
        $stats['today_messages']++;
    }
    if (strpos($msg, $this_week_start) !== false || strtotime(substr($msg, 0, 10)) >= strtotime($this_week_start)) {
        $stats['this_week']++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Graphisme</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #f472b6;
            --dark-color: #1e1e2f;
            --light-color: #f8fafc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-color);
            color: var(--dark-color);
        }
        
        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: var(--dark-color);
            color: white;
            padding: 30px 20px;
            z-index: 100;
        }
        
        .admin-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-logo span {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .admin-menu {
            list-style: none;
        }
        
        .admin-menu li {
            margin-bottom: 10px;
        }
        
        .admin-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .admin-menu a:hover, .admin-menu a.active {
            background: var(--primary-color);
            color: white;
        }
        
        .admin-menu i {
            width: 20px;
        }
        
        .logout-btn {
            position: absolute;
            bottom: 30px;
            left: 20px;
            right: 20px;
        }
        
        .logout-btn a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .logout-btn a:hover {
            background: #ef4444;
            color: white;
        }
        
        /* Main Content */
        .admin-main {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-user .avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        
        .stat-card .stat-icon.blue {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }
        
        .stat-card .stat-icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .stat-card .stat-icon.pink {
            background: rgba(244, 114, 182, 0.1);
            color: var(--secondary-color);
        }
        
        .stat-card .stat-icon.orange {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        /* Table */
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .content-card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-card-header h2 {
            font-size: 1.2rem;
        }
        
        .content-card-body {
            padding: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px 25px;
            text-align: left;
        }
        
        th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #64748b;
        }
        
        tr:not(:last-child) td {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .message-content {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-new {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }
        
        .badge-read {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            
            .admin-logo {
                font-size: 1.2rem;
                text-align: center;
            }
            
            .admin-logo span {
                display: none;
            }
            
            .admin-menu a span {
                display: none;
            }
            
            .admin-menu a {
                justify-content: center;
                padding: 12px;
            }
            
            .admin-main {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-card-header {
                flex-direction: column;
                gap: 15px;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 10px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .admin-main {
                padding: 15px;
            }
            
            .admin-header h1 {
                font-size: 1.4rem;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-card h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-logo">E-<span>Graphisme</span></div>
        
        <ul class="admin-menu">
            <li><a href="index.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="#messages"><i class="fas fa-envelope"></i> <span>Messages</span></a></li>
            <li><a href="#blog"><i class="fas fa-blog"></i> <span>Blog</span></a></li>
            <li><a href="#portfolio"><i class="fas fa-images"></i> <span>Portfolio</span></a></li>
            <li><a href="#settings"><i class="fas fa-cog"></i> <span>Paramètres</span></a></li>
        </ul>
        
        <div class="logout-btn">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="admin-user">
                <div class="avatar"><?php echo strtoupper($_SESSION['admin_user'][0]); ?></div>
                <div>
                    <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong>
                    <p style="font-size: 0.8rem; color: #64748b;">Administrateur</p>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-envelope"></i></div>
                <h3><?php echo $stats['total_messages']; ?></h3>
                <p>Total des messages</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
                <h3><?php echo $stats['today_messages']; ?></h3>
                <p>Messages aujourd'hui</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon pink"><i class="fas fa-calendar-week"></i></div>
                <h3><?php echo $stats['this_week']; ?></h3>
                <p>Cette semaine</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
                <h3>85%</h3>
                <p>Progression</p>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="content-card" id="messages">
            <div class="content-card-header">
                <h2><i class="fas fa-envelope"></i> Messages récents</h2>
                <button class="btn" style="padding: 8px 20px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer;">
                    <i class="fas fa-download"></i> Exporter
                </button>
            </div>
            <div class="content-card-body">
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun message pour le moment</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($messages, 0, 10) as $msg): 
                                $parts = explode('|', $msg);
                            ?>
                            <tr>
                                <td><?php echo isset($parts[0]) ? trim($parts[0]) : ''; ?></td>
                                <td><?php echo isset($parts[2]) ? trim($parts[2]) : ''; ?></td>
                                <td><?php echo isset($parts[3]) ? trim($parts[3]) : ''; ?></td>
                                <td class="message-content">Message depuis le formulaire</td>
                                <td><span class="badge badge-new">Nouveau</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <br><br>
        
        <!-- Blog Management -->
        <div class="content-card" id="blog">
            <div class="content-card-header">
                <h2><i class="fas fa-blog"></i> Articles du blog</h2>
                <button class="btn" style="padding: 8px 20px; background: var(--success); color: white; border: none; border-radius: 8px; cursor: pointer;">
                    <i class="fas fa-plus"></i> Nouvel article
                </button>
            </div>
            <div class="content-card-body">
                <div class="empty-state">
                    <i class="fas fa-pen-fancy"></i>
                    <p>Module de gestion du blog - À connecter à une base de données</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
