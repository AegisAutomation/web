<?php
/**
 * Aegis Web Installer
 *
 * Security note: Always secure this installer (delete after use).
 */

session_start();

$step = isset($_GET["step"]) ? intval($_GET["step"]) : 1;

function renderHeader($title = "Web Installer")
{
    echo "<!DOCTYPE html><html><head><title>$title</title>";
    echo "<style>body{font-family:sans-serif;margin:40px;}input,select{padding:5px;margin:5px;}</style>";
    echo "</head><body><h1>$title</h1>";
}

function renderFooter()
{
    echo "</body></html>";
}

if ($step === 1) {
    renderHeader("Step 1: GitHub Repository"); ?>
    <form method="post" action="?step=2">
        <label>GitHub Repository URL:<br>
            <input type="text" name="repo_url" placeholder="https://github.com/username/repo.git" required>
        </label><br>
        <label>Branch (default: main):<br>
            <input type="text" name="branch" value="main">
        </label><br>
        <button type="submit">Next</button>
    </form>
    <?php renderFooter();
} elseif ($step === 2 && $_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION["repo_url"] = $_POST["repo_url"];
    $_SESSION["branch"] = $_POST["branch"];

    renderHeader("Step 2: Database Setup");
    ?>
    <form method="post" action="?step=3">
        <label>Database Host:<br>
            <input type="text" name="db_host" value="localhost" required>
        </label><br>
        <label>Database Name:<br>
            <input type="text" name="db_name" required>
        </label><br>
        <label>Database User:<br>
            <input type="text" name="db_user" required>
        </label><br>
        <label>Database Password:<br>
            <input type="password" name="db_pass" required>
        </label><br>
        <button type="submit">Next</button>
    </form>
    <?php renderFooter();
} elseif ($step === 3 && $_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION["db"] = [
        "host" => $_POST["db_host"],
        "name" => $_POST["db_name"],
        "user" => $_POST["db_user"],
        "pass" => $_POST["db_pass"],
    ];

    renderHeader("Step 3: SSH Key Setup");
    ?>
    <p>To deploy from GitHub over SSH, you need an SSH key:</p>
    <ol>
        <li>On your server, run: <code>ssh-keygen -t rsa -b 4096 -C "your_email@example.com"</code></li>
        <li>Copy the contents of <code>~/.ssh/id_rsa.pub</code> into your GitHub account under <b>Settings → SSH and GPG keys</b>.</li>
        <li>Ensure <code>ssh-agent</code> is running and your key is added with <code>ssh-add ~/.ssh/id_rsa</code>.</li>
    </ol>
    <form method="post" action="?step=4">
        <label>Have you added your SSH key to GitHub?
            <select name="ssh_ready">
                <option value="yes">Yes</option>
                <option value="no">No (skip for now)</option>
            </select>
        </label><br>
        <button type="submit">Next</button>
    </form>
    <?php renderFooter();
} elseif ($step === 4 && $_SERVER["REQUEST_METHOD"] === "POST") {

    renderHeader("Final Step: Configuration Complete");

    $config =
        "<?php\nreturn " .
        var_export(
            [
                "repo_url" => $_SESSION["repo_url"],
                "branch" => $_SESSION["branch"],
                "database" => $_SESSION["db"],
            ],
            true
        ) .
        ";\n";

    file_put_contents(__DIR__ . "/config.php", $config);
    ?>
    <p>✅ Configuration file created at <code>config.php</code>.</p>
    <p>Next steps:</p>
    <ol>
        <li>Clone the repo manually:<br>
            <code>git clone -b <?= htmlspecialchars(
                $_SESSION["branch"]
            ) ?> <?= htmlspecialchars($_SESSION["repo_url"]) ?></code>
        </li>
        <li>Run your database migrations/import SQL schema.</li>
        <li>Delete this <code>install.php</code> file for security.</li>
    </ol>
    <?php renderFooter();
} else {
    header("Location: ?step=1");
    exit();
}
