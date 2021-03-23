<!DOCTYPE html>
<html lang="fr">
<head>
	<title><?php echo $le_titre ?></title>
	<meta charset="UTF-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <style>
        .feedback {
            text-align: center;
            font-weight: bold;
            color: white;
            background: #a80b24;
            border-radius: 1em;
            margin: 1em auto;
            max-width: 90%;
            padding: .5em;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href=".">
        <img src="logo.png" width="200" height="30" class="d-inline-block align-top" alt="">
    </a>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
<?php
	foreach ($le_menu as $text => $link) {
		echo "<li class='nav-item'><a href=\"$link\" class='nav-link'>$text</a></li>";
	}
?>
		</ul>
        <?php if(isset($_SESSION['user'])){ ?>
        <form class="form-inline my-2 my-lg-0">
            <input class="form-control mr-sm-2" type="search" placeholder="Chercher un groupe" aria-label="Search" name="mot">
            <input type='hidden' name='o' value='groupe'>
            <input type='hidden' name='a' value='recherche'>
            <button class="btn btn-outline-light my-2 my-sm-0" style="border-color: #fc5200; color: #fc5200;" type="submit">Chercher</button>
        </form>
        <?php } ?>
    </div>
	</nav>
	<main>
        <?php if ($feedback!=''){
            echo '<div class="alert alert-danger" role="alert">'.$feedback.'</div>';
        }?>
		<?php echo $le_contenu; ?>
	</main>
</body>
</html>

