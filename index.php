<?php
/**
 * Aegis Web Installer
 *
 * Security note: Delete this installer after setup.
 */

session_start();

$step = isset($_GET["step"]) ? intval($_GET["step"]) : 1;

define("REPO_URL", "https://github.com/AegisAutomation/web/src");
define("REPO_BRANCH", "main");
define("APP_DIR", __DIR__ . "/app");

function renderHeader($title = "Web Installer")
{
    echo "<!DOCTYPE html><html><head><title>" .
        htmlspecialchars($title) .
        "</title>";
    echo "<style>body{font-family:sans-serif;margin:40px;}input{padding:5px;margin:5px;} .error{color:red;}</style>";
    echo "</head><body><h1>" . htmlspecialchars($title) . "</h1>";
}
function renderFooter()
{
    echo "</body></html>";
}

if ($step === 1) {
    renderHeader("Step 1: Database Setup"); ?>
    <form method="post" action="?step=2">
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

} elseif ($step === 2 && $_SERVER["REQUEST_METHOD"] === "POST") {
    $db = [
        "host" => trim($_POST["db_host"]),
        "name" => trim($_POST["db_name"]),
        "user" => trim($_POST["db_user"]),
        "pass" => $_POST["db_pass"],
    ];

    try {
        $dsn = "mysql:host={$db["host"]};dbname={$db["name"]};charset=utf8mb4";
        $pdo = new PDO($dsn, $db["user"], $db["pass"], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $_SESSION["db"] = $db;
        header("Location: ?step=3");
        exit();
    } catch (Exception $e) {
        renderHeader("Step 1: Database Setup");
        echo "<p class='error'>Database connection failed: " .
            htmlspecialchars($e->getMessage()) .
            "</p>";
        echo "<p><a href='?step=1'>Go back</a></p>";
        renderFooter();
    }

} elseif ($step === 3) {
    renderHeader("Step 2: Installing Application");

    $output = [];
    $status = null;

    if (!is_dir(APP_DIR)) {
        exec(
            "git clone -b " .
                escapeshellarg(REPO_BRANCH) .
                " " .
                escapeshellarg(REPO_URL) .
                " " .
                escapeshellarg(APP_DIR) .
                " 2>&1",
            $output,
            $status
        );
    } else {
        exec(
            "cd " .
                escapeshellarg(APP_DIR) .
                " && git fetch --all && git reset --hard origin/" .
                escapeshellarg(REPO_BRANCH) .
                " 2>&1",
            $output,
            $status
        );
    }

    if ($status === 0) {
        echo "<p>✅ Source code pulled from GitHub.</p>";

        $config = <<<PHP
<?php
return [
    'database' => [
        'host' => '{$_SESSION["db"]["host"]}',
        'name' => '{$_SESSION["db"]["name"]}',
        'user' => '{$_SESSION["db"]["user"]}',
        'pass' => '{$_SESSION["db"]["pass"]}',
    ],
];
PHP;
        file_put_contents(APP_DIR . "/config.php", $config);

        echo "<p>✅ Configuration file created at <code>app/config.php</code>.</p>";
        echo "<p>Next steps:</p><ol>
            <li>Run database migrations or import schema.</li>
            <li>Create your first admin user.</li>
            <li>Delete <code>index.php</code> (installer) for security.</li>
            <li>Access your app at <code>/app</code>.</li>
        </ol>";
    } else {
        echo "<p class='error'>❌ Git operation failed:</p><pre>" .
            htmlspecialchars(implode("\n", $output)) .
            "</pre>";
    }

    renderFooter();
} else {
    header("Location: ?step=1");
    exit();
}
