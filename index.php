<?php
session_start();
include 'db.php';

// Jika pengguna belum login, alihkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Tambahkan tugas baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task'])) {
    $task = $_POST['task'];

    // Tambahkan tugas tanpa status
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
    $stmt->execute([$user_id, $task]);
}

// Menghapus tugas
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    header("Location: index.php");
    exit();
}

// Mengambil semua tugas pengguna
$tasks = $pdo->prepare("SELECT id, task FROM tasks WHERE user_id = ?");
$tasks->execute([$user_id]);
$tasks = $tasks->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #8FBC8F, #8FBC8F);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            width: 90%;
            max-width: 700px;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        h2 {
            color: white;
        }
        .logout-button {
            background: red;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-button:hover {
            background: darkred;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        button {
            background-color: rgb(116, 243, 118);
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background-color: rgb(116, 243, 118);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: rgb(116, 243, 118);
            color: white;
        }
        .task-link {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .task-link:hover {
            color: #FFD700;
            text-decoration: underline;
        }
        .action-buttons a {
            text-decoration: none;
            padding: 6px 10px;
            margin: 2px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        .edit-btn {
            background: #facc15;
            color: black;
        }
        .delete-btn {
            background: red;
            color: white;
        }
        .edit-btn:hover {
            background: #eab308;
        }
        .delete-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>To-Do List</h2>
            <a href="logout.php" class="logout-button" onclick="return confirm('Apakah Anda yakin ingin logout?');">Logout</a>
        </div>

        <!-- Form untuk menambah tugas baru -->
        <form method="POST">
            <input type="text" name="task" placeholder="Tambahkan tugas baru" required>
            <button type="submit">➕ Tambah Tugas</button>
        </form>

        <!-- Tabel Daftar Tugas -->
        <table>
            <thead>
                <tr>
                    <th>Tugas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td>
                            <a href="subtasks.php?task_id=<?= $task['id'] ?>" class="task-link">
                                <?= htmlspecialchars($task['task']) ?>
                            </a>
                        </td>
                        <td class="action-buttons">
                            <a href="edit_task.php?task_id=<?= $task['id'] ?>" class="edit-btn"> Edit</a>
                            <a href="?delete_task=<?= $task['id'] ?>" class="delete-btn" onclick="return confirm('Hapus tugas ini?');"> Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
