<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>KnowHub setup needed</title>
        <link rel="stylesheet" href="assets/css/style.css" />
    </head>
    <body class="setup-page">
        <main class="setup-card">
            <div class="brand-mark">K</div>
            <p class="eyebrow">One quick setup step</p>
            <h1>Connect your database</h1>
            <p>
                KnowHub is ready, but it cannot reach MySQL yet. Create the database, import
                the two SQL files, and check your local credentials.
            </p>
            <ol>
                <li>Start Apache and MySQL in XAMPP.</li>
                <li>Open phpMyAdmin and import <code>database/schema.sql</code>.</li>
                <li>Then import <code>database/seed.sql</code>.</li>
                <li>Check <code>config/config.php</code> for your database credentials.</li>
            </ol>
            <a class="button button-primary" href="README.md">Open setup guide</a>
        </main>
    </body>
</html>
