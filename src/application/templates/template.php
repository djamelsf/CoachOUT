<!DOCTYPE html>
<html lang="fr">
<head>
	<title><?php echo $le_titre ?></title>
	<meta charset="UTF-8" />
<!--	<link rel="stylesheet" href="skin/poems.css" />-->
</head>
<body>
	<nav class="menu">
		<ul>
<?php
	foreach ($le_menu as $text => $link) {
		echo "<li><a href=\"$link\">$text</a></li>";
	}
?>
		</ul>
	</nav>
	<main>
		<h1><?php echo $le_titre; ?></h1>
		<?php echo $le_contenu; ?>
	</main>
</body>
</html>

