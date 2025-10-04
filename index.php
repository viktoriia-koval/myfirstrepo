<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vika";

// Создаём подключение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
  die("Verbindungsfehler: " . $conn->connect_error);
}
echo "Verbindung erfolgreich!";

// Удаление книги
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM books WHERE ID = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit;
}

// Добавление или обновление книги
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $publishing_year = $_POST['publishing_year'];
    $publisher_id = $_POST['publisher_id'];

    if (isset($_POST['id']) && $_POST['id'] != '') {
        // Редактирование
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE books SET title=?, description=?, publishing_year=?, publisher_id=? WHERE ID=?");
        $stmt->execute([$title, $description, $publishing_year, $publisher_id, $id]);
    } else {
        // Добавление
        $stmt = $pdo->prepare("INSERT INTO books (title, description, publishing_year, publisher_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $publishing_year, $publisher_id]);
    }

    header("Location: index.php");
    exit;
}

// Получение списка издательств
$publishers = $pdo->query("SELECT * FROM publisher")->fetchAll(PDO::FETCH_ASSOC);

// Если редактируем книгу — получаем её данные
$editBook = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM books WHERE ID = ?");
    $stmt->execute([$id]);
    $editBook = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получение списка книг с издательствами
$books = $pdo->query("
    SELECT books.*, publisher.title AS publisher_name
    FROM books
    JOIN publisher ON books.publisher_id = publisher.ID
    ORDER BY books.ID DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Unsere Bibliothek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<div class="container">
    <h2 class="fw-bold">Unsere Bibliothek</h2>
    <p>Das hier ist unsere öffentliche Bibliothek...</p>

    <!-- Форма добавления/редактирования -->
    <div class="card p-4 mb-5">
        <h4 class="fw-bold"><?= $editBook ? "Buch bearbeiten" : "Neues Buch anlegen" ?></h4>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editBook['ID'] ?? '' ?>">
            <div class="mb-3">
                <label class="form-label">Buchtitel</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editBook['title'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Kurzbeschreibung</label>
                <textarea name="description" class="form-control" maxlength="150" required><?= htmlspecialchars($editBook['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Erscheinungsjahr</label>
                <input type="number" name="publishing_year" class="form-control" required value="<?= htmlspecialchars($editBook['publishing_year'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Verlag</label>
                <select name="publisher_id" class="form-select" required>
                    <?php foreach ($publishers as $p): ?>
                        <option value="<?= $p['ID'] ?>" <?= isset($editBook['publisher_id']) && $editBook['publisher_id'] == $p['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?= $editBook ? "Speichern" : "Neues Buch erstellen" ?></button>
            <?php if ($editBook): ?>
                <a href="index.php" class="btn btn-secondary">Abbrechen</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Вывод книг -->
    <h4 class="fw-bold">Unsere Bücher</h4>
    <div class="row g-3">
        <?php foreach ($books as $book): ?>
            <div class="col-md-3">
                <div class="card p-3">
                    <h6 class="fw-bold"><?= htmlspecialchars($book['title']) ?></h6>
                    <p><?= htmlspecialchars($book['description']) ?></p>
                    <small class="text-muted">Verlag: <?= htmlspecialchars($book['publisher_name']) ?> (<?= $book['publishing_year'] ?>)</small>
                    <div class="mt-3">
                        <a href="?edit=<?= $book['ID'] ?>" class="btn btn-outline-primary btn-sm">✏ Bearbeiten</a>
                        <a href="?delete=<?= $book['ID'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bist du sicher?')">🗑 Löschen</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
