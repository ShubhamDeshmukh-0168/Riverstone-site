<?php
require_once 'partials.php';

/**
 * ============================================================
 *  LAB-VULN: Unrestricted file upload, on purpose.
 * ============================================================
 * This handler is deliberately weak so you can practice the
 * "image upload -> code execution" pattern that shows up in a
 * lot of real CVEs. The flaw: it only blocks a short extension
 * blacklist and trusts the client-supplied MIME type, instead
 * of validating actual file content and serving uploads from a
 * non-executable location. That means a file like
 * profile.phtml, profile.pht, or an upload with a double
 * extension can slip through and land in a web-executable
 * folder.
 *
 * DO NOT deploy this outside an isolated lab / private VPC.
 * DO NOT reuse this upload handler in a real project.
 * ============================================================
 */

$status = null;
$upload_dir = __DIR__ . '/uploads/';

// Weak, incomplete blacklist -- easy to bypass with alternate
// executable extensions (.phtml, .pht, .php5, .phar, etc.)
$blocked_extensions = ['php', 'exe', 'sh'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $status = ['type' => 'error', 'text' => 'Upload failed (error code ' . $file['error'] . ').'];
    } else {
        $original_name = basename($file['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        // Trusting the browser-supplied MIME type instead of inspecting file content.
        $client_mime = $file['type'];

        if (in_array($ext, $blocked_extensions, true)) {
            $status = ['type' => 'error', 'text' => 'That file extension is not allowed.'];
        } else {
            $dest = $upload_dir . $original_name;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $status = [
                    'type' => 'ok',
                    'text' => 'Uploaded as ' . htmlspecialchars($original_name) .
                        ' (reported type: ' . htmlspecialchars($client_mime) . ').'
                ];
            } else {
                $status = ['type' => 'error', 'text' => 'Could not save the uploaded file.'];
            }
        }
    }
}

render_header('team');
?>

<main class="page wrap">
    <h2>Team photo upload</h2>
    <p>Upload a profile photo for the team directory.</p>

    <?php if ($status): ?>
        <div class="notice <?= $status['type'] === 'error' ? 'error' : '' ?>"><?= $status['text'] ?></div>
    <?php endif; ?>

    <form method="post" action="/team.php" enctype="multipart/form-data">
        <div class="field">
            <label for="photo">Photo</label>
            <input type="file" id="photo" name="photo" required>
        </div>
        <button class="btn" type="submit">Upload</button>
    </form>

    <h3 style="margin-top:40px;">Uploaded files</h3>
    <table class="list">
        <tr><th>File</th><th>Uploaded</th></tr>
        <?php
        $files = array_diff(scandir($upload_dir), ['.', '..', '.gitkeep']);
        rsort($files);
        foreach ($files as $f):
        ?>
            <tr>
                <td><a href="/uploads/<?= rawurlencode($f) ?>" target="_blank"><?= htmlspecialchars($f) ?></a></td>
                <td><?= date('Y-m-d H:i:s', filemtime($upload_dir . $f)) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</main>

<?php render_footer(); ?>
