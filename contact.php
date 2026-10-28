<?php
require_once 'config.php';
require_once 'partials.php';

$status = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $status = ['type' => 'error', 'text' => 'All fields are required.'];
    } else {
        // Prepared statement: this form is intentionally NOT part of the lab's vulnerable surface.
        $stmt = mysqli_prepare($conn, "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $status = ['type' => 'ok', 'text' => 'Thanks ' . htmlspecialchars($name) . ', your message has been received.'];
    }
}

render_header('contact');
?>

<main class="page wrap">
    <h2>Get in touch</h2>

    <?php if ($status): ?>
        <div class="notice <?= $status['type'] === 'error' ? 'error' : '' ?>"><?= $status['text'] ?></div>
    <?php endif; ?>

    <form method="post" action="/contact.php">
        <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="field">
            <label for="message">Message</label>
            <textarea id="message" name="message" required></textarea>
        </div>
        <button class="btn" type="submit">Send message</button>
    </form>

    <?php
    $result = mysqli_query($conn, "SELECT name, email, created_at FROM contacts ORDER BY created_at DESC LIMIT 10");
    if ($result && mysqli_num_rows($result) > 0):
    ?>
        <h3 style="margin-top:40px;">Recent inquiries</h3>
        <table class="list">
            <tr><th>Name</th><th>Email</th><th>Received</th></tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</main>

<?php render_footer(); ?>
