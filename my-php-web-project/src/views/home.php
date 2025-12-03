<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>Welcome to My PHP Web Project</h1>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Home</h2>
        <p>This is the home page of your dynamic PHP website.</p>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> My PHP Web Project. All rights reserved.</p>
    </footer>
    <script src="/js/main.js"></script>
</body>
</html>