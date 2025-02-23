<?php
session_start();
include 'db.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['task_id'] ?? null;
$subtask_id = $_GET['subtask_id'] ?? null;

if (!$task_id || !$subtask_id) {
    header("Location: index.php");
    exit();
}

// Mengambil informasi tugas utama
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: index.php");
    exit();
}

// Mengambil data subtugas yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM subtasks WHERE id = ? AND task_id = ?");
$stmt->execute([$subtask_id, $task_id]);
$subtask = $stmt->fetch();

if (!$subtask) {
    header("Location: subtasks.php?task_id=$task_id");
    exit();
}

// Mengupdate subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subtask_name = $_POST['subtask'];
    $due_date = $_POST['due_date'];
    $aksi = $_POST['aksi'];

    $stmt = $pdo->prepare("UPDATE subtasks SET subtask = ?, due_date = ?, aksi = ? WHERE id = ?");
    $stmt->execute([$subtask_name, $due_date, $aksi, $subtask_id]);

    // Setelah berhasil update, kembali ke halaman subtasks.php dengan task_id
    header("Location: subtasks.php?task_id=$task_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subtugas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right,  #8FBC8F, #8FBC8F);
            color: white;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            padding: 20px;
        }
        .container {
            width: 90%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .back-link:hover {
            color: #8FBC8F;
            text-decoration: underline;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input, select, button {
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        input, select {
            background: white;
            font-size: 16px;
            text-align: center;
        }
        button {
            background-color:rgb(116, 243, 118);
            color: black;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: #8FBC8F;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Edit Subtugas untuk: <br><?= htmlspecialchars($task['task']) ?></h2>
        <a href="subtasks.php?task_id=<?= $task_id ?>" class="back-link">⬅ Kembali ke subtugas</a>

        <!-- Form Edit Subtugas -->
        <form method="POST">
            <input type="text" name="subtask" value="<?= htmlspecialchars($subtask['subtask']) ?>" required>
            <input type="date" name="due_date" value="<?= htmlspecialchars($subtask['due_date']) ?>" required min="<?= date('Y-m-d') ?>">

            <!-- Dropdown aksi -->
            <select name="aksi" required>
                <option value="Penting" <?= ($subtask['aksi'] == 'Penting') ? 'selected' : '' ?>>Penting</option>
                <option value="Biasa" <?= ($subtask['aksi'] == 'Biasa') ? 'selected' : '' ?>>Biasa</option>
                <option value="Tidak Penting" <?= ($subtask['aksi'] == 'Tidak Penting') ? 'selected' : '' ?>>Tidak Penting</option>
            </select>

            <button type="submit">📝 Update Subtugas</button>
        </form>
    </div>
</body>
</html>
