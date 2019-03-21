<?php
ini_set("error_reporting", E_ALL);
$host = '';
$user = '';
$password = '';
$database = '';
	// создаем базу данных, если не создана
$link = new MYSQLI($host, $user, $password);
if($link){
	$sql = "CREATE DATABASE IF NOT EXISTS {$database} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$link->query($sql);
}
$link->close();
unset($link);
$connect = new MYSQLI($host, $user, $password, $database) or exit('Не удалось соединиться с базой данных '.$database);
	// создаем таблицу, если не создана
$sql = " CREATE TABLE IF NOT EXISTS `{$database}`.`links` (
	`id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	`domain` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`link` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, 
	`alias` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`shelf_life` DATE NOT NULL,
 	PRIMARY KEY (`id`)
 	)
	ENGINE InnoDB 
	CHARSET=utf8mb4
	COLLATE utf8mb4_unicode_ci;";
$connect->query($sql);

if(isset($_POST['link'])){	
	$user_link = htmlspecialchars($_POST['link']);
	
		// получаем из ссылки
	preg_match('#(https?://(www\.)?[0-9A-Za-z]+\.[a-z]+/)(.+)#', $user_link, $qwe);
		// домен
	$domain = $connect->real_escape_string($qwe[1]);
		// адрес после домена
	$link = $connect->real_escape_string($qwe[2]);
		// алиас адреса
	$alias = md5($link).'{9}';
		// дату хранения
	$date = date('Y-m-d');
	$shelf_life = date('Y-m-d' , strtotime($date. ' + 30 days'));

		// проверить ссылку в БД, если нет сохранить
	$sql = "SELECT `id` FROM `links` WHERE `domain`='$domain' AND `link`='$link'";
	$res = $connect->query($sql);

	if($connect->query($sql)){
		$msg = '<p>Короткая ссылка для данного адреса уже существует</p>';
	}else{
		$sql = "INSERT INTO `links` (`domain`, `link`, `alias`, `shelf_life`)
			VALUES ('$domain', '$link', '$alias', '$shelf_life')
			";
		$connect->query($sql);
	}
}
	// выводим из Бд все не просроченне ссылки

$sql = "SELECT * FROM `links` WHERE CURRENT_DATE() < `shelf_life` ";
$res = $connect->query($sql);
$connect->close();
?>


<style>
*{
	padding: 0;
	margin: 0;
	box-sizing: border-box;
}
html, body{
	display: flex;
	justify-content: center;
}
.page_wrap{
	max-width: 1280px;
	align-self: center;
	padding: 10px;
}
form{
	display: flex;
	flex-direction: column;
	padding: 20px;
}
input, p, h2{
	margin: 5px;
}
.message p{
	font-size: 20px;
	font-weight: bold;
	color: red;
}
#name, #email, #submit, #delete{
	align-self: flex-start;
}

footer{
	height: 100px;
	margin: 20px 0 0;
}

</style>


<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<div class="page_wrap">
		<div class="message">
			<?php if(isset($msg)){echo $msg;} ?>
		</div>
		<h2>Преобразовать ссылку:</h2>
		<form action="" method="POST">
			<label for="name"><p>Введите ссылку</p></label>
			<input id="link" name="link" type="url" required />
			<input type="submit" name="submit" value="Сохранить" id="submit" />
		</form>

	<?php	if(isset($domain) && isset($link)){ ?>
				<h2>Полученная ссылка</h2>
				<a href="<?php echo $domain.$link ?>">
					<p><?php echo $domain.$alias ?></p>
				</a>
	<?php	}	?>

		<div class="link_list">
			<h2>Список ссылок</h2>
			<?php 
				while($link = $res->fetch_array()){ ?>
				<p>
					<?=$link['domain'].$link['alias']; ?>
				</p>	
			<?php	}
			?>
		</div>
		<footer></footer>
	</div>
	
</body>
</html>