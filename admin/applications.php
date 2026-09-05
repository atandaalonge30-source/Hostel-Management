<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once '../includes/db.php';

$msg = '';

if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE applications SET status='Approved' WHERE app_id=?")->execute([$_GET['approve']]);
    $msg = 'Application approved successfully.';
}
if (isset($_GET['reject'])) {
    $pdo->prepare("UPDATE applications SET status='Rejected' WHERE app_id=?")->execute([$_GET['reject']]);
    $msg = 'Application rejected.';
}

$apps = $pdo->query("SELECT a.*, s.full_name, s.matric_no, s.department, s.level, s.gender, h.hostel_name, h.hostel_type 
    FROM applications a 
    JOIN students s ON a.student_id = s.student_id 
    JOIN hostels h ON a.hostel_id = h.hostel_id 
    ORDER BY a.applied_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applications - Admin</title>
<link rel="stylesheet" href="../css/style.css?v=6">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar">
    <div class="brand"><img src="../assets/POLYLOGO.jpg" alt="The Polytechnic, Ibadan logo"> Poly Ibadan HMS Admin</div>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="hostels.php">Hostels</a></li>
        <li><a href="students.php">Students</a></li>
        <li><a href="applications.php" class="active">Applications</a></li>
        <li><a href="allocations.php">Allocations</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="logout.php" class="logout">Logout</a></li>
    </ul>
</nav>
<div class="wrapper">
<aside class="sidebar">
    <div class="user-info">
        <div class="avatar">A</div>
        <div class="name"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
        <div class="role">Administrator</div>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="hostels.php">🏨 Manage Hostels</a>
        <a href="students.php">👥 Students</a>
        <a href="applications.php" class="active">📋 Applications</a>
        <a href="allocations.php">🛏 Allocations</a>
        <a href="payments.php">💰 Payments</a>
        <a href="reports.php">📊 Reports</a>
        <a href="logout.php">🚪 Logout</a>
    </nav>
</aside>
<main class="main">
    <div class="page-title">Hostel Applications</div>
    <div class="page-subtitle">Review and process student accommodation applications</div>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header"><h3>All Applications (<?= count($apps) ?>)</h3></div>
        <div class="table-wrap">
            <?php if (empty($apps)): ?>
                <div class="empty-state"><span class="empty-icon">📋</span><p>No applications submitted yet.</p></div>
            <?php else: ?>
            <table>
                <thead><tr><th>#</th><th>Student</th><th>Matric No</th><th>Dept / Level</th><th>Gender</th><th>Hostel Applied</th><th>Payment Ref</th><th>Date Applied</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($apps as $i => $a): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($a['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($a['matric_no']) ?></td>
                    <td><?= htmlspecialchars($a['department']) ?> / <?= htmlspecialchars($a['level']) ?></td>
                    <td><span class="badge <?= $a['gender']==='Male'?'badge-info':'badge-warning' ?>"><?= $a['gender'] ?></span></td>
                    <td><?= htmlspecialchars($a['hostel_name']) ?> <small>(<?= $a['hostel_type'] ?>)</small></td>
                    <td><?= htmlspecialchars($a['payment_ref'] ?? 'N/A') ?></td>
                    <td><?= date('d M Y', strtotime($a['applied_at'])) ?></td>
                    <td>
                        <?php
                        $badge = match($a['status']) {
                            'Approved' => 'badge-success',
                            'Rejected' => 'badge-danger',
                            default => 'badge-warning'
                        };
                        ?>
                        <span class="badge <?= $badge ?>"><?= $a['status'] ?></span>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php if ($a['status'] === 'Pending'): ?>
                            <a href="?approve=<?= $a['app_id'] ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="?reject=<?= $a['app_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this application?')">Reject</a>
                        <?php elseif ($a['status'] === 'Approved'): ?>
                            <a href="allocations.php?student_id=<?= $a['student_id'] ?>" class="btn btn-info btn-sm">Allocate Room</a>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:0.8rem;">No action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</main>
</div>
<div class="footer">&copy; <?= date('Y') ?> The Polytechnic, Ibadan</div>
</body>
</html>
