<!DOCTYPE html>
<html>
<head>
    <title>SynthesePro Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div style="max-width:400px;margin:50px auto;padding:20px;border:1px solid #ddd;border-radius:10px;">
<h2>Connexion SynthesePro</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="email" name="email" placeholder="email" required><br><br>
    <input type="password" name="password" placeholder="mot de passe" required><br><br>
    <button type="submit">Connexion</button>
</form>
</div>
</body>
</html>

