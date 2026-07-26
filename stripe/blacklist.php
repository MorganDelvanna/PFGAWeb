<?php
require_once 'shared.php';

// require login (same as email.php)
if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
	$loggedIn = TRUE;
} else {
	echo "You are not logged in";
	die;
}

// DB config (match other stripe pages)
$dbHost = $_ENV['DB_HOST'] ?? 'localhost:3306';
$dbName = $_ENV['DB_NAME'] ?? null;
$dbUser = $_ENV['DB_USER'] ?? null;
$dbPass = $_ENV['DB_PASS'] ?? null;

$message = '';
$error = '';

// handle add or delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!$dbName || !$dbUser) {
		$error = 'Database not configured';
	} else {
		$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
		if ($mysqli->connect_errno) {
			error_log('DB connect failed in blacklist.php: ' . $mysqli->connect_error);
			$error = 'Database connection failed';
		} else {
			// add email
			if (isset($_POST['action']) && $_POST['action'] === 'add') {
				$email = trim((string)($_POST['email'] ?? ''));
				if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$error = 'Please provide a valid email address';
				} else {
					$stmt = $mysqli->prepare('INSERT INTO blacklist (`email`) VALUES (?)');
					if ($stmt) {
						$stmt->bind_param('s', $email);
						if ($stmt->execute()) {
							$message = 'Email added to blacklist';
						} else {
							// Duplicate entry? show friendly message
							if ($mysqli->errno === 1062) {
								$error = 'That email is already on the blacklist';
							} else {
								$error = 'Failed to add email: ' . $mysqli->error;
								error_log('blacklist insert failed: ' . $mysqli->error);
							}
						}
						$stmt->close();
					} else {
						$error = 'Failed to prepare insert statement: ' . $mysqli->error;
						error_log('blacklist prepare failed: ' . $mysqli->error);
					}
				}
			}

			// delete email
			if (isset($_POST['action']) && $_POST['action'] === 'delete') {
				$id = intval($_POST['id'] ?? 0);
				if ($id <= 0) {
					$error = 'Invalid id';
				} else {
					$stmt = $mysqli->prepare('DELETE FROM blacklist WHERE id = ?');
					if ($stmt) {
						$stmt->bind_param('i', $id);
						if ($stmt->execute()) {
							$message = 'Email removed from blacklist';
						} else {
							$error = 'Failed to delete: ' . $mysqli->error;
							error_log('blacklist delete failed: ' . $mysqli->error);
						}
						$stmt->close();
					} else {
						$error = 'Failed to prepare delete statement: ' . $mysqli->error;
						error_log('blacklist delete prepare failed: ' . $mysqli->error);
					}
				}
			}

			$mysqli->close();
		}
	}
}

// fetch current blacklist
$rows = [];
if ($dbName && $dbUser) {
	$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	if (!$mysqli->connect_errno) {
		$res = $mysqli->query('SELECT id, email FROM blacklist ORDER BY email ASC');
		if ($res) {
			while ($r = $res->fetch_assoc()) $rows[] = $r;
			$res->close();
		} else {
			error_log('blacklist select failed: ' . $mysqli->error);
			$error = 'Failed to load blacklist';
		}
		$mysqli->close();
	}
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Blacklist Management</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/pfga.css">
</head>
<body>
  <div class="container mt-4">
	<h1>Blacklist Management</h1>
	<?php if ($message): ?>
	  <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
	<?php endif; ?>
	<?php if ($error): ?>
	  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card mb-3">
	  <div class="card-body">
		<form method="post" action="blacklist.php" class="form-inline">
		  <input type="hidden" name="action" value="add">
		  <div class="form-group mr-2">
			<label for="email" class="sr-only">Email</label>
			<input id="email" name="email" type="email" class="form-control" placeholder="email@domain.tld" required>
			<button class="btn btn-primary" type="submit">Add to blacklist</button>
		  </div>		  
		</form>
	  </div>
	</div>

	<div class="card">
	  <div class="card-header">Current Blacklist (<?= count($rows) ?>)</div>
	  <div class="card-body p-0">
		<table class="table table-sm mb-0">
		  <thead>
			<tr><th>Email</th><th style="width:120px">Actions</th></tr>
		  </thead>
		  <tbody>
			<?php if (empty($rows)): ?>
			  <tr><td colspan="2"><em>No blacklisted emails</em></td></tr>
			<?php else: foreach ($rows as $r): ?>
			  <tr>
				<td><?= htmlspecialchars($r['email']) ?></td>
				<td>
				  <form method="post" action="blacklist.php" style="display:inline" onsubmit="return confirm('Remove this email from blacklist?');">
					<input type="hidden" name="action" value="delete">
					<input type="hidden" name="id" value="<?= intval($r['id']) ?>">
					<button type="submit" class="btn btn-sm btn-danger">Delete</button>
				  </form>
				</td>
			  </tr>
			<?php endforeach; endif; ?>
		  </tbody>
		</table>
	  </div>
	</div>

	<p class="mt-3"><a href="email.php">Back to Email</a></p>
  </div>
</body>
</html>
