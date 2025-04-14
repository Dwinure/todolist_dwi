<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['task_id'] ?? null;

if (!$task_id) {
    header("Location: index.php");
    exit();
}

// Tandai semua subtugas sebagai selesai
if (isset($_GET['complete_all'])) {
    $stmt = $pdo->prepare("UPDATE subtasks SET status = 1 WHERE task_id = ?");
    $stmt->execute([$task_id]);
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

// Tambah subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subtask'])) {
    $subtask = $_POST['subtask'];
    $due_date = $_POST['due_date'];
    $aksi = $_POST['aksi'];

    $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, subtask, due_date, aksi, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$task_id, $subtask, $due_date, $aksi, 0]);
    header("Location: subtasks.php?task_id=$task_id");
    exit();
}

// Ambil semua subtugas
$subtasks = $pdo->prepare("
    SELECT * FROM subtasks 
    WHERE task_id = ? 
    ORDER BY FIELD(aksi, 'Penting', 'Biasa', 'Tidak Penting'), due_date ASC
");
$subtasks->execute([$task_id]);
$subtasks = $subtasks->fetchAll();

// Hapus subtugas
if (isset($_GET['delete_subtask'])) {
    $subtask_id = $_GET['delete_subtask'];
    $stmt = $pdo->prepare("DELETE FROM subtasks WHERE id = ?");
    $stmt->execute([$subtask_id]);
    header("Location: subtasks.php?task_id=$task_id");
    exit();
}

// Update status subtugas
if (isset($_GET['update_status']) && isset($_GET['subtask_id'])) {
    $subtask_id = $_GET['subtask_id'];

    // Cegah update jika sudah selesai (optional tapi aman)
    $stmt = $pdo->prepare("SELECT status FROM subtasks WHERE id = ?");
    $stmt->execute([$subtask_id]);
    $status = $stmt->fetchColumn();

    if ($status == 0) {
        $stmt = $pdo->prepare("UPDATE subtasks SET status = 1 WHERE id = ?");
        $stmt->execute([$subtask_id]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtugas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #8FBC8F, #8FBC8F);
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
            width: 80%;
            max-width: 700px;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        h2 { font-size: 20px; margin-bottom: 15px; }
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .back-link:hover { color: #8FBC8F; text-decoration: underline; }
        form { display: flex; flex-direction: column; gap: 10px; }
        input, select, button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        input, select { background: white; font-size: 14px; text-align: center; }
        button {
            background-color: rgb(116, 243, 118);
            color: black;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background-color: #8FBC8F; }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: white;
            border: 2px solid white;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid white;
            background-color: #8FBC8F;
        }
        .completed {
            opacity: 0.5;
        }
        .delete-subtask {
            opacity: 1;
            cursor: pointer;
        }
    </style>
    <script>
        function updateStatus(subtaskId) {
            fetch(`subtasks.php?task_id=<?= $task_id ?>&update_status=1&subtask_id=${subtaskId}`)
                .then(() => {
                    let checkbox = document.getElementById(`subtask-${subtaskId}`);
                    let row = document.getElementById(`row-${subtaskId}`);
                    
                    checkbox.checked = true;
                    checkbox.disabled = true;
                    row.classList.add('completed');
                    
                    let editBtn = document.getElementById(`edit-${subtaskId}`);
                    if (editBtn) editBtn.style.display = 'none';
                });
        }

        function completeAllTasks() {
            if (confirm('Apakah Anda yakin ingin menyelesaikan semua subtugas?')) {
                fetch(`subtasks.php?task_id=<?= $task_id ?>&complete_all=1`)
                    .then(() => location.reload());
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>📝 Subtugas untuk: <br><?= htmlspecialchars($task['task']) ?></h2>
        <a href="index.php" class="back-link">⬅ Kembali ke daftar tugas</a>

        <form method="POST">
            <input type="text" name="subtask" placeholder="Tambahkan subtugas baru" required>
            <input type="date" name="due_date" required min="<?= date('Y-m-d') ?>">
            <select name="aksi" required>
                <option value="Penting">Penting</option>
                <option value="Biasa">Biasa</option>
                <option value="Tidak Penting">Tidak Penting</option>
            </select>
            <button type="submit">➕ Tambah Subtugas</button>
            <button type="button" onclick="completeAllTasks()">✅ Selesaikan Semua</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Subtugas</th>
                    <th>Tenggat</th>
                    <th>Aksi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subtasks as $subtask): ?>
                    <tr id="row-<?= $subtask['id'] ?>" class="<?= $subtask['status'] == 1 ? 'completed' : '' ?>">
                        <td><?= htmlspecialchars($subtask['subtask']) ?></td>
                        <td><?= htmlspecialchars($subtask['due_date']) ?></td>
                        <td><?= htmlspecialchars($subtask['aksi']) ?></td>
                        <td>
                            <input type="checkbox" id="subtask-<?= $subtask['id'] ?>"
                                   onclick="updateStatus(<?= $subtask['id'] ?>)"
                                   <?= $subtask['status'] == 1 ? 'checked disabled' : '' ?>>
                        </td>
                        <td>
                            <?php if ($subtask['status'] == 0): ?>
                                <a id="edit-<?= $subtask['id'] ?>" href="edit_subtask.php?subtask_id=<?= $subtask['id'] ?>&task_id=<?= $task_id ?>">✏ Edit</a>
                            <?php endif; ?>
                            <a class="delete-subtask <?= $subtask['status'] == 1 ? 'completed' : '' ?>"
                               href="subtasks.php?task_id=<?= $task_id ?>&delete_subtask=<?= $subtask['id'] ?>"
                               onclick="return confirm('Hapus subtugas ini?')">🗑 Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
