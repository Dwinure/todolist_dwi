<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data tugas berdasarkan ID
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();

    if (!$task) {
        die("Tugas tidak ditemukan!");
    }
}

// Update tugas setelah form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task'])) {
    $task_id = $_POST['task_id'];
    $task = $_POST['task'];

    $stmt = $pdo->prepare("UPDATE tasks SET task = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$task, $task_id, $user_id]);

    // Menampilkan alert menggunakan JavaScript
    echo "<script>
            alert('Tugas berhasil diubah');
            window.location.href = 'index.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(to right, #8FBC8F, #8FBC8F);
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.2);
            padding: 40px;  /* Perbesar padding */
            border-radius: 15px;  /* Lebih membulat */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);  /* Lebih tegas bayangannya */
            width: 450px;  /* Lebihkan lebar container */
            text-align: center;
        }
        h2 {
            margin-bottom: 30px;  /* Jarak lebih jauh antara judul dan form */
            font-size: 28px;  /* Perbesar ukuran font judul */
            color: #fff;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;  /* Jarak antar elemen form lebih besar */
        }
        input, button {
            padding: 15px;  /* Perbesar padding input dan button */
            border: none;
            border-radius: 8px;  /* Membuat sudut lebih lembut */
            width: 100%;
            font-size: 18px;  /* Perbesar font size input dan button */
            outline: none;
        }
        input {
            background: rgba(255, 255, 255, 0.8);
            font-size: 18px;
            text-align: center;
        }
        input:focus {
            border: 2px solid #4CAF50;
            background-color: #f9f9f9;
        }
        button {
            background-color: rgb(116, 243, 118);
            color: black;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: rgb(116, 243, 118);
            transform: scale(1.05);
        }
        .back-link {
            display: block;
            margin-top: 25px;  /* Tambahkan jarak lebih besar antara tombol dan link kembali */
            color: white;
            text-decoration: none;
            font-size: 16px;  /* Perbesar ukuran font */
            transition: 0.3s;
        }
        .back-link:hover {
            color: rgb(116, 243, 118);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Edit Tugas</h2>
        <form method="POST">
            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
            <input type="text" name="task" value="<?= htmlspecialchars($task['task']) ?>" required placeholder="Nama Tugas">
            <button type="submit">✔ Simpan Perubahan</button>
        </form>
        <a href="index.php" class="back-link">⬅ Kembali ke Daftar</a>
    </div>
</body>
</html>
