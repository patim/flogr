<?php
/////////////////////////////////////////////////////////////////////
// Functional part of the photo website
// Maxim Zalutskiy
// 2008
/////////////////////////////////////////////////////////////////////
class db {

	private $db_l;

	private $db_login;
	private $db_pass;
	private $db_host;
	private $db_name;

	private $connected = false;

	private function db_e($text) { // view error mesage
		die ("ERROR[<i>'$text'</i>]:<br>".mysql_error($this->db_l));
	}

	function __construct($login="phb", $pass="nooGo1ol", $host="localhost", $name="phb") {
		//	echo "construct in db";
		$this->db_login=$login;
		$this->db_pass=$pass;
		$this->db_host=$host;
		$this->db_name=$name;

		$this->db_l=mysql_pconnect($this->db_host, $this->db_login, $this->db_pass) or $this->db_e('connect');
		mysql_select_db($this->db_name, $this->db_l) or $this->db_e('select db');
		$this->connected = true;
	}

	function check_connect() {
		return $this->connected;
	}

	function db_close() {// close connection
		#    mysql_close($this->db_l);
	}

	function q($q) {// query
		//$res = mysql_query($q, $this->db_l) or $this->db_e('query:'.$q);
		$res = mysql_query($q, $this->db_l);
		return $res;
	}
}


class user extends db {
	public  $usr_login;
	private $usr_pass;
	private $rights;
	private $logged = false;

	function __construct() {
		if( !db::check_connect() )
		db::__construct();

		if( isset($_SESSION['id_user']) ) {
			$this->logged = true;
			$this->usr_login = $_SESSION['user'];
			$this->rights = $_SESSION['rights'];
		}
		/*
		$this->usr_login = $usr_l;
		$this->usr_pass = $usr_p;
		*/
	}

	// авторизация
	function auth ($usr_l, $usr_p) {
		$user = $this->q("SELECT `id_user`, `name`, `pass`, `rights` FROM `user` WHERE `name`='".$usr_l."' AND `pass`='". $usr_p."'");

		if($row_user = mysql_fetch_assoc($user)) {

			$this->usr_login = $usr_l;
			$this->usr_pass =  $usr_p;

			$this->logged = true;
			$_SESSION['id_user'] =  $row_user['id_user'];
			$_SESSION['user'] =  $row_user['name'];
			$_SESSION['rights'] = $row_user['rights'];

			setcookie("user", $usr_l, time() + 3600*24*300);
			setcookie("pass", $usr_p, time() + 3600*24*300);

			$this->q("UPDATE `user` SET `last_ip`=INET_ATON('".get_ip()."') WHERE `id_user`=".$row_user['id_user']);
		} else {
			setcookie("pass", "", time() - 3600);
			$error = "Неправильное имя или пароль";
			header("location: enter.php?er=$error");
			exit;
		}
	}

	function show_user() {
		return $this->usr_login;
	}

	function check_ses() {
		return $this->logged;
	}

	function out() {
		unset( $_SESSION['alb_ses'] );
		unset( $_SESSION['alb_page']);
		unset($_SESSION['gr_rights']);
		setcookie("pass", "", time() - 3600);
		$this->logged = false;

		unset( $_SESSION['id_user'] );
		unset( $_SESSION['rights'] );
	}

	// проверка вводимых данных на соответствие
	function validate($name, $domain, $pass, $confpass, $email, $id_user=0) {

		$error = '';

		if($pass != $confpass)
		$error .= "Неверно подтвержденный пароль<br>";

		if(empty($pass) and !$id_user)
		$error .= "Пустой пароль<br>";

		if(empty($name))
		$error .= "Пустое имя<br>";

		if(empty($email))
		$error .= "Email не указан<br>";

		$for_edit = "";
		if($id_user != 0)
		$for_edit = " AND `id_user`!=".$id_user;

		$res = $this->q("SELECT `id_user` FROM `user` WHERE `name` LIKE '$name'".$for_edit);
		if(mysql_num_rows($res))
		$error .= "Такое имя уже существует<br>";

		// вид домена допускает латинские буквы, цифры, тире, начинающееся НЕ с цифры и тире
		if( !empty($domain) and !eregi("^([a-z]([-a-z0-9]*[a-z0-9]+)?)$", $domain))
		$error .= "Домен задан в неправильном формате<br>";

		if( !empty($domain) ) {
			$res = $this->q("SELECT `id_user` FROM `user` WHERE `domain` LIKE '$domain'".$for_edit);
			if(mysql_num_rows($res))
			$error .= "Такой домен уже существует<br>";
		}

		return $error;
	}

	function registration($fio, $name, $domain, $avatar, $pass, $confpass, $email, $captcha, $hideemail) {

		$error = $this->validate($name, $domain, $pass, $confpass, $email);
		if($_SESSION['captcha'] != $captcha) $error .= "Число на картинке указано неправильно<br>";

		if($avatar['tmp_name'])	{
			if( substr($avatar['type'], 0, strpos($avatar['type'], "/")) == 'image' ) {
				$ext = strrchr($avatar['name'], ".");

				$base = $id_user."avr".date("YmdHis",time()).$ext;
				$avtr_im = "images/".$base;

				if(empty($error))
				resizeimg($avatar['tmp_name'], $avtr_im, 48, 48);

				$insert = ", `avatar`";
				$value = ", '$base'";
			}
			else {
				$error .= "Выбранный файл для аватара не является изображением<br>";
			}
		} else {
			$insert = "";
			$value = "";
		}

		if(empty($error)) {
			$ip = get_ip();
			$res = $this->q("INSERT INTO `user` (`fio`, `name`$insert, `pass`, `email`, `reg_date`, `domain`, `hideemail`, `ip`)
							 VALUES ('".addslashes(strip_tags($fio))."', '".addslashes(strip_tags($name))."'".$value.", 
							 		 '".$pass."', '".addslashes(strip_tags($email))."', NOW(), '".$domain."', '".$hideemail."', 
							 		 INET_ATON('".$ip."'))");
			$id_user = mysql_insert_id();
			if($res) {
				mkdir("../files/".$id_user);
				unset($_SESSION['captcha']);
				return 1;
			} else
			$error = "Ошибка регистрации";
		}

		return $error;
	}

	function edit($id_user, $fio, $name, $domain, $avatar, $pass, $confpass, $email, $hideemail, $oldpass) {

		$error = $this->validate($name, $domain, $pass, $confpass, $email, $id_user);

		if ( !mysql_num_rows( $this->q("SELECT * FROM `user` WHERE `id_user`=$id_user AND `pass` LIKE '$oldpass'") ) )
		$error .= "Старый пароль неверен<br>";

		if($avatar['tmp_name'])	{
			if( substr($avatar['type'], 0, strpos($avatar['type'], "/")) == 'image' ) {
				$res = $this->q("SELECT `avatar` FROM `user` WHERE `id_user`=".$id_user);
				$old_avatar = mysql_fetch_assoc($res);

				$ext = strrchr($avatar['name'], ".");

				$base = $user."avr".date("YmdHis",time()).$ext;
				$avtr_im = "images/".$base;

				if(empty($error)) {
					if( file_exists("images/".$old_avatar['avatar'])
					and is_file("images/".$old_avatar['avatar']) )
					unlink("images/".$old_avatar['avatar']);

					resizeimg($avatar['tmp_name'], $avtr_im, 48, 48);
				}

				$update = ", `avatar`='$base'";

				//chmod($avtr_im, 0644);
			} else {
				$error .= "Выбранный файл для аватара не является изображением<br>";
			}

			unlink($avatar['tmp_name']);
		} else {
			$update = "";
		}

		if(empty($error)) {
			if( $id_user and !empty($pass) ) {
				$pass_set = ", `pass`='".$pass."'";
				setcookie("pass", $pass, time() + 3600*24*300);

			} else
			$pass_set = "";


			$res = $this->q("UPDATE `user` SET `fio`='".addslashes(strip_tags($fio))."', `domain`='".$domain."', `name`='".addslashes(strip_tags($name)).
			"'".$pass_set.", `email`='".addslashes(strip_tags($email))."', `hideemail`='".$hideemail."'$update
							WHERE `id_user`=".$id_user );
			if($res) {
				$_SESSION['user'] = $name;
				setcookie("user", $name, time() + 3600*24*300);
				return 1;
			}
			else
			$error = "Неизвестная ошибка регистрации";
		}

		return $error;
	}
}


class ph_blog_page extends db {
	private $_title; // заголовок страницы
	private $_top; // верх страницы с лого
	private $_bottom; // низ
	private $_copyright;
	private $_pop_tag;// блок для популярных тегов
	private $_left; // блок слева внизу

	public $_auth_box; // блок для авторизации/приветствия

	public $_bottom_menu; // нижнее меню

	public $_user; // авторизованный пользователь поле (тип user)
	public $__;// переменная ответственная за вывод основного контента сайта (тип content)

	function __construct($title) {
		if( !db::check_connect() )
		db::__construct();

		$this->_user = new user();

		if(!isset($_SESSION['id_user'])) {
			if( isset($_COOKIE['pass']) and !isset($_POST['pass']) )
			$this->_user->auth( $_COOKIE['user'], $_COOKIE['pass']);
		}
		// секция последних фотографий
		$sec = $this->last_photos();

		// 	секция популярных фото под последними фото
		$sec->merge($this->pop_photos());

		$this->__ = new content($sec, sh_mainpage_spacers());

		$this->_title = $title;
		$this->_top = sh_top();
		$this->_bottom = sh_bottom();
		$this->_copyright = sh_copyright();
		$this->_left = '';

		$this->pop_tag();
		$this->_auth_box = sh_user_auth_form();
		$this->_bottom_menu = '&nbsp;';
		$this->shortnews(); // список новостей слева
		$this->pop_groups(); // популярные группы снизу слева
	}

	//------------------- Администрирование -----------------------

	// функция позволяет суперпользователю удалять нежелательные элементы (фото, группы) с главной страницы сайта
	function main_page_remove_add($id, $table, $rem_add) {
		$res = $this->q("SELECT `rights` FROM `user` WHERE `id_user`=".$_SESSION['id_user']);
		$row = mysql_fetch_assoc($res);
		if($row['rights'] == 100 ) {
			if($rem_add == 'remove') $main_page = "no";
			elseif ($rem_add == 'add') $main_page = "yes";

			$this->q("UPDATE `".$table."` SET `main_page`='".$main_page."' WHERE `id_".$table."`=".$id);
		}
	}

	//
	function publish_ban_unban($id_user, $mode) {
		if($_SESSION['rights'] == 100) {
			if($mode == 'ban') $set = "yes";
			elseif ($mode == 'unban') $set = "no";

			$this->q("UPDATE `user` SET `pb_ban`= '$set' WHERE `id_user`=".$id_user);
		}
	}

	function publishtag_rem_add($id_tag, $mode) {
		if($_SESSION['rights'] == 100) {
			if($mode == 'rem') $set = "no";
			elseif ($mode == 'add') $set = "yes";

			$this->q("UPDATE `tag` SET `main_page`= '$set' WHERE `id_tag`=".$id_tag);
		}
	}

	//------------------------------

	// Список друзей с формой для добавления
	// Две моды работы: list (список), add (режим добавления)
	function friends($id_user, $mode) {
		if($id_user == $_SESSION['id_user']) {

			if($mode == 'add') {
				$title = "<span class=header1>Добавление друзей</span>";

				$user_res = $this->q("SELECT `name`, `id_user` FROM `user` WHERE `id_user` != $id_user ORDER BY `name` ASC");
				if(mysql_num_rows($user_res)) {
					$row = mysql_fetch_assoc($user_res);

					// ограничиваем видимую часть списка 20ю записями
					$size = ( mysql_num_rows($user_res) > 20 ) ? 20 : mysql_num_rows($user_res);
					$friend_str_sel = '<select name="friend_select[]" size="'.$size.'" multiple>
									   <option value="'.$row['id_user'].'">'.$row['name'].'&nbsp;</option>';

					while ($row = mysql_fetch_assoc($user_res)) {
						$friend_str_sel .= 	'<option value="'.$row['id_user'].'">'.$row['name'].'&nbsp;&nbsp;</option>';
					}
					$friend_str_sel .= '</select>';
					$add_form = '<form action="handler.php" method="post">
              				<p>'.$friend_str_sel.'&nbsp;&nbsp;
              				<input name="id_user" type="hidden" id="handle" value='.$id_user.'>              
              				<input name="handle" type="hidden" id="handle" value="add_friend">
              				<input name="Submit2" type="submit" class="bigbtn" value="Добавить">
            				</p></form>';				
				}
			} else {
				$add_form = '&nbsp;';
			}

			if($mode == 'list') {
				$title = "<span class=header1>Список друзей</span>";
				$friend_res = $this->q("SELECT `user`.`name` 'frnd', `id_friend` FROM `friends`
										LEFT JOIN `user` ON `user`.`id_user`=`friends`.`id_friend` WHERE `id_author`=".$id_user);

				if(mysql_num_rows($friend_res)) {
					$row2 = mysql_fetch_assoc($friend_res);
					$friend_str = '<a href="index.php?go=profile&id_user='.$row2['id_friend'].'" class="user">'.$row2['frnd'].'</a>
					&nbsp;&nbsp; <a href="index.php?go=del_friend&id_author='.$id_user.'&id_friend='.$row2['id_friend'].'" class="link">
					Удалить из друзей</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="index.php?go=usrsphoto&id_user='.$row2['id_friend'].'" class="link">Фото</a><br>';

					while ( $row2 = mysql_fetch_assoc($friend_res) ) {
						$friend_str .= '<a href="index.php?go=profile&id_user='.$row2['id_friend'].'" class="user">'.$row2['frnd'].'
							</a>'.'&nbsp;&nbsp; <a href="index.php?go=del_friend&id_author='.$id_user.'&id_friend='.$row2['id_friend'].'" 
							class="link">Удалить из друзей</a>&nbsp;&nbsp;&nbsp;&nbsp;
							<a href="index.php?go=usrsphoto&id_user='.$row2['id_friend'].'" class="link">Фото</a><br>';
					}
				} else {
					$friend_str = '<span class=text>Друзья не добавлены</span>';
				}
			} else
			$friend_str = '&nbsp;';

			$friends_sec = new section( $title, 2, 2, 1,
			sh_friends($id_user, $friend_str, $add_form) );

			$friends = new content($friends_sec);
			$this->set_content($friends);
		}
	}

	// форма восстановление пароля
	function pass_recover($error) {

		if($error != null ) $error = '<div align=left>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color="#FF0000" class="formcomm">'.$error.'</font></div><br>';

		$form = $error.'Введите имя <br>
              <br><form action="handler.php" method="post" >
              <input name="name" type="text" id="name" size="15">
              <br>
              <br>
              или email <br>
              <br>
              <input name="email" type="text" id="email" size="20">
              <br>
              <br><input name="handle" type="hidden" id="handle" value="passrec">
              <input name="send" type="submit"  class="bigbtn" id="send" value="Послать">
			  </form>';

		$passrec_sec = new section("<span class=header1>Восстановление пароля</span>", 2, 2, 1, sh_out($form));
		$passrec = new content($passrec_sec);
		$this->set_content($passrec);
	}

	// отправка пароля
	function pass_send($name, $email) {
		if( !empty($email) ) $where = "`email`='".strip_tags($email)."'";

		if( !empty($name) ) $where = "`name`='".strip_tags($name)."'";

		$res = $this->q("SELECT `id_user`, `name` ,`email`, `pass` FROM `user` WHERE ".$where);
		$row = mysql_fetch_assoc($res);

		if($row) {
			$new_pass = mt_rand(); // случайно сгенерированный пароль
			$this->q("UPDATE `user` SET `pass`='".md5($new_pass)."' WHERE `id_user`=".$row['id_user']);
			$e_mail =  $row['email'];
			$theme = "Password delivery";
			$msg = "Здравствуйте ".$row['name'].",\r\nНовый пароль: ".$new_pass."
			\r\nИзмените пароль у себя в профиле\r\n\r\n--\r\nПочтовый робот (не отвечайте на это сообщение)";
			$sender = "From: passdeliver@".$_SERVER['SERVER_NAME'];

			if( mail($e_mail, $theme, $msg, $sender) )
			return 1;
			else
			return "Ошибка отправки";
		} else
		return "Учетная запись с введенными данными не найдена";
	}

	function contacts() {
		$text = '<p class=text>Телефон кол-центра <span class=cin>&nbsp;&raquo;&nbsp;</span> 490-555</p>
				<p class=text>Телефон круглосуточной технической поддержки <span class=cin>&nbsp;&raquo;&nbsp;</span> 40-28-74</p>
				<p class=text>Электронный адрес <span class=cin>&nbsp;&raquo;&nbsp;</span> <a href="mailto:info@flogr.ru" class=link>info@flogr.ru</a></p>';

		$cont_sec = new section("<span class=header1>Контакты</span>", 2, 2, 1, sh_out($text, 'left'));
		$cont = new content($cont_sec);
		$this->set_content($cont);
	}

	// CAPTCHA: Completely Automated Public Turing test to tell Computers and Humans Apart
	function captcha() {
		if (!$image = @imagecreatefromgif('images/captcha.gif')) {
			$image = imagecreatetruecolor(88, 31);

			$backgroundcolor = imagecolorallocate($image, 255, 255, 255);
			imagefill($image, 0, 0, $backgroundcolor);
		}

		$fontcolor = imagecolorallocate($image, 0, 0, 0);

		$one = mt_rand(0, 9);
		$two = mt_rand(0, 9);
		$three = mt_rand(0, 9);
		$four = mt_rand(0, 9);

		$_SESSION['captcha'] = $one.$two.$three.$four;
		imagestring($image, 25, 10, mt_rand(2, 18), $one, $fontcolor);
		imagestring($image, 25, 30, mt_rand(2, 18), $two, $fontcolor);
		imagestring($image, 25, 50, mt_rand(2, 18), $three, $fontcolor);
		imagestring($image, 25, 70, mt_rand(2, 18), $four, $fontcolor);

		imagesetthickness($image, 3);
		imageline($image, 88, 0, 0, mt_rand(10, 30), $fontcolor);

		imagesetthickness($image, 2);
		imageline($image, 0, 0, 88, mt_rand(5, 30), $fontcolor);

		imagesetthickness($image, 1);
		imageline($image, 45, 30, mt_rand(0, 88), 0, $fontcolor);
		imageline($image, 88, 30, mt_rand(0, 50), 0, $fontcolor);
		imageline($image, 0, 30, mt_rand(45, 88), 0, $fontcolor);

		header("Content-type: image/gif");
		imagegif($image);

	}

	// Удалить из друзей
	function del_friend($id_author, $id_friend) {
		if($id_author == $_SESSION['id_user']) {
			if(
			$this->q("DELETE FROM `friends` WHERE `id_friend`=".$id_friend." AND `id_author`=".$id_author)
			) {
				/*
				if( strpos( $_SERVER['HTTP_REFERER'], 'go=fr_add') )
				$go = 'index.php?go=fr_list&id_user='.$_SESSION['id_user'];
				else
				$go = $_SERVER['HTTP_REFERER'];
				*/
				$go = $_SERVER['HTTP_REFERER'];
				header('location:'.$go); exit;
			}
		}
	}

	function allow_comment($id_user) {
		$this->q("DELETE FROM `ban_comment` WHERE `id_author`=".$_SESSION['id_user']." AND `id_user`=".$id_user);
	}

	function forbid_comment($id_user) {
		$this->q("INSERT INTO `ban_comment`(`id_author`, `id_user`) VALUES (".$_SESSION['id_user'].", ".$id_user.")");
	}

	// Добавить в друзья
	function add_friend($id_user, $friends) {

		foreach ($friends as $ind => $frnd)	{
			$res = $this->q("SELECT * FROM `friends` WHERE `id_author`=$id_user AND `id_friend`=$frnd");
			if( !mysql_num_rows($res) )
			$friends[$ind] = '('.$id_user.', '.$frnd.')';
			else
			unset($friends[$ind]);
		}

		$insert = implode(', ', $friends);
		if( !empty($insert) )
		$this->q("INSERT INTO `friends` VALUES ".$insert);

		if( strpos( $_SERVER['HTTP_REFERER'], 'go=fr_add') )
		$go = 'index.php?go=fr_list&id_user='.$_SESSION['id_user'];
		else
		$go = $_SERVER['HTTP_REFERER'];

		header('location:'.$go); exit;
		//header('location:index.php?go=fr_list&id_user='.$_SESSION['id_user']); exit;
	}


	//--------------------------------------- GROUP -----------------------------------------------------------------------

	// Проверка на членство в группе
	function membership($id_group) {
		if($_SESSION['id_user']) {
			$mem_ship = ( mysql_num_rows($this->q("SELECT * FROM `group_member` WHERE `id_user`=".$_SESSION['id_user']." AND `id_group`=".$id_group." AND `connected` LIKE 'yes'")) or
			mysql_num_rows($this->q("SELECT * FROM `group` WHERE `id_author`=".$_SESSION['id_user']." AND `id_group`=".$id_group)) );
		} else {
			$mem_ship = false;
		}
		return $mem_ship;
	}

	function del_posts($id_discuss) {
		$this->q("DELETE FROM `post` WHERE `id_discuss`=".$id_discuss);
	}

	function del_post($id_post) {
		$res = $this->q("SELECT `discuss`.`id_discuss` 'id_discuss', `group`.`id_author` 'id_author'
						 FROM `post`, `discuss`, `group` 
						 WHERE `id_post`=".$id_post." AND `post`.`id_discuss`=`discuss`.`id_discuss`
						 AND `discuss`.`id_group` =`group`.`id_group`");

		$row = mysql_fetch_assoc($res);

		// удаление постов разрешено пользователям с правами в группе выше
		// 50 (модератор, администратор) либо суперпользователю
		settype($_SESSION['gr_rights'], 'integer');
		if( $_SESSION['gr_rights'] > 49 or $_SESSION['rights'] == 100 ) {
			$this->q("DELETE FROM `post` WHERE `id_post`=".$id_post);
			$this->del_dis_comments($id_post);
		}
		return $row['id_discuss'];
	}

	function del_dis_comments($id_post) {
		$this->q("DELETE FROM `dis_comment` WHERE `id_post`=".$id_post);
	}

	function del_dis_comment($id_comment) {
		$res = $this->q("SELECT `discuss`.`id_discuss` 'id_discuss', `group`.`id_author` 'id_author', `group`.`id_group` 'id_group'
						 FROM `dis_comment`, `post`, `discuss`, `group` 
						 WHERE `id_dis_comment`=".$id_comment." 
						 AND `dis_comment`.`id_post`=`post`.`id_post` AND `post`.`id_discuss`=`discuss`.`id_discuss`
						 AND `discuss`.`id_group` =`group`.`id_group`");		
		$row = mysql_fetch_assoc($res);

		if($row['id_author'] == $_SESSION['id_user'] or $_SESSION['rights']==100 or $_SESSION['gr_rights'] > 49)
		$this->q("DELETE FROM `dis_comment` WHERE `id_dis_comment`=".$id_comment);

		return $row['id_discuss'];
	}

	function del_comment($id_comment) {
		$res = $this->q("SELECT `photo`.`id_user` 'id_author', `comment`.`id_photo` 'id_photo'
						 FROM `comment`, `photo` 
						 WHERE `id_comment`=".$id_comment." AND `comment`.`id_photo`=`photo`.`id_photo`");		

		$row = mysql_fetch_assoc($res);

		if($row['id_author'] == $_SESSION['id_user'] or $_SESSION['rights']==100)
		$this->q("DELETE FROM `comment` WHERE `id_comment`=".$id_comment);

		return $row['id_photo'];
	}


	// $discusses is an array of id_discuss
	function del_discuss($discusses) {
		if(isset($discusses)) {
			$str_discusses = implode(", ", $discusses);

			foreach ($discusses as $id_discuss) {
				$res = $this->q("SELECT `id_post` FROM `post` WHERE `id_discuss`=".$id_discuss);

				while( $row = mysql_fetch_assoc($res) ) {
					$this->del_dis_comments($row['id_post']);
				}

				$this->del_posts($id_discuss);
			}
			$this->q("DELETE FROM `discuss` WHERE `id_discuss` IN (".$str_discusses.")");
			$res = 1;
		} else {
			$res = "Не выбранны дисскуссии";
		}

		return $res;
	}

	// права в группе
	function group_rights($id_group) {
		if(isset($_SESSION['id_user'])) {
			$rights = mysql_fetch_assoc($this->q("SELECT `rights` 'gr_rights' FROM `group_member`
												  WHERE `id_user`=".$_SESSION['id_user']." AND `id_group`=".$id_group));
			$_SESSION['gr_rights'] = $rights['gr_rights'];
			return $rights;
		} else
		return null;
	}

	// Отображение группы, а также дисскуссий к ней
	function group($id_group, $error='') {

		$header = "<span class=header1>Группа</span><span class=cin>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</span><a href='index.php?go=add_group' class=link>добавить</a>";

		$res = $this->q("SELECT `title`, `img`, `descr`, `type`, `user`.`name` 'author', `id_author`
						 FROM `group`, `user` 
						 WHERE `group`.`id_group`=".$id_group." AND `user`.`id_user`=`group`.`id_author`");

		$row = mysql_fetch_assoc($res);

		$rights = $this->group_rights($id_group);

		$auth = ( isset($_SESSION['id_user']) and $_SESSION['id_user'] == $row['id_author'] ) ? true : false;

		if(isset($_SESSION['id_user']))
		$row2 = mysql_fetch_assoc($this->q("SELECT `connected` 'cnctd' FROM `group_member` WHERE `id_user`=".$_SESSION['id_user']." AND `id_group`=".$id_group) );

		// Определение статуса пользователя в группе и отображение соответствующего
		if(isset($_SESSION['id_user']) and $_SESSION['id_user'] != $row['id_author'] and $_SESSION['rights'] < 100) {
			// пользователь состоит в группе или забанен
			if($row2['cnctd'] == 'yes' or $row2['cnctd'] == 'ban') {
				$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span><a href='index.php?go=delme&id_group=".$id_group."' class=link>выйти из группы</a>";
				if($row2['cnctd'] == 'ban') $addme .= "<br><font color=#FF0000 class=formcomm>Ваши права в группе были ограничены администратором группы</font>";
			} elseif ($row2['cnctd'] == 'no') // пользователю отказано в участии
			$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span>
				<a href='index.php?go=addme&id_group=".$id_group."' class=link>присоединиться</a><span class=cin>&nbsp;&raquo;&nbsp;</span><span class=text>отказано :'(</span>";
			elseif( $row2['cnctd'] == 'addme') // запрос пользователя на добавление в группу (в случае типов private, public_reg)
			$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span><span class=text>Вас подключают...</span>";
			elseif ( $row2['cnctd'] == null or $row2['cnctd'] == 'del') // пользователь не подключен либо удален
			$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span><a href='index.php?go=addme&id_group=".$id_group."' class=link>присоединиться</a>";
		} else
		$addme = '';


		$image = ($row['img'] != NULL) ? "<a href='index.php?go=group&id_group=".$id_group."'><img src='images/".$row['img']."' border=0></a>"
		: "<a href='index.php?go=group&id_group=".$id_group."'><img src='images/im_group.gif' border=0></a>";

		$group = $image."&nbsp;&nbsp;<span class=header1>".$row['title']."</span>".$addme;

		if($auth) $group .= " <span class=cin>&nbsp;&raquo;&nbsp;</span> <a href='index.php?go=edit_group&id_group=".$id_group."' class=link >редактировать</a>";

		$group .= "<br>
		<a href='index.php?go=members&id_group=".$id_group."' class=group_btm>участники</a><br>";

		if( $auth or ( isset($_SESSION['rights']) and ($_SESSION['rights'] == 100 or
		mysql_num_rows($this->q("SELECT `id_group` FROM `group_member`
									  WHERE `id_group`=".$id_group." AND `id_user`=".$_SESSION['id_user']." AND `connected`='yes'")) ) )		 
									  ) {
									  	$res = $this->q("SELECT `photo`.`id_photo` 'id_photo', `id_user`
						 	 FROM `group_photo`
						 	 LEFT JOIN `photo` ON `photo`.`id_photo`=`group_photo`.`id_photo`
						 	 WHERE `id_group`=".$id_group." AND `id_user`=".$_SESSION['id_user']);


									  	if( isset($_SESSION['id_user']) and  !mysql_num_rows( $this->q("
						SELECT `photo`.`id_photo` 'id_photo', `id_user` 
			  			FROM `group_photo`
			  		 	LEFT JOIN `photo` ON `photo`.`id_photo`=`group_photo`.`id_photo`
			  		 	WHERE `id_group`=".$id_group." AND `id_user`=".$_SESSION['id_user']) ) 
			  		 	) {

			  		 		$group .= "<p class=formcomm>Добавить дискуссию нельзя. Добавьте фото в группу</p>";
			  		 		$add_disc = '<span class=cin>&nbsp;&raquo;&nbsp;</span><span class=text>добавить дискуссию</span>';
			  		 	} else {
			  		 		$add_disc = '<span class=cin>&nbsp;&raquo;&nbsp;</span>
				<a href="index.php?go=add_discuss&id_group='.$id_group.'" class=link>добавить дискуссию</a>'; 			
			  		 	}
									  } else {
									  	$group .= "<p class=formcomm>Вы не можете добавлять дискуссии</p>";
									  }

									  if( $row['type'] != 'private' or ($row['type'] == 'private' and $this->membership($id_group)) or ($_SESSION['rights'] == 100) ) {

									  	$dis_res = $this->q("SELECT `discuss`.`id_discuss` 'id_discuss', `title`, `name`, `discuss`.`id_author` 'id_author',
										`post`.`id_post` 'id_post', COUNT(`id_post`) AS 'posts', `last_post_date`, DATE_FORMAT(`last_post_date`, '<font size=1>%e.%m.%Y&nbsp;%k:%i:%s</font>') 'lp_date',
`last_id_user`
								 FROM `discuss` LEFT JOIN `user` ON `id_user`=`discuss`.`id_author`
								 LEFT JOIN `post` ON `post`.`id_discuss`=`discuss`.`id_discuss`
								 WHERE `id_group`=".$id_group." GROUP BY `discuss`.`id_discuss`
								 ORDER BY `last_post_date` DESC");		

									  	if(!empty($error))
									  	$error = '<font color="#FF0000" class="formcomm">'.$error.'<br></font>';

									  	$group .= '<br><table width="100%" border="0">
    	            <tr> 
        	          <td width="336" height="24" valign="bottom">'.$error.'<span class=header1>Дискуссии</span> 
            	        '.$add_disc.'</td>
                	  <td width="130" align="center" valign="bottom" class="help"> 
	                    автор</td>
    	              <td width="44" align="center" valign="bottom" class="help">постов</td>
    	              <td width="103" align="center" valign="bottom" class="help">последний пост</td>
        	        </tr><form action="handler.php" method="post">';

									  	// список дисскусси
									  	if(mysql_num_rows($dis_res)) {
									  		//do
									  		while ($row_dis = mysql_fetch_assoc($dis_res)) {
									  			$checkbox = $del_discuss = '';
									  			// управлять группами может автор группы, модератор и суперпользователь
									  			$group_rights = $_SESSION['gr_rights'];
									  			settype($group_rights, "integer");
									  			if($auth or $_SESSION['rights'] == 100 or $group_rights > 49) {
									  				$del_discuss = '<input name="del_discuss" type="submit" class="deldisc" id="del_discuss" value="удалить выделенные">';
									  				$checkbox = '<input name="disc[]" type="checkbox" value="'.$row_dis['id_discuss'].'">';
									  			}

									  			$row3 = mysql_fetch_assoc( $this->q("SELECT `name` FROM `user` WHERE `id_user`=".$row_dis['last_id_user']) );

									  			$lastpost = '';
									  			if($row_dis['last_id_user'] != 0)
									  			$lastpost = '<a href="index.php?go=discuss&id_discuss='.$row_dis['id_discuss'].'" class=link>'.$row3['name'].'</a>, <a href="index.php?go=discuss&id_discuss='.$row_dis['id_discuss'].'" class=albumlink>'.$row_dis['lp_date'].'</a>';

									  			$group .= '<tr>
                	  <td height="25" valign="middle">'.$checkbox.'
	                  <a href="index.php?go=discuss&id_discuss='.$row_dis['id_discuss'].'" class="link">'.$row_dis['title'].'</a></td>
    	              <td align="center" valign="middle" >
        	          <a href="index.php?go=profile&id_user='.$row_dis['id_author'].'" class=user>'.$row_dis['name'].'</a></td>
            	      <td align="center" valign="middle" class=text>'.$row_dis['posts'].'</td>
            	      <td align="center" valign="middle">'.$lastpost.'</td>
                	</tr>';
									  		}
									  	} else {
									  		$group .= '<tr>
            	      <td height="30" valign="middle" class=text>Нет дискуссий</td>
                	  <td align="center" valign="middle" >&nbsp;</td>
          	          <td align="center" valign="middle" class=text>&nbsp;</td>
          	          <td>&nbsp;</td>
            	    </tr>';
									  	}
									  	$group .= '<tr align="right">
                  		<td height="25" colspan="3" valign="bottom">'.$del_discuss.'</td>
                	</tr>
                	<input name="id_group" type="hidden" id="handle" value="'.$id_group.'">
                	<input name="handle" type="hidden" id="handle" value="del_discuss">
                </form></table>';
									  }

									  $group .= "<br><p class=header1>О группе</p>";
									  if(!empty($row['descr']) )
									  $group .= $row['descr'].".<br>";

									  switch ($row['type']) {
									  	case 'public':
									  		$group .= '<p class=formcomm><img src="images/public.gif" style="FLOAT: left;">&nbsp;Это публичная группа (без регистрации)</p>';
									  		break;

									  	case 'public_reg':
									  		$group .= '<p class=formcomm><img src="images/public_reg.gif" style="FLOAT: left;">&nbsp;Это публичная группа (с регистрацией)</p>';
									  		break;
									  	case 'private':
									  		$group .= '<p class=formcomm><img src="images/private.gif" style="FLOAT: left;">&nbsp;Это частная группа</p>';
									  		break;
									  }

									  $wait_list = "";
									  if($auth) {
									  	$wait_res = $this->q("SELECT `user`.`name` 'username', `user`.`id_user` 'id_user'
								  FROM `group_member` LEFT JOIN `user` ON `user`.`id_user`=`group_member`.`id_user`   
								  WHERE `id_group`=".$id_group." AND `connected` LIKE 'addme'");
									  	if(mysql_num_rows($wait_res)) {
									  		$wait_list .= "Ожидают подключения:<br>";
									  		$row_wait = mysql_fetch_assoc($wait_res);
									  		$wait_list .= "<a href='index.php?go=profile&id_user=".$row_wait['id_user']."' class=user>".$row_wait['username']."</a>  <span class=cin>&nbsp;&raquo;&nbsp;</span>
				<a href='index.php?go=add_mem&id_user=".$row_wait['id_user']."&id_group=".$id_group."' class=link>подключить</a>&nbsp;&nbsp;<a href='index.php?go=ref_mem&id_user=".$row_wait['id_user']."&id_group=".$id_group."' class=link>отказать</a>";
									  		while ($row_wait = mysql_fetch_assoc($wait_res)) {
									  			$wait_list .= ",<br><a href='index.php?go=profile&id_user=".$row_wait['id_user']."' class=user>".$row_wait['username']."</a> <span class=cin>&nbsp;&raquo;&nbsp;</span>
					<a href='index.php?go=add_mem&id_user=".$row_wait['id_user']."&id_group=".$id_group."' class=link>подключить</a>&nbsp;&nbsp;<a href='index.php?go=ref_mem&id_user=".$row_wait['id_user']."&id_group=".$id_group."' class=link>отказать</a>";
									  		}
									  	}
									  }

									  $group .= "<p><br><br>".$wait_list."</p>";

									  $group_sec = new section($header, 2, 2, 1, sh_out($group, 'left'));

									  $group = new content($group_sec);

									  $this->set_content($group);
	}

	// форма добавления дисскусси
	function add_discuss_form($id_group, $posttitle='', $imradio=0, $posttext='', $error='') {
		if( $_SESSION['rights']==100 or $this->membership($id_group)) {
			$header = "<span class=header1>Добавление дискуссии</span>";

			$res = $this->q("SELECT `photo`.`id_photo` 'id_photo', `id_user`
							 FROM `group_photo`
							 LEFT JOIN `photo` ON `photo`.`id_photo`=`group_photo`.`id_photo`
							 WHERE `id_group`=".$id_group." AND `id_user`=".$_SESSION['id_user']);
			$photos = '';
			while ($row = mysql_fetch_assoc($res)) {
				if($row['id_photo'] == $imradio)
				$checked = 'checked';
				else
				$checked = '';

				$photos .= '<img src="index.php?go=photo_small&photo_num='.$row['id_photo'].'&id_group='.$id_group.'" width="100" height="100"><input type="radio" name="imradio" value="'.$row['id_photo'].'" '.$checked.'>&nbsp;&nbsp;';
			}

			$res2 = $this->q("SELECT `group`.`title` 'grp_title'
							  FROM `group` 
							  WHERE `id_group`=".$id_group);
			$row_dis = mysql_fetch_assoc($res2);

			$header2 = '<tr>
          					<td width="24"><p></p></td>
          					<td height=25 colspan="6" valign="middle">
          					<a href="index.php?go=group&id_group='.$id_group.'" class=link title="группа">'.$row_dis['grp_title'].'</a></td>
        				</tr>
        				<tr> 
          					<td width="24"><p></p></td>';

			$form_sec = new section($header, 5, 2, 1, $header2.sh_discuss_from($photos, $id_group, $posttitle, $imradio, $posttext, $error));

			$form = new content($form_sec);

			$this->set_content($form);
		}
	}

	// добавление дискуссии
	function add_discuss($posttitle, $id_photo, $posttext, $id_group, $id_discuss) {
		$error = '';
		if( !isset($id_discuss) ) {
			if(empty($posttitle))
			$error .= "Заголовок не добавлен<br>";
		}
		if( empty($posttext) )
		$error .= "Пустое сообщение<br>";

		if( !isset($id_photo) )
		$error .= "Фото не выбрано<br>";

		if( $error == '') {
			$new_discuss = !isset($id_discuss);
			if( $new_discuss ) {
				$this->q("INSERT INTO `discuss` (`id_group`, `id_author`, `title`, `last_post_date`, `last_id_user`) VALUES ($id_group, ".$_SESSION['id_user'].", '".addslashes($posttitle)."', NOW(), ".$_SESSION['id_user'].")");
				$id_discuss = mysql_insert_id();
			} else
			$this->q("UPDATE `discuss` SET `last_post_date`=NOW(), `last_id_user`=".$_SESSION['id_user']." WHERE `id_discuss`=".$id_discuss);

			$this->q("INSERT INTO `post` (`posttext`, `id_photo`, `id_author`, `id_discuss`)
					  VALUES ('".addslashes($posttext)."', $id_photo, ".$_SESSION['id_user'].", $id_discuss )");				

			return 1;
		} else
		return $error;
	}

	// форма добавления поста
	function add_dispost_form($id_discuss, $id_group, $imradio=0, $posttext='', $error='') {

		if(isset($_SESSION['id_user'])) {
			$mem = mysql_num_rows($this->q("SELECT `id_user` FROM `group_member` WHERE `id_user`=".$_SESSION['id_user']." AND `connected`='yes' AND `id_group`=".$id_group));
			$auth = mysql_num_rows($this->q("SELECT `id_group` FROM `group` WHERE `id_author`=".$_SESSION['id_user']." AND `id_group`=".$id_group));
			$su = ($_SESSION['rights'] == 100) ? true : false;
			if( $mem or $auth or $su) {
				$header = "<span class=header1>Добавление поста</span>";

				// Фотографии добавленные в группу
				$res = $this->q("SELECT `photo`.`id_photo` 'id_photo'
								 FROM `group_photo`
								 LEFT JOIN `photo` ON `photo`.`id_photo`=`group_photo`.`id_photo`
						 		 WHERE `id_group`=".$id_group." AND `id_user`=".$_SESSION['id_user']);
				$photos = '';
				while ( $row = mysql_fetch_assoc($res) ) {
					if($row['id_photo'] == $imradio)
					$checked = 'checked';
					else
					$checked = '';

					$photos .= '<img src="index.php?go=photo_small&photo_num='.$row['id_photo'].'" width="100" height="100">
					<input type="radio" name="imradio" value="'.$row['id_photo'].'" '.$checked.'>&nbsp;&nbsp;';
				}

				$res2 = $this->q("SELECT `group`.`title` 'grp_title', `discuss`.`title` 'dsc_title'
								  FROM `discuss`, `group` 
								  WHERE `id_discuss`=".$id_discuss." AND `discuss`.`id_group`=`group`.`id_group`");
				$row_post = mysql_fetch_assoc($res2);

				$header2 = '<tr>
          						<td width="24"><p></p></td>
          						<td height=25 colspan="6" valign="middle"><a href="index.php?go=group&id_group='.$id_group.'" class=link title="группа">'.$row_post['grp_title'].'</a>
						 <span class=cin>&nbsp;&raquo;&nbsp;</span> <a href="index.php?go=discuss&id_discuss='.$id_discuss.'" class=link title="дискуссия">'.$row_post['dsc_title'].'</a></td>
        					</tr>
        					<tr> 
          						<td width="24"><p></p></td>';

				$form_sec = new section($header, 5, 2, 1, $header2.sh_discuss_from($photos, $id_group, "", $imradio, $posttext, $error, $id_discuss));

				$form = new content($form_sec);

				$this->set_content($form);
			} else
			header('location:enter.php');

		} else
		header('location:enter.php');
	}

	// Список участников
	function members($id_group) {
		/*
		$admin_res = $this->q("SELECT `user`.`id_user` 'id_user', `user`.`name` 'author', `title` FROM `group`
		LEFT JOIN `user` ON `user`.`id_user`=`group`.`id_author` WHERE `id_group`=".$id_group);
		$row_admin = mysql_fetch_assoc($admin_res);
		$users = "<p><a href='index.php?go=profile&id_user=".$row_admin['id_user']."' class=user>".$row_admin['author']."</a> <span class=cin>&nbsp;&raquo;&nbsp;</span> <span class=formcomm>админ</span></p>";
		*/
		$users = "";
		$res = $this->q("SELECT `user`.`id_user` 'id_user', `user`.`name` 'user', `group_member`.`rights` 'grouprights',
								`connected`, `id_author`, `title`, `img` 
						 FROM `group_member`, `user`, `group`
						 WHERE `group_member`.`id_group`=".$id_group." AND ( `connected`='yes' OR `connected`='ban') 
						 		AND `group_member`.`id_user`=`user`.`id_user` AND `group_member`.`id_group`=`group`.`id_group` 
						 ORDER BY `group_member`.`rights` DESC");

		if( isset($_SESSION['id_user']) ) {
			$res2 = $this->q("SELECT * FROM `group_member`
						  	  WHERE `id_user`=".$_SESSION['id_user']." AND `id_group`=".$id_group." AND `rights`=100");
			$ban_auth = mysql_num_rows($res2);
		}

		if($row = mysql_fetch_assoc($res)) {
			$image = ($row['img'] != NULL) ? "<a href='index.php?go=group&id_group=".$id_group."'><img src='images/".$row['img']."' border=0></a>"
			: "<a href='index.php?go=group&id_group=".$id_group."'><img src='images/im_group.gif' border=0></a>";
			$group = $image."&nbsp;&nbsp;<span class=header1>".$row['title']."</span>";

			if($_SESSION['id_user'] == $row['id_author']) $group .= " <span class=cin>&nbsp;&raquo;&nbsp;</span> <a href='index.php?go=edit_group&id_group=".$id_group."' class=link >редактировать</a>";

			do {
				$ban_user = "";
				$style = 'user';

				if($ban_auth) { // если админ то предоставляется функционал для работы с участниками группы
					switch ($row['connected']) {
						case 'yes':
							$ban_user = " <span class=cin>&nbsp;&raquo;&nbsp;</span>
							<a href='handler.php?handle=ban_user&id_user=".$row['id_user']."&id_group=".$id_group."' class=exit>выключить</a>";					
							if($_SESSION['id_user'] == $row['id_author'] and $row['grouprights'] == 1)
							$ban_user .= " <span class=cin>&nbsp;&raquo;&nbsp;</span>
								<a href='handler.php?handle=grnt_md_rght&id_user=".$row['id_user']."&id_group=".$id_group."' class=link>назначить модератором</a>";

							break;
						case 'ban':
							$ban_user = " <span class=cin>&nbsp;&raquo;&nbsp;</span>
							<a href='handler.php?handle=unban_user&id_user=".$row['id_user']."&id_group=".$id_group."' class=link>включить</a>";
							if($_SESSION['id_user'] == $row['id_author'] and $row['grouprights'] == 1)
							$ban_user .= " <span class=cin>&nbsp;&raquo;&nbsp;</span>
								<a href='handler.php?handle=grnt_md_rght&id_user=".$row['id_user']."&id_group=".$id_group."' class=link>назначить модератором</a>";							
							$style = 'banuser';
							break;
					}
				}
				$users .= "<p><a href='index.php?go=profile&id_user=".$row['id_user']."' class=".$style.">".$row['user']."</a>";

				// отображение статуса участника
				if($row['grouprights'] == 100)
				$users .= " <span class=cin>&nbsp;&raquo;&nbsp;</span> <span class=formcomm>админ</span></p>";
				elseif($row['grouprights'] == 50) {

					if($_SESSION['id_user'] == $row['id_author'] )
					$grant_right = "<span class=cin>&nbsp;&raquo;&nbsp;</span> <a href='handler.php?handle=ungrnt_md_rght&id_user=".$row['id_user']."&id_group=".$id_group."' class=link>освободить</a>";

					$users .= " <span class=cin>&nbsp;&raquo;&nbsp;</span> <span class=formcomm>модератор</span>".$grant_right."</p>";
				} else
				$users .= $ban_user."</p>";

			} while ($row = mysql_fetch_assoc($res));
		}

		$title = "<span class=header1>Участники группы</span>";
		$group_user_sec = new section($title, 2, 2, 1, sh_out($group.$users, 'left'));

		$group_user = new content($group_user_sec);

		$this->set_content($group_user);
	}

	// бан/снятие бана
	function ban_unban_user($id_user, $id_group, $mode) {

		// Проверка на администратора
		$res = $this->q("SELECT * FROM `group_member`
						 WHERE `id_user`=".$_SESSION['id_user']." AND `id_group`=".$id_group." AND `rights`=100");

		if(mysql_num_rows($res)) {
			if($mode == 'ban') {
				$this->q("UPDATE `group_member` SET `connected`='ban' WHERE `id_user`=".$id_user." AND `id_group`=".$id_group);
			} elseif ($mode = 'unban') {
				$this->q("UPDATE `group_member` SET `connected`='yes' WHERE `id_user`=".$id_user." AND `id_group`=".$id_group);
			}
		}
	}

	// страница с группами
	// структурно разделяется на "мои группы" и группы в которых "я участник"
	function groups() {
		$title = "<span class=header1>Группы</span> <span class=cin>&nbsp;&raquo;&nbsp;</span> <a href='index.php?go=all_groups' class=link>все группы</a>";

		//------------------------------ блок моих групп -----------------------------------------
		if( isset($_SESSION['id_user']) ) {
			$groups = "<p><span class=cur>Мои группы</span><span class=cin>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</span><a href='index.php?go=add_group' class=link>добавить группу</a></p>";

			$res = $this->q("SELECT `group`.`id_group` 'id_group', `type`, `title`, `descr`, `img`, `main_page`
							 FROM `group`
							 WHERE `id_author`=".$_SESSION['id_user']);

			if(mysql_num_rows($res)) {
				while ($row = mysql_fetch_assoc($res)) {
					$image = ($row['img'] != NULL) ? "<a href='index.php?go=group&id_group=".$row['id_group']."' class=link><img src='images/".$row['img']."' border=0></a>"
					: "<a href='index.php?go=group&id_group=".$row['id_group']."' class=link><img src='images/im_group.gif' border=0></a>";

					$mems = mysql_num_rows( $this->q("SELECT * FROM `group_member` WHERE `id_group`=".$row['id_group']." AND (`connected`='yes' OR `connected`='ban')") );

					$wait_res = $this->q("SELECT `user`.`name` 'username', `user`.`id_user` 'id_user'
										  FROM `group_member` LEFT JOIN `user` ON `user`.`id_user`=`group_member`.`id_user`   
										  WHERE `id_group`=".$row['id_group']." AND `connected` LIKE 'addme'");
					if(mysql_num_rows($wait_res)) {
						$wait_list .= "Ожидают подключения:<br>";
						$row_wait = mysql_fetch_assoc($wait_res);
						$wait_list .= "<a href='index.php?go=profile&id_user=".$row_wait['id_user']."' class=user>".$row_wait['username']."</a>  <span class=cin>&nbsp;&raquo;&nbsp;</span>
						<a href='index.php?go=add_mem&id_user=".$row_wait['id_user']."&id_group=".$row['id_group']."' class=link>подключить</a>&nbsp;&nbsp;<a href='index.php?go=ref_mem&id_user=".$row_wait['id_user']."&id_group=".$row['id_group']."' class=link>отказать</a>";
						while ($row_wait = mysql_fetch_assoc($wait_res)) {
							$wait_list .= ",<br><a href='index.php?go=profile&id_user=".$row_wait['id_user']."' class=user>".$row_wait['username']."</a> <span class=cin>&nbsp;&raquo;&nbsp;</span>
						<a href='index.php?go=add_mem&id_user=".$row_wait['id_user']."&id_group=".$row['id_group']."' class=link>подключить</a>&nbsp;&nbsp;<a href='index.php?go=ref_mem&id_user=".$row_wait['id_user']."&id_group=".$row['id_group']."' class=link>отказать</a>";
						}
					} else {
						$wait_list = "";
					}

					// Так как пост содержит одно фото, то количество постов равно кол-ву фото плюс группировка по `id_photo`
					$num_photo = mysql_num_rows($this->q("SELECT `id_photo` FROM `post`, `discuss`
														  WHERE `discuss`.`id_group`=".$row['id_group']." AND `discuss`.`id_discuss`=`post`.`id_discuss`
														  GROUP BY `id_photo`"));

					switch ($row['type']) {
						case 'public':
							$type = '<img src="images/public.gif" style="FLOAT: left;">&nbsp;<span class=formcomm>Это публичная группа (без регистрации)</span>';
							break;

						case 'public_reg':
							$type = '<img src="images/public_reg.gif"style="FLOAT: left;">&nbsp;<span class=formcomm>Это публичная группа (с регистрацией)</span>';
							break;

						case 'private':
							$type = '<img src="images/private.gif"style="FLOAT: left;">&nbsp;<span class=formcomm>Это частная группа</span>';
							break;
					}

					$descr = (!empty($row['descr'])) ? "<span class=text>".$row['descr']."</span><br><br>" : "<span class='nodescr'>Нет описания</span><br><br>";
					$info = "<a href='index.php?go=members&id_group=".$row['id_group']."' class=group_btm>участников ($mems)</a> <span class=date>|</span>
					<span class=date>фото ($num_photo)</span>";

					$mainpage = '';
					if($_SESSION['rights'] == 100)
					if($row['main_page'] == 'yes')
					$mainpage = '&nbsp;&nbsp;&nbsp;<a href="handler.php?handle=mn_page&id_group='.$row['id_group'].'&rem_add=remove"><img src="images/remove.gif" width="17" height="17" border="0" title="убрать с главной"></a>';
					elseif($row['main_page'] == 'no')
					$mainpage = '&nbsp;&nbsp;&nbsp;<a href="handler.php?handle=mn_page&id_group='.$row['id_group'].'&rem_add=add"><img src="images/add.gif" width="17" height="17" border="0" title="добавить на главную"></a>';

					$groups .= "<p>$image&nbsp;&nbsp;<a href='index.php?go=group&id_group=".$row['id_group']."' class=grouplink>".$row['title']."</a> $mainpage</p>$type<br><br>$descr$info<br><br><br><br>".$wait_list."<hr>";
				}
			} else {
				$groups .= "<p class=formcomm>Группы не добавлены</p>";
			}
			//------------------------------------------------------------------------------------

			//--------------------------- Участник в группах -------------------------------------
			$groups .= "<br><br><p><span class=cur>Состою в группах</span></p>";

			$in_groups_res = $this->q("SELECT `type`, `title`, `group_member`.`id_group` 'id_group', `img`, `descr`, `connected`, `main_page`
									   FROM `group_member`
									   LEFT JOIN `group` ON `group`.`id_group`= `group_member`.`id_group`
								   	   WHERE `id_user`=".$_SESSION['id_user']." AND (`connected`='yes' OR `connected`='ban') AND `rights` < 100");			

			if(mysql_num_rows($in_groups_res)) {
				while ($row = mysql_fetch_assoc($in_groups_res)) {
					$image = ($row['img'] != NULL) ? "<a href='index.php?go=group&id_group=".$row['id_group']."' class=link><img src='images/".$row['img']."' border=0></a>"
					: "<a href='index.php?go=group&id_group=".$row['id_group']."' class=link><img src='images/im_group.gif' border=0></a>";


					$delme = "<span class=cin>&nbsp;&raquo;&nbsp;</span><a href='index.php?go=delme&id_group=".$row['id_group']."' class=link>выйти</a>";

					$mems = mysql_num_rows( $this->q("SELECT * FROM `group_member` WHERE `id_group`=".$row['id_group']) );
					// Так как пост содержит одно фото, то количество постов равно кол-ву фото плюс группировка по `id_photo`
					$num_photo = mysql_num_rows($this->q("SELECT `id_photo` FROM `post`, `discuss`
														  WHERE `discuss`.`id_group`=".$row['id_group']." AND `discuss`.`id_discuss`=`post`.`id_discuss`
														  GROUP BY `id_photo`"));

					switch ($row['type']) {
						case 'public':
							$type = '<img src="images/public.gif" style="FLOAT: left;">&nbsp;<span class=formcomm>Это публичная группа (без регистрации)</span>';
					break;

	case 'public_reg':
		$type = '<img src="images/public_reg.gif"style="FLOAT: left;">&nbsp;<span class=formcomm>Это публичная группа (с регистрацией)</span>';
break;

						case 'private':
							$type = '<img src="images/private.gif"style="FLOAT: left;">&nbsp;<span class=formcomm>Это частная группа</span>';
							break;
					}

					$descr = "<span class=text>".$row['descr']."</span>";
					$info = "<a href='index.php?go=members&id_group=".$row['id_group']."' class=group_btm>участников ($mems)</a> <span class=date>|</span>
				<span class=date>фото ($num_photo)</span>";
					$right_restr = '';
					if($row['connected'] == 'ban')
					$right_restr = "<br><font color=#FF0000 class=formcomm>Ваши права в группе были ограничены администратором группы</font>";

					$mainpage = '';
					if($_SESSION['rights'] == 100)
					if($row['main_page'] == 'yes')
					$mainpage = '&nbsp;&nbsp;&nbsp;<a href="handler.php?handle=mn_page&id_group='.$row['id_group'].'&rem_add=remove"><img src="images/remove.gif" width="17" height="17" border="0" title="убрать с главной"></a>';
					elseif($row['main_page'] == 'no')
					$mainpage = '&nbsp;&nbsp;&nbsp;<a href="handler.php?handle=mn_page&id_group='.$row['id_group'].'&rem_add=add"><img src="images/add.gif" width="17" height="17" border="0" title="добавить на главную"></a>';

					$groups .= "<p>$image&nbsp;&nbsp;<a href='index.php?go=group&id_group=".$row['id_group']."' class=grouplink>".$row['title']."</a> ".$delme.$mainpage.$right_restr."</p>$type<br><br>$descr<br><br>$info<br><br><br><br>";
				}
			} else {
				$groups .= "<p class=formcomm>Пусто</p>";
			}

		} else
		$groups .= "<p class=text>Необходима авторизация</p>";
		//------------------------------------------------------------------------------------------------

		$group_sec = new section($title, 2, 2, 1, sh_out($groups, 'left'));

		$group = new content($group_sec);

		$this->set_content($group);
	}

	// добавление/отказ юзера в группу
	function add_ref_member($id_user, $id_group, $mode) {
		$res = $this->q("SELECT `id_author` FROM `group` WHERE `id_group`=".$id_group);
		$row = mysql_fetch_assoc($res);

		if($_SESSION['id_user'] == $row['id_author']) {

			if($mode == 'add')
			$cnctd = 'yes';
			elseif($mode == 'ref')
			$cnctd = 'no';

			$this->q("UPDATE `group_member` SET `connected`='".$cnctd."' WHERE `id_user`=".$id_user." AND `id_group`=".$id_group." AND `connected` LIKE 'addme'");
		}

		$back = $_SERVER['HTTP_REFERER'];
		header('location:'.$back);
		exit;
	}

	// отправка запроса администратору группы на вступление в группу (актуально для групп типа "частная" и "с ригистрацией" )
	function addme_in_group($id_user, $id_group) {
		/*
		$res = $this->q("SELECT `type`, `group_member`.`connected` 'cnctd' FROM `group`
		LEFT JOIN `group_member` ON `group_member`.`id_group`= `group`.`id_group`
		WHERE `group`.`id_group`=".$id_group);
		*/

		$res = $this->q("SELECT `group_member`.`connected` 'cnctd' FROM `group_member`
						 WHERE `group_member`.`id_user`=".$id_user." AND `group_member`.`id_group`=".$id_group);

		$row = mysql_fetch_assoc($res);

		// если пользователь никогда не состоял в данной группе
		if( $row['cnctd'] == null ) {
			$row = mysql_fetch_assoc($this->q("SELECT `type` FROM `group` WHERE `id_group`=".$id_group));

			if($row['type'] == 'public_reg' or $row['type'] == 'private') {

				$this->q("INSERT INTO `group_member`(`id_user`, `id_group`) VALUES($id_user, $id_group)");
				$add_me_ok = '<span class=text>Сообщение послано администратору группы. Следите за изменениями в Вашем профиле.</span>
							  <p><a href="index.php?go=groups" class=link>Перейти на группы</a></p>';

			} elseif ($row['type'] == 'public') {
				$this->q("INSERT INTO `group_member`(`id_user`, `id_group`, `connected`) VALUES($id_user, $id_group, 'yes')");
				$add_me_ok = '<span class=text>Вас добавили</span><p>
							  <a href="index.php?go=groups" class=link>Перейти на группы</a></p>';			
			}

		} else {
			if( $row['cnctd'] == 'no' ) { // если пользователю было отказано в участии
				$this->q("UPDATE `group_member` SET `connected`='addme' WHERE `id_user`=$id_user AND `id_group`=$id_group");
				$add_me_ok = '<span class=text>Повторное сообщение послано администратору группы.
								Следите за изменениями в Вашем профиле.</span>
							  <p><a href="index.php?go=groups" class=link>Перейти на группы</a></p>';				
			} elseif ( $row['cnctd'] == 'del' ) { // пользователь покинул группу после того как был забанен
				$this->q("UPDATE `group_member` SET `connected`='ban' WHERE `id_user`=$id_user AND `id_group`=$id_group");
				$add_me_ok = '<span class=text>Вы добавленны.</span>
							  <p><a href="index.php?go=groups" class=link>Перейти на группы</a></p>';					
			} else {
				$add_me_ok = 'Вы либо уже добавлены либо Ваш запрос ещё рассматривается администратором';
			}
		}

		$addme_sec = new section("<span class=header1>Добавление в группу</span>", 2, 2, 1, sh_out($add_me_ok, 'left'));

		$addme= new content($addme_sec);

		$this->set_content($addme);
	}

	// выход из группы
	function delme_from_group($id_user, $id_group) {

		$res = $this->q("SELECT `connected` FROM `group_member` WHERE `id_user`=".$id_user." AND `id_group`=".$id_group);
		$row = mysql_fetch_assoc($res);

		// обработка ситуации когда пользователь покидает группу после того как его забанили
		if($row['connected'] != 'ban')
		$this->q("DELETE FROM `group_member` WHERE `id_user`=".$id_user." AND `id_group`=".$id_group);
		else
		$this->q("UPDATE `group_member` SET `connected`='del' WHERE `id_user`=".$id_user." AND `id_group`=".$id_group);

		$back = $_SERVER['HTTP_REFERER'];
		header('location:'.$back);
		exit;
	}

	// все группы на странице
	function all_groups() {
		$title = "<span class=header1>Все группы</span>";
		//		if( isset($_SESSION['id_user']) ) {
		$all_groups = "";

		if( isset($_SESSION['id_user']) ) {
			$res = $this->q("SELECT DISTINCT `type`, `group`.`id_group` 'id_group', `img`, `descr`, `title`,
							`group_member`.`connected` 'cnctd', `id_author`, `main_page`,  COUNT(g_m.`id_user`) 'members'
							 FROM `group` 
							 	LEFT JOIN `group_member` ON `group_member`.`id_group`=`group`.`id_group` AND `group_member`.`id_user`=".$_SESSION['id_user']."
							 	LEFT JOIN `group_member` g_m ON g_m.`id_group`=`group`.`id_group` AND g_m.`connected`='yes'
							 GROUP BY `group`.`id_group`
							 ORDER BY members DESC");		
		} else {
			$res = $this->q("SELECT `type`, `group`.`id_group` 'id_group', `img`, `descr`, `title`, `id_author` FROM `group`");
		}

		if(mysql_num_rows($res)) {
			while ($row = mysql_fetch_assoc($res)) {
				$image = ($row['img'] != NULL) ? "<a href='index.php?go=group&id_group=".$row['id_group']."' class=link><img src='images/".$row['img']."' border=0></a>"
				: "<a href='index.php?go=group&id_group=".$row['id_group']."' class=link><img src='images/im_group.gif' border=0></a>";

				if(isset($_SESSION['id_user']) and $_SESSION['id_user'] != $row['id_author'] and $_SESSION['rights'] < 100 ) {

					if($row['cnctd'] == 'yes' or $row['cnctd'] == 'ban') {
						$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span><a href='index.php?go=delme&id_group=".$row['id_group']."' class=link>выйти</a>";
						if($row['cnctd'] == 'ban') $addme .= "<br><font color=#FF0000 class=formcomm>Ваши права в группе были ограничены администратором группы</font>";
					} elseif ($row['cnctd'] == 'no')
					$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span>
						<a href='index.php?go=addme&id_group=".$row['id_group']."' class=link>присоединиться</a><span class=cin>&nbsp;&raquo;&nbsp;</span><span class=text>отказано :'(</span>";
					elseif( $row['cnctd'] == 'addme')
					$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span><span class=text>Вас подключают...</span>";
					elseif ( $row['cnctd'] == null or $row['cnctd'] == 'del')
					$addme = "<span class=cin>&nbsp;&raquo;&nbsp;</span><a href='index.php?go=addme&id_group=".$row['id_group']."' class=link>присоединиться</a>";

				} else
				$addme = '';

				$mems = mysql_num_rows( $this->q("SELECT * FROM `group_member` WHERE `id_group`=".$row['id_group']." AND (`connected`='yes' OR `connected`='ban')") );
				// Так как пост содержит одно фото, то количество постов равно кол-ву фото плюс группировка по `id_photo`
				$num_photo = mysql_num_rows($this->q("SELECT `id_photo` FROM `post`, `discuss`
												  	  WHERE `discuss`.`id_group`=".$row['id_group']." AND `discuss`.`id_discuss`=`post`.`id_discuss`
												  	  GROUP BY `id_photo`"));
				switch ($row['type']) {
					case 'public':
						$type = '<img src="images/public.gif" style="FLOAT: left;">&nbsp;<span class=formcomm>Это публичная группа (без регистрации)</span>';
						break;

					case 'public_reg':
						$type = '<img src="images/public_reg.gif" style="FLOAT: left;">&nbsp;<span class=formcomm>Это публичная группа (с регистрацией)</span>';
						break;

					case 'private':
						$type = '<img src="images/private.gif" style="FLOAT: left;">&nbsp;<span class=formcomm>Это частная группа</span>';
						break;
				}

				$descr = (!empty($row['descr'])) ? "<span class=text>".$row['descr']."</span><br><br>" : "<span class='nodescr'>Нет описания</span><br><br>";
				$info = "<a href='index.php?go=members&id_group=".$row['id_group']."' class=group_btm>участников ($mems)</a> <span class=date>|</span>
				<span class=date>фото ($num_photo)</span>";	

				if($_SESSION['rights'] == 100) {
					if($row['main_page'] == 'yes')
					$mainpage = '&nbsp;&nbsp;&nbsp;<a href="handler.php?handle=mn_page&id_group='.$row['id_group'].'&rem_add=remove"><img src="images/remove.gif" width="17" height="17" border="0" title="убрать с главной"></a>';
					elseif($row['main_page'] == 'no')
					$mainpage = '&nbsp;&nbsp;&nbsp;<a href="handler.php?handle=mn_page&id_group='.$row['id_group'].'&rem_add=add"><img src="images/add.gif" width="17" height="17" border="0" title="добавить на главную"></a>';
				}
				$all_groups .= "<p>$image
				&nbsp;&nbsp;<a href='index.php?go=group&id_group=".$row['id_group']."' class=grouplink>".$row['title']."</a>$addme $mainpage</p>$type<br><br>$descr$info<br><br><br><br>";	
			}
		} else {
			$all_groups .= "<p>Группы не добавлены</p>";
		}


		$group_sec = new section($title, 2, 2, 1, sh_out($all_groups, 'left'));

		$group = new content($group_sec);

		$this->set_content($group);
	}

	//---------------------------------------------- GROUP END ---------------------------------------------------------

	// Пользовательский профиль
	function profile($id_user) {

		$res = $this->q("SELECT `fio`, `name`, `avatar`,`email`, `hideemail`, `domain`, `pb_ban`,
								DATE_FORMAT(`reg_date`, '%e.%m.%Y&nbsp;<font size=1>%k:%i:%s</font>') 'reg_date',
								IF (`ip`=0, 'пусто', INET_NTOA(`ip`)) 'ip', INET_NTOA(`last_ip`) 'last_ip'
						 FROM `user` WHERE `id_user`=".$id_user);
		$row = mysql_fetch_assoc($res);

		if( isset($_SESSION['id_user']) )
		$f = "( `scope`='friends' AND ".$_SESSION['id_user']." IN (
				 SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= $id_user)) OR `id_user`=".$_SESSION['id_user']." OR  
				(`scope`='onlyme' AND `id_user`=".$_SESSION['id_user'].") OR ";
		else
		$f = "";
		$num_photo = mysql_num_rows( $this->q("SELECT `id_user` FROM `photo` WHERE `id_user`=$id_user AND (".$f."(`scope` = 'all') )") );

		// Пользователи помеченные мною как мои друзья
		$friend_res = $this->q("SELECT `user`.`name` 'frnd', `id_friend` FROM `friends`
								LEFT JOIN `user` ON `user`.`id_user`=`friends`.`id_friend` WHERE `id_author`=".$id_user);		
		if(mysql_num_rows($friend_res)) {
			$row2 = mysql_fetch_assoc($friend_res);
			$friend_str = '<a href="index.php?go=profile&id_user='.$row2['id_friend'].'" class="user">'.$row2['frnd'].'</a>';

			while ( $row2 = mysql_fetch_assoc($friend_res) ) {
				$friend_str .= ', <a href="index.php?go=profile&id_user='.$row2['id_friend'].'" class="user">'.$row2['frnd'].'</a>';
			}
		} else {
			$friend_str = 'Нет друзей';
		}

		// Пользователи пометившие "меня" как своего друга
		$frnd2_res = $this->q("SELECT `user`.`name` 'frndto', `id_author` FROM `friends`
								  LEFT JOIN `user` ON `user`.`id_user`=`friends`.`id_author` WHERE `id_friend`=".$id_user); 				
		if(mysql_num_rows($frnd2_res)) {
			$row2 = mysql_fetch_assoc($frnd2_res);

			$frnd2_str = '<a href="index.php?go=profile&id_user='.$row2['id_author'].'" class="user">'.$row2['frndto'].'</a>';
			if($row2['id_author'] == $_SESSION['id_user']) $frnd_flag = true;

			while ( $row2 = mysql_fetch_assoc($frnd2_res) ) {
				$frnd2_str .= ', <a href="index.php?go=profile&id_user='.$row2['id_author'].'" class="user">'.$row2['frndto'].'</a>';
				if($row2['id_author'] == $_SESSION['id_user']) $frnd_flag = true;
			}
		} else {
			$frnd2_str = 'Не добавлен никем';
		}

		if( isset($_SESSION['id_user']) and $id_user != $_SESSION['id_user'] ) {
			/*
			if(isset($frnd_flag))
			$deladd_to_friends = '<a href="index.php?go=del_friend&id_author='.$_SESSION['id_user'].'&id_friend='.$id_user.'" class=link><font size=1>удалить из друзей</font></a>';
			else
			$deladd_to_friends = "<a href='handler.php?handle=add_friend&id_user=".$_SESSION['id_user']."&friend_select=".$id_user."' class=link><font size=1>добавить в друзья</font></a>";

			if($_SESSION['rights'] == 100) {
			if($row['pb_ban'] == 'no')
			$frbd_alw_cmt = '&nbsp;&nbsp;<a href="handler.php?handle=pb_ban&id_user='.$id_user.'" class=link title="запрет на публикацию фото пользователя на главной странице и в общем разделах"><font size=1 color=#FF0000>запретить публиковаться</font></a>';
			elseif ($row['pb_ban'] == 'yes')
			$frbd_alw_cmt = '&nbsp;&nbsp;<a href="handler.php?handle=pb_unban&id_user='.$id_user.'" class=link title="запрет на публикацию фото пользователя на главной странице и в общем разделах"><font size=1>разрешить публиковаться</font></a>';
			}
			*/
			if(isset($frnd_flag))
			$deladd_to_friends = '<a href="index.php?go=del_friend&id_author='.$_SESSION['id_user'].'&id_friend='.$id_user.'" title="Удалить из друзей" ><img src="images/rem_friend.gif" border="0"></a>';
			else
			$deladd_to_friends = "<a href='handler.php?handle=add_friend&id_user=".$_SESSION['id_user']."&friend_select=".$id_user."' title='Добавить в друзья'><img src='images/add_friend.gif' border=0></a>";

			if($_SESSION['rights'] == 100) {
				if($row['pb_ban'] == 'no')
				$frbd_alw_pb = '&nbsp;&nbsp;<a href="handler.php?handle=pb_ban&id_user='.$id_user.'" class=link title="Запретить пользователю публикацию фото на главной странице и в общих разделах"><img src="images/pb_ban.gif" border="0"></a>';
				elseif ($row['pb_ban'] == 'yes')
				$frbd_alw_pb = '&nbsp;&nbsp;<a href="handler.php?handle=pb_unban&id_user='.$id_user.'" class=link title="Разрешить пользователю публикацию фото на главной странице и в общих разделах"><img src="images/pb_unban.gif" border="0"></a>';
			}

			$row3 = $this->q("SELECT * FROM `ban_comment` WHERE `id_author`=".$_SESSION['id_user']." AND `id_user`=".$id_user);
			if(mysql_num_rows($row3)) {
				$frbd_alw_cmt = '&nbsp;&nbsp;<a href="handler.php?handle=alw_comment&id_user='.$id_user.'" class=link title="Разрешить пользователю комментировать мои фото"><img src="images/comment.gif" border="0"></a>';
			} else
			$frbd_alw_cmt = '&nbsp;&nbsp;<a href="handler.php?handle=frbd_comment&id_user='.$id_user.'" class=link title="Запретить пользователю комментировать мои фото"><img src="images/forbid_comment.gif" border="0"></a>';

		} else {
			$frbd_alw_cmt = "";
			$deladd_to_friends = "";
		}


		if( empty($row['avatar']) ) {
			$avatar = '<span class=formcomm>не добавлен</span>';
		} else {
			$avatar = '<img src="images/'.$row['avatar'].'">';
		}

		// Группы созданные "мною"
		$my_groups_res = $this->q("SELECT * FROM `group` WHERE `id_author`=".$id_user);

		if( mysql_num_rows($my_groups_res) ) {
			$row_my_groups = mysql_fetch_assoc($my_groups_res);

			$title = wordwrap($row_my_groups['title'], 32, "<br>", 1);
			$my_groups = "<a href='index.php?go=group&id_group=".$row_my_groups['id_group']."' class=link>".$title."</a>";
			while ( $row_my_groups = mysql_fetch_assoc($my_groups_res) ) {
				$title = wordwrap($row_my_groups['title'], 32, "<br>", 1);
				$my_groups .= ", <a href='index.php?go=group&id_group=".$row_my_groups['id_group']."' class=link>".$title."</a>";
			}
		} else {
			$my_groups = "не созданы";
		}

		// Группы в которых я участник
		$in_groups_res = $this->q("SELECT `title`, `group_member`.`id_group` 'id_group'  FROM `group_member`
								   LEFT JOIN `group` ON `group`.`id_group`= `group_member`.`id_group`
								   WHERE `id_user`=".$id_user." AND `connected`='yes'");

		if( mysql_num_rows($in_groups_res) ) {
			$row_in_groups = mysql_fetch_assoc($in_groups_res);

			$title = wordwrap($row_in_groups['title'], 32, "<br>", 1);
			$in_groups = "<a href='index.php?go=group&id_group=".$row_in_groups['id_group']."' class=link>".$title."</a>";
			while ( $row_in_groups = mysql_fetch_assoc($in_groups_res) ) {
				$title = wordwrap($row_in_groups['title'], 32, "<br>", 1);
				$in_groups .= ", <a href='index.php?go=group&id_group=".$row_in_groups['id_group']."' class=link>".$title."</a>";
			}
		} else {
			$in_groups = "нет вхождений";
		}

		$profile_sec = new section("<span class=header1>Профиль</span>", 4, 3, 1,
		sh_profile($id_user, $row['fio'], $row['name'], $row['domain'], $avatar, $row['email'], $row['hideemail'],
		$num_photo, $friend_str, $frnd2_str, $my_groups, $in_groups, $row['reg_date'], $deladd_to_friends.$frbd_alw_cmt.$frbd_alw_pb, $row['ip'], $row['last_ip']) );

		$profile = new content($profile_sec);

		$this->set_content($profile);
	}

	/*
	Бокс популярных тегов слева вверху
	Популряность - количество фотографии по данному тегу
	Количество фотографий по тегу расчитывается с учетом видимости для пользователя,
	таким образом список в общем случае разный для разных пользователей.
	*/
	function pop_tag() {
		$body = '<tr>';

		// если пользователь вошел, то определить его статус (друг, автор) для предоставления права на просмотр фото
		if( isset($_SESSION['id_user']) ) $f = "( ( `scope`='friends' AND ".$_SESSION['id_user']." IN (
				SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= idauthor) OR `photo`.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND `photo`.`id_user`=".$_SESSION['id_user'].") OR (`scope` = 'all') )";
		else // отображаются только те фото у которых область видимости "для всех"
		$f = "(`scope` = 'all') ";


		$res = $this->q("SELECT `tag`.`id_tag`, `tag`.`name` 'name', COUNT(`tag_photo`.`id_tag`) 'c', `scope`,
								`photo`.`id_user` 'idauthor', `tag`.`main_page` 'mainpage'  
						 FROM `tag` 
						 	LEFT JOIN `tag_photo` ON `tag_photo`.`id_tag` = `tag`.`id_tag` 
						 	LEFT JOIN `photo` ON `photo`.`id_photo`=`tag_photo`.`id_photo` 
						 WHERE ".$f." AND `tag`.`main_page`='yes'						 
						 GROUP BY `tag_photo`.`id_tag`
						 ORDER BY c DESC, `tag`.`id_tag` ASC");
		/*
		Список тегов выводим в две колонки по принципу:
		01 первый	02 второй
		03 третий	04 четвертый
		...
		*/

		$i = 0; // индекс для левого столбца
		$j = 5; // -||- -||- для правого
		$sw = -1; // флаг "-1" - курсор слева, "+1" - справа
		$N = mysql_num_rows($res);
		if( $N ) {
			do {
				// добавлять нолик перед цифрами до 10ти
				$o = ( $i < 5 ) ? '0' : '';

				// background рисунок для первых двух столбоцов в левой колонке
				$bg = (!$i) ? 'background="images/first_pop.jpg"' : '';
				$bg = ($i == 1 and $j == 6) ? 'background="images/second_pop.jpg"' : $bg;

				if( $sw == -1 ) {// ввод тегов для левой колонки
					$num = $i+1;
					mysql_data_seek($res, $i++);
					$flag = false;
				} else {	// для правой
					$num = $j+1;
					if($j++ < $N) {
						mysql_data_seek($res, $j-1);
						$flag = false;
					} else
					$flag = true;
				}
				if( !($row = mysql_fetch_object($res)) or $i > 5) {
					$body .= '</tr>';
					break;
				}
				if($flag) $row->name='';

				$tagname = $row->name;

				// Обрезаем длинные теги
				if( strlen($row->name) > 15 )
				$tagname = substr($row->name, 0, 12)."...";


				$tagminus = '';
				/*
				if($_SESSION['id_user']) {
				if($row->mainpage == 'yes')
				$tagminus = '<a href="handler.php?handle=tag_rem&id_tag='.$row->id_tag.'"  class="tagminus"> -</a>';
				elseif ($row->mainpage == 'no')
				$tagminus = '<a href="handler.php?handle=tag_rem&id_tag='.$row->id_tag.'"  class="tagminus"> -</a>';
				}*/

				if($_SESSION['rights'] == 100) {
					if(!empty($tagname))
					$tagminus = '<a href="handler.php?handle=tag_rem&id_tag='.$row->id_tag.'"  class="tagminus"  title="убрать тег с главной страницы"> -</a>';
				}

				$number = ($row->name != '') ? '<span class="num">'.$o.$num.'</span>' : '';
				$body .='<td width="147" height="20" valign="top" '.$bg.'>'.$number.'
            			 <a href="index.php?go=photo_tag&id_tag='.$row->id_tag.'&ph_page=1"  class="taglink">'.$tagname.'</a>'.$tagminus.'</td>';

				if( $sw == 1 and $i < 5) $body .= '</tr><tr>';
				$sw = -$sw;
			} while (1);
		}

		$this->_pop_tag = sh_pop_tag($body);
	}
	/*
	function tag_info($id_tag) {

	$res = $this->q("SELECT `tag`.`name` 'tagname', `user`.`name` 'author', `user`.`id_user` 'id_author'
	FROM `tag` LEFT JOIN `user` ON `user`.`id_user` = `tag`.`id_user`
	WHERE `id_tag`=".$id_tag);

	$row = mysql_fetch_assoc($res);

	if( isset($_SESSION['id_user']) ) $f = " AND ( ( `scope`='friends' AND ".$_SESSION['id_user']." IN (
	SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= `photo`.`id_user`) OR `photo`.`id_user`=".$_SESSION['id_user'].") OR
	( `scope`='onlyme' AND `photo`.`id_user`=".$_SESSION['id_user'].") OR  (`scope` = 'all') )";
	else $f = " AND (`scope` = 'all')";

	//
	$num_photos = mysql_num_rows(
	$this->q("
	SELECT *
	FROM `tag_photo`
	LEFT JOIN `photo` ON `photo`.`id_photo`=`tag_photo`.`id_photo`
	WHERE `tag_photo`.`id_tag`=".$id_tag.$f));
	/////////////////////////////////////

	$tag_sec = new section("<span class=header1>Информация о теге</span>", 2, 2, 1, sh_taginfo($row['id_author'], $id_tag, $row['tagname'], $num_photos, $row['author']) );
	$tag = new content($tag_sec);
	$this->set_content($tag);

	}
	*/

	// "Облако тегов"
	function all_tags($mode) {
		$main_page = "'yes'";
		$limit = "LIMIT 0, 200";
		if( isset($mode) and $_SESSION['rights'] == 100) {
			$main_page = "'no'";
			$limit = "";
		}

		if( !isset($mode) )	{
			if( isset($_SESSION['id_user']) ) $f = "( ( `scope`='friends' AND ".$_SESSION['id_user']." IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= idauthor) OR `photo`.`id_user`=".$_SESSION['id_user'].") OR 
						     		( `scope`='onlyme' AND `photo`.`id_user`=".$_SESSION['id_user'].") OR (`scope` = 'all') )";
			else $f = "(`scope` = 'all') ";


			$res = $this->q("SELECT `tag`.`id_tag` 'id_tag', `tag`.`name` 'name', COUNT(`tag_photo`.`id_tag`) AS 'rank', `scope`,
									`photo`.`id_user` 'idauthor', `tag`.`main_page` 'main_page'  
							 FROM `tag` 
							 	LEFT JOIN `tag_photo` ON `tag_photo`.`id_tag` = `tag`.`id_tag` 
							 	LEFT JOIN `photo` ON `photo`.`id_photo`=`tag_photo`.`id_photo` 
								WHERE ( ".$f." AND `tag`.`main_page`=".$main_page.")
							 GROUP BY `tag_photo`.`id_tag`
							 ORDER BY rank DESC, `tag`.`id_tag` ASC ".$limit);


			//$res = $this->q("SELECT `rank` FROM `tag` ORDER BY `rank` DESC");
			$i=0;

			//--------------- Масштабирование популярности тегов на 6-ти пунктовой шкале ------------------------
			while( $row = mysql_fetch_assoc($res) ) {
				if(!$i++)
				$max = $row['rank'];

				$rank2[$row['id_tag']] = $row['rank'];
			}

			// 6 levels of popularity
			settype($max, 'integer');
			$scale = $max / 6;
		}
		$tags_str = '';
		$res2 = $this->q("SELECT * FROM `tag` WHERE `tag`.`main_page`=".$main_page." ORDER BY `name` ASC");

		//$res2 = $res;

		while( $row2 = mysql_fetch_assoc($res2) ) {

			$tagminus = '';

			if($_SESSION['rights'] == 100) {
				if($row2['main_page'] == 'yes')
				$tagminus = ' <a href="handler.php?handle=tag_rem&id_tag='.$row2['id_tag'].'"  class="tagminus" title="убрать тег с главной страницы"> -</a>';
				elseif ($row2['main_page'] == 'no')
				$tagminus = ' <a href="handler.php?handle=tag_add&id_tag='.$row2['id_tag'].'"  class="tagminus" title="добавить тег на главную страницу"> +</a>';
			}

			if( !isset($mode) )	{
				if( isset($rank2[$row2['id_tag']])  )
				$rank = $rank2[$row2['id_tag']];
				else {
					$rank = 0;	continue;
				}
			} else {
				$rank = 0;
			}

			settype($rank, 'integer');
			$a = '<a href="index.php?go=photo_tag&id_tag='.$row2['id_tag'].'"  class="taglink">';

			if( $rank >= 0 and $rank <= $scale ) {
				$tags_str .= $a.'<font size="1">'.$row2['name'].'</font></a>'.$tagminus.' ';
			} elseif( $rank > $scale and $rank <= 2*$scale ) {
				$tags_str .= $a.$row2['name'].'</a>'.$tagminus.' ';
			} elseif( $rank > 2*$scale and $rank <= 3*$scale ) {
				$tags_str .= $a.'<font size="2">'.$row2['name'].'</font></a>'.$tagminus.' ';
			} elseif( $rank > 3*$scale and $rank <= 4*$scale ) {
				$tags_str .= $a.'<font size="3">'.$row2['name'].'</font></a>'.$tagminus.' ';
			} elseif ( $rank > 4*$scale and $rank <= 5*$scale ) {
				$tags_str .= $a.'<font size="4">'.$row2['name'].'</font></a>'.$tagminus.' ';
			} elseif( $rank > 5*$scale and $rank <= $max ) {
				$tags_str .= $a.'<font size="5">'.$row2['name'].'</font></a>'.$tagminus.' ';
			}
		}
		//---------------------------------------------------------------------------------------------------

		$hidden_tags = "<br>";
		if($_SESSION['rights']==100) {
			if( !isset($mode))
			$hidden_tags = "<a href='index.php?go=all_tags&mode=all' class=link>cкрытые теги</a><br><br>";
			else
			$hidden_tags = "<a href='index.php?go=all_tags' class=link>вернуться на облако тегов</a><br><br>";
		}

		$all_tags_sec = new section("<span class=header1>Облако тегов</span>", 2, 2, 1, sh_alltags($hidden_tags.$tags_str));
		$all_tags = new content($all_tags_sec);
		$this->set_content($all_tags);
	}

	// Популярные группы в левом нижнем углу
	// Популярность расчитывается по количеству участников
	function pop_groups() {

		$res = $this->q("
		SELECT `group`.`id_group` 'id_group', `img`, `title`, COUNT(`id_user`) 'members', `descr`
		FROM `group` LEFT JOIN `group_member` ON `group`.`id_group`=`group_member`.`id_group`
		WHERE `main_page`='yes'
		GROUP BY `group`.`id_group`
		ORDER BY members DESC
		LIMIT 0, 4");

		$body = '';
		while ($row = mysql_fetch_assoc($res)) {
			$mems = mysql_num_rows( $this->q("SELECT * FROM `group_member` WHERE `id_group`=".$row['id_group']." AND (`connected`='yes' OR `connected`='ban')") );

			// Так как пост содержит одно фото, то количество постов равно кол-ву фото плюс группировка по `id_photo`
			$num_photo = mysql_num_rows($this->q("SELECT `id_photo` FROM `post`, `discuss`
												  WHERE `discuss`.`id_group`=".$row['id_group']." AND `discuss`.`id_discuss`=`post`.`id_discuss`
												  GROUP BY `id_photo`"));

			$image = ($row['img'] != NULL) ? "<a href='index.php?go=group&id_group=".$row['id_group']."'><img src='images/".$row['img']."' border=0></a>"
			: "<a href='index.php?go=group&id_group=".$row['id_group']."'><img src='images/im_group.gif' border=0></a>";
			$descr = ( !empty($row['descr']) ) ? "<span class=groupdis>".$row['descr']."</span><br>" : "";

			$title = wordwrap($row['title'], 24, "<br>", 1);

			$mainpage = '';
			if($_SESSION['rights'] == 100)
			$mainpage = '&nbsp;&nbsp;&nbsp;<a href="handler.php?handle=mn_page&id_group='.$row['id_group'].'&rem_add=remove"><img src="images/remove.gif" width="17" height="17" border="0" title="убрать с главной"></a>';

			$body .= "<p>$image&nbsp;&nbsp;<a href='index.php?go=group&id_group=".$row['id_group']."' class=grouplink2>".$title."</a>".$mainpage."<br>
                    ".$descr."<a href='index.php?go=members&id_group=".$row['id_group']."' class=group_btm>участников 
                    (".$mems.")</a> <span class=date>|</span> <span class=date>фото (".$num_photo.")</span></p>";
		}

		$this->_left .= sh_pop_groups($body);
	}

	// Короткие новости слева
	function shortnews() {
		$this->_left = sh_news();
	}

	function news($id_news) {
		switch ($id_news) {

			case 2:
				$newstxt = ' <p class=news>&nbsp;&nbsp;&nbsp;Подготовка к запуску нашего проекта завершается.
				Скоро он будет открыт для широких масс пользователей сети <strong><font color="#00599d">next</font><font color="#f1592a">One</font></strong>.
				Ваши фото увидит весь мир!
				</p>';
				break;

			case 1:
				$newstxt = '<p class=news>&nbsp;&nbsp;&nbsp;Торжественный момент настал! Сегодня день рождения нашего сайта.
				Мы благодарим наших тестеров за помощь в поиске багов и ждем новых пользователей.</p>';
				break;

			case 3:
				$newstxt = ' <p class=news>&nbsp;&nbsp;&nbsp;Первая городская сеть, которая объединяет жителей всех районов Волгограда в единое информационное
				пространство. Мы дарим нашим пользователям радость общения.</p>';
				break;
		}


		$news_sec = new section("<span class=header1>Новости</span>", 2, 2, 1, sh_out($newstxt, 'left'));
		$news_sec_con = new content($news_sec);
		$this->set_content($news_sec_con);
	}

	function allnews() {

		$newstxt = '<p><a href="index.php?go=news&id_news=1" class="news">Торжественный момент настал! Сегодня день рождения нашего сайта. Мы благодарим...<br>
              <br>
              </a><span class="date">9.11.2007</span></p>
            <p><a href="index.php?go=news&id_news=2" class="news">Подготовка к запуску нашего проекта завершается. 
				Скоро он будет открыт для широких масс пользователей сети...<br>
              <br>
              </a><span class="news"><span class="date">12.10.2007</span></span></p>';	

		$news_sec = new section("<span class=header1>Все новости</span>", 2, 2, 1, sh_out($newstxt, 'left'));
		$news_sec_con = new content($news_sec);
		$this->set_content($news_sec_con);
	}

	// отображение страницы
	function show() {

		echo sh_show($this->_title, $this->_top, $this->_auth_box, $this->_pop_tag,
		$this->_left, $this->__->output(), $this->_copyright, $this->_bottom, $this->_bottom_menu);
	}

	// Секция последних фото на главной странице
	function last_photos() {

		if( isset($_SESSION['id_user']) ) $f = "( `scope`='friends' AND ".$_SESSION['id_user']." IN (
		SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= idauthor) OR usr.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND usr.`id_user`=".$_SESSION['id_user'].") OR ";
		else $f = "";

		$where = "";
		//if($_SESSION['rights'] != 100)
		$where = "WHERE `main_page` != 'no'";

		$res = $this->q("SELECT DATE_FORMAT(`pubdate`, '%e.%m.%y&nbsp;&nbsp;%k:%i:%s') 'date', usr.`name` 'author',
					 		 usr.`id_user` 'idauthor', usr.`pb_ban`, `small`, `id_photo`, `rating`, `scope`, `main_page` 
					     FROM (
					     	SELECT `pubdate`, `id_user`, `small`, `id_photo`, `rating`, `scope`, `main_page` 
					     	FROM `photo`
					     	".$where."
					     	ORDER BY `pubdate` DESC
					     ) tmp LEFT JOIN `user` usr ON usr.`id_user` = tmp.`id_user` 
					     WHERE ".$f." 
					     		(`scope` = 'all') AND usr.`id_user` = tmp.`id_user` AND usr.`pb_ban` = 'no'
					     GROUP BY tmp.`id_user` ORDER BY `pubdate` DESC LIMIT 0, 10");		

		/*
		$res = $this->q("SELECT DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'date', usr.`name` 'author',
		usr.`id_user` 'idauthor', `small`, `id_photo`, `rating`, `scope`
		FROM `photo` LEFT JOIN `user` usr ON usr.`id_user` = `photo`.`id_user`
		WHERE ".$f."
		(`scope` = 'all')
		ORDER BY `pubdate` DESC LIMIT 0, 10");
		*/
		$i=0;
		while( $row_photo = mysql_fetch_assoc($res)) {
			$photo[$i++] = new photo($row_photo['small'], $row_photo['id_photo'],
			$row_photo['date'], $row_photo['author'], $row_photo['idauthor'], true, $row_photo['main_page']);
		}

		if(isset($_SESSION['id_user']))
		$place = 2;
		else
		$place = 1;

		$last_phts = new section("<span class=header1>Последние фото </span><span class=cin>&nbsp;&raquo;
								&nbsp;</span><a href='index.php?go=all_lastphotos&ph_page=1' class=link>Смотреть все фото</a>", 8, 5, $place);
		$last_phts->add_photos($photo, 2);
		return $last_phts;
	}

	function pop_photos() {

		if( isset($_SESSION['id_user']) ) $f = "( `scope`='friends' AND ".$_SESSION['id_user']." IN (
		SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= idauthor) OR usr.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND usr.`id_user`=".$_SESSION['id_user'].") OR ";
		else $f = "";

		$where = "";
		//if($_SESSION['rights'] != 100)
		$where = " AND `main_page` != 'no'";
		$res = $this->q("SELECT DATE_FORMAT(`pubdate`, '%e.%m.%y&nbsp;&nbsp;%k:%i:%s') 'date', usr.`name` 'author',
					 		 usr.`id_user` 'idauthor', `small`, `id_photo`, `rating`, `scope`, `main_page` 
					     FROM `photo` LEFT JOIN `user` usr ON usr.`id_user` = `photo`.`id_user`
					     WHERE ".$f." 
					     		(`scope` = 'all') ".$where." AND usr.`pb_ban`='no'
						 ORDER BY `rating` DESC, `pubdate` ASC LIMIT 0, 5");

		$i=0;

		while( $row_photo = mysql_fetch_assoc($res) ) {
			/*
			$photo[$i++] = new photo($row_photo['small'], $row_photo['id_photo'], $row_photo['date'],
			$row_photo['author']." rate=".$row_photo['rating'], $row_photo['idauthor']);*/

			$photo[$i++] = new photo($row_photo['small'], $row_photo['id_photo'], $row_photo['date'],
			$row_photo['author'], $row_photo['idauthor'], true, $row_photo['main_page']);
		}

		$pop_phts = new section("<span class=header1>Популярные фото </span><span class=cin>&nbsp;&raquo;
								&nbsp;</span><a href='index.php?go=all_popphotos&ph_page=1' class=link>Смотреть все фото</a>", 8, 5, 2);
		$pop_phts->add_photos($photo);
		return $pop_phts;
	}

	// function which determines the outfit of the page as a result of user's enter.
	function my_photos() {
		$my_header = '<span class="header1">Мои фото</span>
			<span class="cin">&nbsp;&raquo;&nbsp;</span> <a href="index.php?go=albums&id_user='.$_SESSION['id_user'].'" class="link">Альбомы</a>
			<span class="cin">&nbsp;&raquo;&nbsp;</span> <a href="index.php?go=all_albums&id_user='.$_SESSION['id_user'].'" class="link">Архив</a></span>
			<span class="cin">&nbsp;&raquo;&nbsp;</span> <a href="index.php?go=add_photo&alb_num=0" class="link">Добавить</a>';

		$photos = $this->q("SELECT `small`, `id_photo`, DATE_FORMAT(`pubdate`, '%e.%m.%y&nbsp;&nbsp;%k:%i:%s') 'date'
							FROM `photo` WHERE `id_user`=".$_SESSION['id_user']." ORDER BY `pubdate` DESC");

		$i = 0;
		while( $row_photos = mysql_fetch_assoc($photos) and $i<5 ) {
			$pht[$i] = new photo($row_photos['small'], $row_photos['id_photo'], $row_photos['date'], $_SESSION['user'],
			$_SESSION['id_user'], false);
			$i++;
		}
		$my_photos = new section($my_header, 8, 5);
		$my_photos->add_photos($pht);

		$this->__->prepend($my_photos->output());

	}

	function all_albums($id_user) {
		$alb_header = '<span class="header1">Все альбомы</span>';

		if( isset($_SESSION['id_user']) ) {

			$f = " AND ( ( `ascope`='friends' AND ".$_SESSION['id_user']." IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= ".$id_user.") OR `album`.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `ascope`='onlyme' AND `album`.`id_user`=".$_SESSION['id_user'].") OR  (`ascope` = 'all') )";
		} else {
			$f = " AND ( `ascope` = 'all' )";
		}

		$res = $this->q("SELECT `id_album`, `id_user` 'id_author', `name`, `image`, DATE_FORMAT(`date`, '%e.%m.%Y') 'date'
						 FROM `album` WHERE `id_user`=".$id_user.$f." ORDER BY `date` ASC");		

		for ($i=0; $row_albums = mysql_fetch_assoc($res); $i++) {
			$num_photos = mysql_num_rows( $this->q("SELECT `id_photo` FROM `photo` WHERE `id_album`=".$row_albums['id_album']) );
			$albumname = wordwrap($row_albums['name'], 10, "<br>", 1);
			$albmp[$i] = new album($id_user, $row_albums['id_album'], $num_photos, $albumname, $row_albums['image'],
			$row_albums['date'], false, false);
		}

		$all_albums = new section($alb_header, 8, 5);
		$all_albums->add_albums($albmp);

		$all_albums_con = new content($all_albums, sh_mainpage_spacers());

		$this->set_content($all_albums_con);
	}


	function left_albums($checked, $page, $init_link, $id_user, $nochecked=true) {

		$_SESSION['id_user_left'] = $id_user;

		if($checked == 0 and $nochecked) {
			$row_album = mysql_fetch_assoc($this->q("SELECT `id_album` FROM `album` WHERE `id_user`=".$id_user." LIMIT 0, 1"));
			$checked = $row_album['id_album'];
		}

		if( isset($_SESSION['id_user']) ) {

			$f = " AND ( ( `ascope`='friends' AND ".$_SESSION['id_user']." IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= ".$id_user.") OR `album`.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `ascope`='onlyme' AND `album`.`id_user`=".$_SESSION['id_user'].") OR  (`ascope` = 'all') )";

			$ff = " AND ( ( `scope`='friends' AND ".$_SESSION['id_user']." IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= ".$id_user.") OR `id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND `id_user`=".$_SESSION['id_user'].") OR  (`scope` = 'all') )";

			if( $_SESSION['id_user'] == $id_user )
			$header = '<span class="header1">Ваши альбомы </span>';
			else
			$header = '<span class="header1">Альбомы пользователя</span>';
		} else {
			$ff = " AND ( `scope` = 'all' )";
			$f = " AND ( `ascope` = 'all' )";
			$header = '<span class="header1">Альбомы пользователя</span>';
		}
		$perpage = 6;

		$base_query = "SELECT `id_album`, `name`, `image`, DATE_FORMAT(`date`, '%e.%m.%Y') 'date'
					   FROM `album` 
					   WHERE `album`.`id_user`=".$id_user.$f;
		$num_albums	= mysql_num_rows( $this->q($base_query) );

		$all_pages = ceil($num_albums/$perpage);

		// This is needed when we delete the album which is on the last page.
		// In order to prevent overrunning of the pages.
		settype($page, 'integer');
		if($page > $all_pages or $page <= 0)
		$page = 1;

		$_SESSION['alb_ses'] = $checked;
		$_SESSION['alb_page'] = $page;

		$range_bottom = ($page-1)*$perpage;

		$albums = $this->q($base_query." LIMIT $range_bottom, $perpage");

		for($i = 1; $row_albums = mysql_fetch_assoc($albums) and $i < $perpage+1; $i++ ) {
			$num_photos = mysql_num_rows(
			$this->q("SELECT `id_photo`, `scope`
						  FROM `photo`
						  WHERE `id_album`=".$row_albums['id_album'].$ff) 
						  );
						  //$num_photos = $row_albums['num_photos'];
						  $chckd = ($checked == $row_albums['id_album']) ? true : false;

						  $albm[$i] = new album($id_user, $row_albums['id_album'], $num_photos, wordwrap($row_albums['name'], 10, "<br>", 1), $row_albums['image'],
						  $row_albums['date'], $chckd, false);
		}

		$lft_albums = new album_sec($header, $albm, $page, $all_pages, $init_link, $id_user);

		$this->_left = $lft_albums->output();
	}

	function my_album($id_album) {
		$res = $this->q("SELECT `id_album`, `name`, `image`, DATE_FORMAT(`date`, '%e.%m.%Y') 'date'
						 FROM `album` WHERE `id_album`=".$id_album." AND `id_user`=".$_SESSION['id_user']);
		$row_album = mysql_fetch_assoc($res);

		$num_photos = mysql_num_rows( $this->q("SELECT `id_photo` FROM `photo` WHERE `id_album`=".$row_album['id_album']) );

		$albm = new album($_SESSION['id_user'], $row_album['id_album'], $num_photos, $row_album['name'], $row_album['image'],
		$row_album['date'], true, true);


		return $albm;
	}

	function add_group_form($title='', $type='', $descr='', $error='') {
		if(isset($_SESSION['id_user'])) {
			$header = "<span class=header1>Добавление группы</span>";

			$group_sec = new section($header, 4, 3, 1, sh_group_form($title, $type, $descr, $error, 'add_group') );

			$group = new content($group_sec);

			$this->set_content($group);
		} else {
			header('location:enter.php');
		}
	}

	function add_group( $title, $type, $descr, $im_file ) {
		$error = '';
		if( empty($title) )
		$error .= 'Название группы не указано<br>';

		if( empty($type))
		$error .= 'Тип группы не указан<br>';

		$image = "`img`, ";

		$im_type = substr( $im_file['type'], -(strlen($im_file['type'])-strpos($im_file['type'], "/")-1) );
		if($im_file['tmp_name'] and $im_file['tmp_name']==0) {
			if(substr($im_file['type'], 0, strpos($im_file['type'], "/")) == 'image') {
				if( !($im_type=='pjpeg' or $im_type=='jpeg' or $im_type=='gif' or $im_type=='png') ) {
					$error .= "Загрузка изображения данного типа не поддерживается<br>";
				} else {
					$ext = strrchr($im_file['name'], ".");
					$base = "grp".$_SESSION['id_user'].date("YmdHis",time()).$ext;
					$img_bd = "'".$base."', ";
				}
			} else
			$error .= "Файл не является изображением<br>";
		} else {
			$img_bd = "NULL, ";
		}

		if(
		mysql_num_rows( $this->q("SELECT `id_group` FROM `group` WHERE `title` LIKE '".$title."'") )
		) $error .= "Такое название для группы уже используется<br>";

		if( $error == '' ) {
			//do something
			$this->q("INSERT INTO `group` (
			`title`,
			`type`,
			".$image."
			`id_author`,
			`descr`) VALUES ('".addslashes(strip_tags($title))."', 
			'".$type."', ".$img_bd.$_SESSION['id_user'].", '".addslashes(strip_tags($descr))."')");
			$id_group = mysql_insert_id();

			$this->q("INSERT INTO `group_member`(`id_group`, `id_user`, `connected`, `rights`) VALUES($id_group, ".$_SESSION['id_user'].", 'yes', 100)");

			$img = "images/".$base;
			$size_img = getimagesize($im_file['tmp_name']);

			if($size_img[1] <= 48 and $size_img[0] <= 48) {
				if ( copy($im_file['tmp_name'], $img) ) {
					unlink($im_file['tmp_name']);
					//chmod($img, 0644);
				}
			} else {
				resizeimg($im_file['tmp_name'], $img, 48, 48);
			}

			return 1;
		} else {
			return $error;
		}
	}

	function edit_group_form($id_group, $error) {

		$res = $this->q("SELECT * FROM `group` WHERE `id_group`=".$id_group);
		$row = mysql_fetch_assoc($res);
		if(isset($_SESSION['id_user']) and $row['id_author'] == $_SESSION['id_user']) {
			$header = "<span class=header1>Редактирование группы</span>";

			$group_sec = new section($header, 4, 3, 1,
			sh_group_form($row['title'], $row['type'], $row['descr'], $error, 'ed_group', $id_group) );

			$group = new content($group_sec);

			$this->set_content($group);
		}
	}

	function edit_group($id_group, $title, $descr, $im_file) {
		$error = '';
		if( empty($title) )
		$error .= 'Название группы не указано<br>';

		if( !empty($im_file) ) {
			$im_type = substr( $im_file['type'], -(strlen($im_file['type'])-strpos($im_file['type'], "/")-1) );
			if($im_file['tmp_name'] and $im_file['tmp_name']==0) {
				if(substr($im_file['type'], 0, strpos($im_file['type'], "/")) == 'image') {
					if( !($im_type=='pjpeg' or $im_type=='jpeg' or $im_type=='gif' or $im_type=='png') ) {
						$error .= "Загрузка изображения данного типа не поддерживается<br>";
					} else {
						$ext = strrchr($im_file['name'], ".");
						$base = "grp".$_SESSION['id_user'].date("YmdHis",time()).$ext;
						$img_bd = ", `img`='".$base."'";

						// выбираем старую иконку грппы для удаления позже
						$res2 = $this->q("SELECT `img` FROM `group` WHERE `id_group`=".$id_group);
						$row2 = mysql_fetch_assoc($res2);
					}
				} else
				$error .= "Файл не является изображением<br>";
			}
		} else {
			$img_bd = "";
		}

		if(
		mysql_num_rows( $this->q("SELECT `id_group` FROM `group` WHERE `title` LIKE '$title' AND `id_group`!=".$id_group) )
		) $error .= "Такое название для группы уже используется<br>";

		if(empty($error) ) {
			if( $this->q("UPDATE `group` SET `title`='".$title."',  `descr`='".$descr."' ".$img_bd." WHERE `id_group`=".$id_group) ) {

				if($row2['img'] != null)
				//удаление старой картинки
				unlink("images/".$row2['img']);

				$img = "images/".$base;
				$size_img = getimagesize($im_file['tmp_name']);

				if($size_img[1] <= 48 and $size_img[0] <= 48) {
					if ( copy($im_file['tmp_name'], $img) ) {
						unlink($im_file['tmp_name']);
						//chmod($img, 0644);
					}
				} else {
					resizeimg($im_file['tmp_name'], $img, 48, 48);
				}
			}
			return 1;
		}

		return $error;
	}

	function add_album($aname, $scope, $file, &$id_album) {
		$error = "";
		$image = "";
		$img_bd = "";

		$user = $_SESSION['id_user'];

		$image = "`image`, ";

		$im_type = substr( $file['type'], -(strlen($file['type'])-strpos($file['type'], "/")-1) );

		if($file['tmp_name'] and $file['tmp_name']==0) {
			if(substr($file['type'], 0, strpos($file['type'], "/")) == 'image') {
				if( !($im_type=='jpeg' or $im_type=='pjpeg' or $im_type=='gif' or $im_type=='png') ) {
					$error .= "Загрузка изображения данного типа не поддерживается<br>";
					break;
				}
				$ext = strrchr($file['name'], ".");
				$img_bd = "'album".$user.date("YmdHis",time())."$ext', ";

			} else
			$error .= "Файл не является изображением<br>";
		} else {
			$img_bd = "'emptyalbum.gif', ";
		}

		if ( empty($aname) ) {
			$error .= "Название альбома не задано<br>";
		}

		if( empty($scope) ) $error .= 'Область видимости не задана<br>';

		if($error == "") {
			$this->q("INSERT INTO `album` (
						`name`,
						`ascope`, 
						$image 
						`date`, 
						`id_user`) VALUES (
						'".addslashes(strip_tags($aname))."',
						'".$scope[0]."',
						".$img_bd."
						NOW(),
						".$_SESSION['id_user']."
						) ");
			$id_album = mysql_insert_id();

			if( !file_exists("../files/".$_SESSION['id_user']) )
			mkdir( "../files/".$_SESSION['id_user'] );

			mkdir("../files/".$_SESSION['id_user']."/".$id_album);


			$img = "../files/".$_SESSION['id_user']."/".$id_album."/album".$user.date("YmdHis",time()).$ext;
			$size_img = getimagesize($file['tmp_name']);

			if($size_img[1] <= 100 and $size_img[0] <= 100) {
				if ( copy($file['tmp_name'], $img) ) {
					unlink($file['tmp_name']);
					//chmod($img, 0644);
				}
			} else {
				resizeimg2($file['tmp_name'], $img, 100, 100);
			}

			return 1;
		} else
		return $error;
	}

	function edit_album_form($id_alb, $alb_page, $ilink, $error='') {

		//$edit_alb = $page->my_album($id_alb);

		$res = $this->q("SELECT `id_album`, `name`, `ascope`,`image`, DATE_FORMAT(`date`, '%e.%m.%Y') 'date'
						 FROM `album` WHERE `id_album`=".$id_alb." AND `id_user`=".$_SESSION['id_user']);
		$row_album = mysql_fetch_assoc($res);

		$num_photos = mysql_num_rows( $this->q("SELECT `id_photo` FROM `photo` WHERE `id_album`=".$row_album['id_album']) );

		$edit_alb = new album($_SESSION['id_user'], $row_album['id_album'], $num_photos, $row_album['name'], $row_album['image'],
		$row_album['date'], true, true);

		$alb = '<td width="110" valign="bottom" class="text" height="169">'.$edit_alb->output().'</td>
        <td colspan=6></td>
        </tr>
        <tr> 
          <td valign="top" width=25>&nbsp;</td>';

		$scope[0] = $row_album['ascope'];
		$t = $alb.sh_album_form($id_alb, 'edit_album', $scope, $row_album['name'], $error);
		$add_form_sec = new section( "<span class=header1>Редактирование альбома</span>
			<span class='cin'>&nbsp;&raquo;&nbsp; <a href='index.php?go=albums&alb_num=$id_alb' class='link'>Фото альбома</a>
			</span>", 5, 3, 1, $t);


		$emp = new content($add_form_sec);

		$this->set_content($emp);

		$this->left_albums($id_alb, $alb_page, $ilink, $_SESSION['id_user']);
	}

	function edit_album($aname, $scope, $file, $id_album) {
		$error = "";
		$set_im = "";
		$img_bd = "";
		$user = $_SESSION['id_user'];

		$in_path = "../files/".$_SESSION['id_user']."/".$id_album;

		if($file['tmp_name'] and $file['tmp_name']==0) {
			if(substr($file['type'], 0, strpos($file['type'], "/")) == 'image') {
				//if( !empty($file['tmp_name']) ) {
				$ext = strrchr($file['name'], ".");
				$img_bd = "'album".$user.date("YmdHis",time())."$ext'";
				$img = $in_path."/album".$user.date("YmdHis",time()).$ext;

				$size_img = getimagesize($file['tmp_name']);

				if($size_img[1] <= 100 or $size_img[0] <= 100) {
					if ( copy($file['tmp_name'],$img) ) {
						unlink($file['tmp_name']);
						//chmod($img, 0644);
						$image = "`image`, ";
					}
				} else {
					resizeimg2($file['tmp_name'], $img, 100, 100);
					$image = "`image`, ";
				}
				$set_im = ", `image`=".$img_bd;
			} else
			$error .= "Файл не является изображением<br>";
		}

		if ( empty($aname) ) {
			$error .= "Название альбома не задано<br>";
		}

		if ( empty($scope) ) {
			$error .= "Область видимости не задана<br>";
		}

		if($error == "") {
			$res = $this->q("SELECT `image` FROM `album` WHERE `id_album`=".$id_album." AND `image` NOT LIKE 'sml%'");
			$row = mysql_fetch_assoc($res);

			if($row['image'] != 'emptyalbum.gif'
			and file_exists($in_path.$row['image'])
			and is_file($in_path.$row['image'])) unlink($in_path.$row['image']);

			$res2 = $this->q("SELECT `ascope` FROM `album` WHERE `id_album`=".$id_album);

			$row2 = mysql_fetch_assoc($res2);

			$this->q("UPDATE `album` SET `name`='".addslashes(strip_tags($aname))."'".$set_im.", `ascope`='".$scope[0]."' WHERE `id_album`=".$id_album.
			" AND `id_user`=".$_SESSION['id_user']);

			if($row2['ascope'] != $scope[0])
			$this->q("UPDATE `photo` SET `scope`='".$scope[0]."' WHERE `id_album`=".$id_album);

			return 1;
		} else
		return $error;
	}

	function add_photo_form($album, $tag_set, $album_set, $photoname='', $error='', $new_alb='', $tag='',
	$descr='', $scopebox='', $scopeboxo='', $groupbox=0) {
		if( isset($_SESSION['id_user']) ) {
			$alb_name = mysql_fetch_assoc($this->q("SELECT `name` FROM `album` WHERE `id_album`=".$album));

			$groups = $this->groupbox($groupbox);

			$cmt_rgt = 'all';
			$form = sh_photo_form($album, $alb_name['name'], $photoname, $tag_set, $album_set, $error, $new_alb, $tag, $descr, $scopebox, $scopeboxo, $groups, $cmt_rgt);

			$add_form_sec = new section( "<span class=header1>Добавить новое фото</span>",
			5, 4, 1, $form);

			$emp = new content($add_form_sec);

			$this->set_content($emp);
		} else {
			header('location:enter.php');
		}
	}

	function add_photo($photoname, $tag_select, $tag, $descr, $scope, $scope_orgnl, $group_box, $file, &$id_album,
	$alb_sel, $new_alb, $ignore, $cmt_rgt) {

		$error = '';

		$user = $_SESSION['id_user'];

		if(empty($scope)) $error .= 'Область видимости не задана<br>';

		$im_type = substr( $file['type'], -(strlen($file['type'])-strpos($file['type'], "/")-1) );

		if(!$file['tmp_name'])
		$error .= 'Фото не выбрано<br>';
		elseif( substr($file['type'], 0, strpos($file['type'], "/")) != 'image' )
		$error .= "Файл не является изображением<br>";
		elseif ( substr($file['type'], 0, strpos($file['type'], "/")) == 'image' ) {
			if( !($im_type=='jpeg' or $im_type=='pjpeg' or $im_type=='gif' or $im_type=='png' or $im_type == 'x-png') )
			$error .= "Загрузка изображения данного типа не поддерживается<br>";
		}

		if(!$id_album) {
			if($alb_sel == 0 and empty($new_alb))
			$error .= "Альбом не выбран<br>";
			elseif (!empty($new_alb) and $error == "") {
				$albscope[0] = 'all';
				$this->add_album($new_alb, $albscope,'', $id_album);

			} elseif ($alb_sel != 0)
			$id_album = $alb_sel;
		}

		if($error == "") {

			if( empty($photoname) ) {
				$photoname = substr($file['name'], 0, strrpos($file['name'], ".") );
			}

			if( $im_type=='jpeg' or $im_type=='pjpeg' ) {
				$exif = exif_read_data($file['tmp_name'], 0, true);
				$ort = $exif['IFD0']['Orientation'];
			}
			if( $exif['IFD0']['DateTime'] != null) {
				$datetime = explode(" ", $exif['IFD0']['DateTime']);
				$date1 = explode(":", $datetime[0]);
				$time2 = explode(":", $datetime[1]);

				$date_ins = "'".date("Y-m-d H:i:s", mktime($time2[0], $time2[1], $time2[2], $date1[1], $date1[2], $date1[0]) )."'";
			} else
			$date_ins = "NULL";

			//$ort = $exif['IFD0']['Orientation'];
			//$t = date("YmdHis",$exif['IFDO']['DateTime']);

			$ext = strrchr($file['name'], ".");
			$root_name = $user.date("YmdHis",time());
			$size_photo = getimagesize( $file['tmp_name'] );

			if( !file_exists("../files/".$_SESSION['id_user']) )
			mkdir( "../files/".$_SESSION['id_user']);

			if( !file_exists("../files/".$_SESSION['id_user']."/".$id_album) )
			mkdir("../files/".$_SESSION['id_user']."/".$id_album);


			$path = "../files/".$_SESSION['id_user']."/".$id_album."/";
			$photo = $path.$root_name.$ext;

			$degrees = 0;

			if(!$ignore) {
				switch ($ort) {
					case 8 : $degrees = 90; break;
					case 6 : $degrees = -90; break;
					case 3 : $degrees = 180; break;
				}
			}
			/*
			switch ($ort) {
			case 8 : $degrees = 270; break;
			case 6 : $degrees = 90; break;
			case 3 : $degrees = 180; break;
			}
			*/
			if($size_photo[1] <= 100 and $size_photo[0] <= 100) {
				if ( copy($file['tmp_name'], $photo) ) {
					$values = "'".$root_name.$ext."', '".$root_name.$ext."', '".$root_name.$ext."', '".$root_name.$ext."'";
				}
				//chmod($photo, 0644);

			} elseif($size_photo[1] <= 152 and $size_photo[0] <= 152) {
				$sml_photo = $path."sml".$root_name.$ext;

				resizeimg2($file['tmp_name'], $sml_photo, 100, 100, $degrees);

				//chmod($photo, 0644);
				//chmod($sml_photo, 0644);

				$values = "'sml".$root_name."$ext', '".$root_name."$ext', '".$root_name."$ext', '".$root_name.$ext."'";
				copy($file['tmp_name'], $photo);
			} elseif($size_photo[0] <= 622) {
				$mdm_photo = $path."mdm".$root_name.$ext;
				$sml_photo = $path."sml".$root_name.$ext;

				if( $size_photo[1] < $size_photo[0] )
				resizeimg($file['tmp_name'], $mdm_photo, 175, 152, $degrees);
				else
				resizeimg($file['tmp_name'], $mdm_photo, 153, 175, $degrees);

				resizeimg2($file['tmp_name'], $sml_photo, 100, 100, $degrees);

				$values = "'sml".$root_name.$ext."', 'mdm".$root_name.$ext."', '".$root_name.$ext."', '".$root_name.$ext."'";

				copy($file['tmp_name'], $photo);
			} else {
				$maxi_photo = $path."maxi".$root_name.$ext;
				$mdm_photo = $path."mdm".$root_name.$ext;
				$sml_photo = $path."sml".$root_name.$ext;

				resizeimg2($file['tmp_name'], $sml_photo, 100, 100, $degrees);

				if( $size_photo[1] < $size_photo[0] )
				resizeimg($file['tmp_name'], $mdm_photo, 175, 152, $degrees);
				else
				resizeimg($file['tmp_name'], $mdm_photo, 153, 175, $degrees);

				// variable should be at least $size_photo[1]
				resizeimg($file['tmp_name'], $maxi_photo, 618, 618, $degrees);
				//stop

				copy($file['tmp_name'], $photo);
				$values = "'sml".$root_name.$ext."', 'mdm".$root_name.$ext."', 'maxi".$root_name.$ext."', '".$root_name.$ext."'";
			}

			unlink($file['tmp_name']);
			/*
			if( !empty($month) and !empty($month) and !empty($month) ) {
			settype($month, 'integer');
			settype($day, 'integer');
			settype($year, 'integer');

			$date_ins = "'".date("Y-m-d", mktime(0, 0, 0, $month, $day, $year))."'";
			} else {
			$date_ins = "NULL";
			}
			*/
			//$time_ins = "'$time'";

			$field = $value = "";
			if( isset($cmt_rgt) ) {
				$field = ", `cmt_rgt`";
				$value = ", '$cmt_rgt'";
			}

			$this->q("INSERT INTO `photo` (`small`, `medium`, `maxi`, `original`, `pubdate`, `id_user`,
										   `id_album`, `shootdate`, `descr`, `scope`, `scope_orgnl`, `name`, `ip`".$field.") 
					  VALUES (
						".$values.", NOW(), ".$_SESSION['id_user'].", ".$id_album.
						", ".$date_ins.", '".addslashes(strip_tags($descr))."', '".$scope[0]."', '".$scope_orgnl."', '".addslashes(strip_tags($photoname))."', INET_ATON('".get_ip()."')".$value.") ");
						$id_photo = mysql_insert_id();

						// group manipulation
						$group_values = '';

						if( isset($group_box) ) {
							$group_values = "($id_photo, ".array_shift($group_box).")";
							foreach ($group_box as $id_group) {
								$group_values .= ", ($id_photo, $id_group)";
							}
							$this->q("INSERT INTO `group_photo`(`id_photo`, `id_group`) VALUES ".$group_values);
						}
						//--

						// tag manipulation
						if(!empty($tag)) {
							$tag_array = explode(", ", $tag);
							foreach ($tag_array as $tag_value) {
								$res = $this->q("SELECT `id_tag` FROM `tag` WHERE `name` LIKE '".addslashes($tag_value)."'");
								$tagexist = mysql_num_rows($res);
								if($tagexist) {
									//$this->q("UPDATE `tag` SET `name`='$tag_value', `rate`=NOW() WHERE `name` LIKE '$tag_value'");
									$row = mysql_fetch_assoc($res);
									$id_tag = $row['id_tag'];
								} else {
									$this->q("INSERT INTO `tag`(`name`, `id_user`)
								  VALUES('".trim(addslashes(strip_tags($tag_value)))."', $user)");
									$id_tag = mysql_insert_id();
								}
								$this->q("INSERT INTO `tag_photo`(`id_tag`, `id_photo`, `rate`) VALUES($id_tag, $id_photo, NOW())");
							}
						}
						//---

						$res = $this->q("SELECT `small` FROM `photo` WHERE `id_album`=".$id_album);
						$alb = mysql_fetch_assoc($this->q("SELECT `image` FROM `album` WHERE `id_album`=".$id_album));

						if (mysql_num_rows($res)==1 and $alb['image'] == 'emptyalbum.gif') {
							$img = mysql_fetch_assoc($res);
							$this->q("UPDATE `album` SET `image`='".$img['small']."' WHERE `id_album`=".$id_album);
						}

						return 1;

		} else {
			return $error;
		}
	}

	function groupbox($checked, $mode='add') {

		$dis = ($mode == 'ed') ? ' disabled ' : '';

		$groups = '';

		/*
		$res3 = $this->q("SELECT `id_group`, `title` FROM `group` WHERE `id_author`=".$_SESSION['id_user']);
		while ( $row3 = mysql_fetch_assoc($res3) ) {
		if( is_array($checked) ) {
		$chck = ( in_array($row3['id_group'], $checked) ) ? 'checked'.$dis : '' ;
		} else {
		$chck = ( $row3['id_group'] == $checked ) ? 'checked'.$dis : '' ;
		}
		$groups .= '<input name="groupbox[]" type="checkbox" value='.$row3['id_group'].' '.$chck.'>'.$row3['title']."&nbsp;&nbsp;";
		}
		*/

		if($_SESSION['rights'] < 100) {
			$res3 = $this->q("SELECT `title`, `group`.`id_group` 'id_group'
							  FROM `group_member`, `group` 
							  WHERE `id_user`=".$_SESSION['id_user']." AND `connected` LIKE 'yes' AND `group_member`.`id_group`= `group`.`id_group`");
		} else
		$res3 = $this->q("SELECT `title`, `id_group` FROM `group`");

		while ( $row3 = mysql_fetch_assoc($res3) ) {

			if( is_array($checked) ) {
				$chck = ( in_array($row3['id_group'], $checked) ) ? 'checked'.$dis : '' ;
			} else {
				$chck = ( $row3['id_group'] == $checked ) ? 'checked'.$dis : '' ;
			}
			$groups .= '<input name="groupbox[]" type="checkbox" value='.$row3['id_group'].' '.$chck.'>'.$row3['title']."&nbsp;&nbsp;";
		}

		return $groups;
	}

	function ed_photo_form($id_photo, $error='') {
		$res = $this->q("SELECT `id_album`, `id_user`, `descr`, `scope`,
								`scope_orgnl`, `name`, `cmt_rgt`
						 FROM `photo` WHERE `id_photo`=".$id_photo." AND `id_user`=".$_SESSION['id_user']);
		$row = mysql_fetch_assoc($res);

		$tag_set = make_taglist();
		$album_set = make_albumlist($row['id_album'], $row['id_user']);

		$res2 = $this->q("SELECT `tag`.`name` 'tagname' FROM `tag_photo`
						  LEFT JOIN `tag` ON `tag`.`id_tag`=`tag_photo`.`id_tag` 
						  WHERE `id_photo`=".$id_photo); 

		if( mysql_num_rows($res2) ) {
			$row2 = mysql_fetch_assoc($res2);
			$tag_str = $row2['tagname'];
			while ($row2 = mysql_fetch_assoc($res2)) {
				$tag_str .=", ".$row2['tagname'];
			}
		}

		//
		$res3 = $this->q("SELECT `id_group` FROM `group_photo` WHERE `id_photo`=".$id_photo);
		while ($row3 = mysql_fetch_assoc($res3) ) {
			$check[] = $row3['id_group'];
		}
		$groups = $this->groupbox($check, 'ed');

		//--

		$scope[0] = $row['scope'];
		$form = sh_photo_form(0, '', $row['name'], $tag_set, $album_set, $error, '', $tag_str, $row['descr'],
		$scope, $row['scope_orgnl'], $groups, $row['cmt_rgt'],'ed_photo', $id_photo);

		$ed_form_sec = new section( "<span class=header1>Редактирование фото</span>",
		5, 4, 1, $form);


		$comments = $this->q("SELECT `comment`.`comment_txt` 'commenttxt', `comment`.`date` 'date', u.`name` 'user', `anonymous`
							  FROM `comment` 
							  LEFT JOIN `user` u ON `comment`.`id_user` = u.`id_user`
							  WHERE `comment`.`id_photo`=".$id_photo);

		$num_com = mysql_num_rows($comments);

		$emp = new content($ed_form_sec);

		$this->set_content($emp);
	}

	function ed_photo($id_photo, $photoname, $tag_select, $tag, $descr,
	$scope, $scope_o, &$id_album, $alb_sel, $new_alb, $group_box, $cmt_rgt) {

		$error = '';
		$user = $_SESSION['id_user'];

		if(empty($scope)) $error .= 'Область видимости не задана<br>';

		if(!$id_album) {
			if($alb_sel == 0 and empty($new_alb))
			$error .= "Альбом не выбран<br>";
			elseif (!empty($new_alb) and $error== "") {
				$albscope[0] = 'onlyme';
				$this->add_album($new_alb, $albscope, '', $id_album);

			} elseif ($alb_sel != 0)
			$id_album = $alb_sel;
		}

		if($error == "") {
			$ext = strrchr($file['name'], ".");
			$root_name = $user.date("YmdHis",time());
			$size_photo = getimagesize($file['tmp_name']);

			// deletion of old pictures
			$res = $this->q("SELECT `small`, `maxi`, `medium`, `original`, `id_album` FROM `photo`
							 WHERE `id_photo`=".$id_photo." AND `id_user`=".$_SESSION['id_user']);
			$row = mysql_fetch_assoc($res);


			if($row['id_album'] != $id_album) {
				$path = "../files/".$_SESSION['id_user']."/";

				if( copy($path.$row['id_album']."/".$row['original'], $path.$id_album."/".$row['original']) ) {
					unlink($path.$row['id_album']."/".$row['original']);
				} else
				exit;

				if( $row['original'] != $row['maxi']) {
					if( copy($path.$row['id_album']."/".$row['maxi'], $path.$id_album."/".$row['maxi']) )
					unlink($path.$row['id_album']."/".$row['maxi']);
					else
					exit;
				}

				if( $row['original'] != $row['medium']) {
					if( copy($path.$row['id_album']."/".$row['medium'], $path.$id_album."/".$row['medium']) )
					unlink($path.$row['id_album']."/".$row['medium']);
					else
					exit;
				}

				if( $row['original'] != $row['small'] ) {
					if( copy($path.$row['id_album']."/".$row['small'], $path.$id_album."/".$row['small']) )
					unlink($path.$row['id_album']."/".$row['small']);
					else
					exit;
				}

				// manipulation with album's image
				$res2 = $this->q("SELECT * FROM `photo` WHERE `id_album`=".$row['id_album']." AND `id_user`=".
				$_SESSION['id_user']." ORDER BY `pubdate` ASC");

				if(mysql_num_rows($res2) == 1)
				$this->q("UPDATE `album` SET `image`='emptyalbum.gif' WHERE `id_album`=".$row['id_album'].
				" AND `id_user`=".$_SESSION['id_user']." AND `image` LIKE 'sml%'");
				else {
					$i=2;
					while ($i--) {
						$row2 = mysql_fetch_assoc($res2);
						if($row2['small'] != $row['small']) break;
					}
					$this->q("UPDATE `album` SET `image`='".$row2['small']."' WHERE `id_album`=".$row2['id_album'].
					" AND `id_user`=".$_SESSION['id_user']." AND `image` LIKE 'sml%' AND `image` LIKE '".$row['small']."'");
				}

				$this->q("UPDATE `album` SET `image`='".$row['small']."' WHERE `id_album`=".$id_album.
				" AND `id_user`=".$_SESSION['id_user']." AND `image` LIKE 'emptyalbum.gif'");
				//--
			}

			$update = ", `cmt_rgt`='all' ";
			if(isset($cmt_rgt))
			$update = ", `cmt_rgt`='".$cmt_rgt."' ";

			$this->q("UPDATE `photo` SET `name`='".htmlspecialchars(strip_tags($photoname))."', `scope`='".$scope[0]."', `scope_orgnl`='".$scope_o."',
					  `descr`='".addslashes(strip_tags($descr))."', `id_album`=".$id_album.$update." 
					  WHERE `id_photo`=".$id_photo." AND `id_user`=".$_SESSION['id_user']);

			/*
			$res3 = $this->q("SELECT `id_tag` FROM `tag_photo` WHERE `id_photo`=".$id_photo);

			while ($row3 = mysql_fetch_assoc($res3)) {
			$numtag = mysql_num_rows($this->q("SELECT * FROM `tag_photo` WHERE `id_tag`=".$row3['id_tag']) );
			if( !$numtag )
			$this->q("DELETE FROM `tag` WHERE `id_tag`=".$row3['id_tag']);
			}*/

			$this->q("DELETE FROM `tag_photo` WHERE `id_photo`=".$id_photo);


			if(!empty($tag)) {
				$tag_array = explode(", ", $tag);
				foreach ($tag_array as $tag_value) {
					$res = $this->q("SELECT `id_tag` FROM `tag` WHERE `name` LIKE '".addslashes($tag_value)."'");
					$tagexist = mysql_num_rows($res);

					if($tagexist) {
						$row = mysql_fetch_assoc($res);
						$id_tag = $row['id_tag'];

					} else {
						$this->q("INSERT INTO `tag`(`name`) VALUES('".trim(addslashes(strip_tags($tag_value)))."')");
						$id_tag = mysql_insert_id();
					}
					$this->q("INSERT INTO `tag_photo`(`id_tag`, `id_photo`, `rate`) VALUES($id_tag, $id_photo, NOW())");
				}
			}

			$this->q("DELETE FROM `tag`
					  WHERE `tag`.`id_tag` NOT IN 
					  		(SELECT `id_tag` FROM `tag_photo` 
					  		 WHERE `tag_photo`.`id_tag`=`tag`.`id_tag`)");


			$group_values = '';

			if( isset($group_box) ) {
				$group_values = "($id_photo, ".array_shift($group_box).")";
				foreach ($group_box as $id_group) {
					$group_values .= ", ($id_photo, $id_group)";
				}
				$this->q("INSERT INTO `group_photo`(`id_photo`, `id_group`) VALUES ".$group_values);
			}

			return 1;
		} else {
			return $error;
		}
	}

	function del_photo($id_photo) {
		//
		$res = $this->q("SELECT `small`, `medium`, `maxi`, `original`, `id_album` FROM `photo` WHERE `id_photo`=".$id_photo.
		" AND `id_user`=".$_SESSION['id_user']);
		$row = mysql_fetch_assoc($res);
		$in_path = "../files/".$_SESSION['id_user']."/".$row['id_album']."/";

		$photo_path = $row['small'];
		if( file_exists($in_path.$row['small'])
		and
		is_file($in_path.$row['small']) )
		unlink($in_path.$row['small']);

		if( file_exists($in_path.$row['medium'])
		and
		is_file($in_path.$row['medium']) )
		unlink($in_path.$row['medium']);

		if(file_exists($in_path.$row['original'])
		and
		is_file($in_path.$row['original']) )
		unlink($in_path.$row['original']);

		if(file_exists($in_path.$row['maxi'])
		and
		is_file($in_path.$row['maxi']) )
		unlink($in_path.$row['maxi']);

		// manipulation with album's image
		$res2 = $this->q("SELECT * FROM `photo` WHERE `id_album`=".$row['id_album']." AND `id_user`=".
		$_SESSION['id_user']." ORDER BY `pubdate` ASC");
		if(mysql_num_rows($res2) == 1)
		$this->q("UPDATE `album` SET `image`='emptyalbum.gif' WHERE `id_album`=".$row['id_album'].
		" AND `id_user`=".$_SESSION['id_user']." AND `image` LIKE 'sml%'");
		else {

			$i=2;
			while ($i--) {
				$row2 = mysql_fetch_assoc($res2);
				if($row2['small'] != $photo_path) break;
			}
			$this->q("UPDATE `album` SET `image`='".$row2['small']."' WHERE `id_album`=".$row2['id_album'].
			" AND `id_user`=".$_SESSION['id_user']." AND `image` LIKE 'sml%' AND `image` LIKE '$photo_path'");
		}
		//--

		$this->q("DELETE FROM `photo` WHERE `id_photo`=".$id_photo." AND `id_user`=".$_SESSION['id_user']);
		$this->q("DElETE FROM `comment` WHERE `id_photo`=".$id_photo);

		$res = $this->q("SELECT `id_tag` FROM `tag_photo` WHERE `id_photo`=".$id_photo);
		if(mysql_num_rows($res)) {
			/*
			$row = mysql_fetch_assoc($res);
			$in = "IN (".$row['id_tag'];
			while ($row = mysql_fetch_assoc($res))  {
			$in .= ", ".$row['id_tag'];
			}
			$in .=")";
			$this->q("UPDATE `tag` SET `rank`=`rank`-1 WHERE `id_tag` ".$in);
			*/

			$this->q("DELETE FROM `tag_photo` WHERE `id_photo`=".$id_photo);
			while ($row = mysql_fetch_assoc($res))  {
				$res3 = $this->q("SELECT * FROM `tag_photo` WHERE `id_tag`=".$row['id_tag']);
				if(!mysql_num_rows($res3)) {
					$this->q("DELETE FROM `tag` WHERE `id_tag`=".$row['id_tag']);
				}
			}
		}

		// удаление из групп
		$this->q("DELETE FROM `group_photo` WHERE `id_photo`=".$id_photo);
	}

	function del_album($id_album) {
		//
		$in_path = "../files/".$_SESSION['id_user']."/".$id_album."/";

		$res = $this->q("SELECT * FROM `photo` WHERE `id_album`=".$id_album." AND `id_user`=".$_SESSION['id_user']);
		if(mysql_num_rows($res)) {
			while ($row = mysql_fetch_assoc($res)) {
				$this->del_photo($row['id_photo']);
			}
		}

		$res = $this->q("SELECT `image` FROM `album` WHERE `id_album`=".$id_album." AND `id_user`=".$_SESSION['id_user']);
		$row = mysql_fetch_assoc($res);

		if(file_exists($in_path.$row['image']) and is_file($in_path.$row['image']) and $row['image'] != 'emptyalbum.gif')
		unlink($in_path.$row['image']);

		$this->q("DELETE FROM `album` WHERE `id_album`=".$id_album." AND `id_user`=".$_SESSION['id_user']);
		rmdir($in_path);
	}

	function profile_edit($id_user, $fio, $name, $email, $hideemail, $error) {

		if( $id_user == $_SESSION['id_user']) {
			$res = $this->q("SELECT * FROM `user` WHERE `id_user`=".$id_user);
			$row = mysql_fetch_assoc($res);
			$edit_form = new section("<span class=header1>Изменение профиля</span>", 4, 3, 1,
			sh_reg($error, $row['fio'], $row['name'], $row['domain'], $row['email'], $row['hideemail'], $id_user) );
		}


		$edit_form_con = new content($edit_form);
		$this->set_content($edit_form_con);
	}

	function first_alb($id_user) {
		$row_album = mysql_fetch_assoc($this->q("SELECT `id_album` FROM `album` WHERE `id_user`=".$id_user." LIMIT 0, 1"));
		return $row_album['id_album'];
	}

	function id_user_from_domain($domain) {
		$row = mysql_fetch_assoc($this->q("SELECT `id_user` FROM `user` WHERE `domain` LIKE '$domain'"));
		return $row['id_user'];
	}

	function show_photos($page, $mode, $id_tag=0, $id_user=0, $id_album=0) {

		//Считываем текущее время
		$mtime = microtime();
		//Разделяем секунды и миллисекунды
		$mtime = explode(" ",$mtime);
		//Составляем одно число из секунд и миллисекунд
		$mtime = $mtime[1] + $mtime[0];
		//Записываем стартовое время в переменную
		$tstart = $mtime;


		$select = "SELECT `id_photo`, `photo`.`name` 'pname', `medium`, `original`, `photo`.`main_page` 'main_page',
						  DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'publdate', 
						  alb.`name` 'albname', `photo`.`id_album` 'idalbum', `scope`, `descr`";

		$join = "LEFT JOIN `user` usr ON usr.`id_user` = `photo`.`id_user` LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`";

		$address = 'index.php?go='.$mode;

		if($mode == 'all_myphotos') {
			$photoalb_h = '<span class="cur">Мои фото</span><span class="cin">&nbsp;&nbsp;&raquo;&nbsp;</span>
					<a href="index.php?go=add_photo&alb_num=0" class="link">Добавить фото</a>';

			$base_query = $select."
				FROM `photo` ".$join."
				WHERE `photo`.`id_user`=".$_SESSION['id_user']." ORDER BY `pubdate` DESC";
		} else {

			if( isset($_SESSION['id_user']) ) $f = "( `scope`='friends' AND ".$_SESSION['id_user']." IN (
				SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= idauthor) OR `photo`.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND `photo`.`id_user`=".$_SESSION['id_user'].") OR  (`scope` = 'all') ";
			else $f = "(`scope` = 'all') ";

			if ($mode == 'all_lastphotos') {
				$photoalb_h = '<span class="cur">Последние фото</span>';

				$base_query = $select.", usr.`name` 'author', usr.`id_user` 'idauthor'
						     FROM `photo` ".$join."	
						     WHERE ".$f." AND usr.`pb_ban`='no'
							 ORDER BY `pubdate` DESC";		
			} elseif ($mode == 'all_popphotos') {
				$photoalb_h = '<span class="cur">Популярные фото</span>';

				$base_query = $select.", usr.`name` 'author', usr.`id_user` 'idauthor', `rating`
						     FROM `photo` ".$join."	
						     WHERE ".$f." AND usr.`pb_ban`='no'
							 ORDER BY `rating` DESC, `pubdate` ASC";						
			} elseif ($mode == 'photo_tag') {
				$base_query = $select.", usr.`name` 'author', usr.`id_user` 'idauthor', `rating`, `tag`.`name` 'tagname'
						     FROM `photo` $join LEFT JOIN `tag` ON `tag`.`id_tag`=$id_tag
						     			  	
						     WHERE ( ".$f." ) AND `id_photo` IN 
						     	(SELECT `id_photo` FROM `tag_photo` WHERE `id_tag`=".$id_tag.")
							 ORDER BY `pubdate` DESC";
				$address = $address.'&id_tag='.$id_tag;
			} elseif ($mode == 'usrsphoto') {
				$base_query = $select.", usr.`name` 'author', usr.`id_user` 'idauthor', `rating`
						     FROM `photo` ".$join."	
						     WHERE ( ".$f." ) AND usr.`id_user`=".$id_user."
							 ORDER BY `pubdate` DESC";
				$address .='&id_user='.$id_user;
			} elseif ($mode == 'fr_lastphoto') {

				$base_query = $select.", usr.`name` 'author', usr.`id_user` 'idauthor', `rating`
				FROM `photo` ".$join."	
				WHERE ( ".$f." ) AND `photo`.`id_user` IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`=".$_SESSION['id_user'].") 
				ORDER BY `pubdate` DESC";							
			} elseif ($mode == 'usr_albs') {

				if($id_album == 0) {
					$id_album = $this->first_alb($id_user);

					//$row_album = mysql_fetch_assoc($this->q("SELECT `id_album` FROM `album` WHERE `id_user`=".$id_user." LIMIT 0, 1"));
					//$id_album = $row_album['id_album'];
				}
				if( isset($_SESSION['id_user']) )
				$ff = " AND ( ( `ascope`='friends' AND ".$_SESSION['id_user']." IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= ".$id_user.") OR alb.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `ascope`='onlyme' AND alb.`id_user`=".$_SESSION['id_user'].") OR  (`ascope` = 'all') )";				
				else
				$ff = " AND ( `ascope` = 'all' )";

				$base_query = $select.", `photo`.`id_user` 'idauthor', usr.`name` 'username'
							   FROM `photo` ".$join." 
							   WHERE ( ".$f." ) ".$ff." AND alb.`id_album`=".$id_album." 
							   ORDER BY `pubdate` DESC";
				$address =  'index.php?go=albums&id_user='.$id_user.'&alb_num='.$id_album;
			}
		}

		$per_page = 9;
		$all_photos = mysql_num_rows($this->q($base_query));

		$_SESSION['ph_page'] = $page;

		$all_pages = ceil($all_photos/$per_page);

		if($page > $all_pages and $all_pages != 0) $page = $all_pages;

		$range_bottom = ($page - 1)*$per_page;


		$left_col = 11; $right_col = 3;
		$navig_box = navig($address, $page, $all_pages, ($left_col+$right_col) );
		$alb_photos = $this->q($base_query." LIMIT $range_bottom, $per_page");

		$noalbum = false;
		$i = 0;

		if($mode == 'all_myphotos') {
			if(mysql_num_rows($alb_photos)) {
				while ($row_alb_photos = mysql_fetch_assoc($alb_photos) and $i < 9) {
					$comments = $this->q("SELECT * FROM `comment` WHERE `id_photo`=".$row_alb_photos['id_photo']);
					$num_com = mysql_num_rows($comments);
					$albpht[$i++] = new medium_photo($row_alb_photos['id_photo'], $row_alb_photos['pname'], $row_alb_photos['medium'],
					$row_alb_photos['original'], $row_alb_photos['publdate'], $num_com, $row_alb_photos['scope'],
					$row_alb_photos['descr'], $row_alb_photos['tag'], $row_alb_photos['idalbum'], $_SESSION['user'],
					$_SESSION['id_user'], 0, 'allow', $row_alb_photos['albname']);
				}
				//$band = '<span class="header1">&nbsp;</span>
				// <a href="index.php?go=band&id_album='.$row_alb_photos['idalbum'].'&id_user='.$_SESSION['id_user'].'" class=link>лента</a>&nbsp;';
			} else {
				//$band = '';
			}
		} else {
			if(mysql_num_rows($alb_photos)) {
				while ($row_alb_photos = mysql_fetch_assoc($alb_photos) and $i < 9) {
					$comments = $this->q("SELECT * FROM `comment` WHERE `id_photo`=".$row_alb_photos['id_photo']);
					$num_com = mysql_num_rows($comments);
					$albpht[$i++] = new medium_photo($row_alb_photos['id_photo'], $row_alb_photos['pname'], $row_alb_photos['medium'],
					$row_alb_photos['original'], $row_alb_photos['publdate'], $num_com, "", $row_alb_photos['descr'],
					$row_alb_photos['tag'], $row_alb_photos['idalbum'], $row_alb_photos['author'],
					$row_alb_photos['idauthor'], 0, 'forbid', $row_alb_photos['albname'], $row_alb_photos['main_page']);
				}
				if($mode == 'usr_albs') {
					$band = '<span class="cur">&nbsp;</span>
						     <a href="index.php?go=band&id_album='.$id_album.'&id_user='.$id_user.'&ph_page='.$page.'" class=link>лента</a>&nbsp;';
				} elseif($mode == 'usrsphoto') {
					$band = '<span class="cur">&nbsp;</span>
						     <a href="index.php?go=band&mode='.$mode.'&id_user='.$id_user.'&ph_page='.$page.'" class=link>лента</a>&nbsp;';
				} else {
					$band = '<span class="cur">&nbsp;</span>
						     <a href="index.php?go=band&mode='.$mode.'&ph_page='.$page.'" class=link>лента</a>&nbsp;';	
				}
			} else {
				$band = '';
			}
		}

		$html = '';
		if($mode == 'photo_tag') {
			$row = mysql_fetch_assoc($this->q($base_query));
			$address = $address.'&id_tag='.$id_tag;
			if( !empty($row['tagname']) )
			$tag = '<span class="cin">&nbsp;&raquo;&nbsp;</span>
						   <span class="cur">'.$row['tagname'].'</span>';

			$photoalb_h = '<span class="cur">Фотографии по тегу </span> '.$tag;
		} elseif($mode == 'usrsphoto') {

			$row = mysql_fetch_assoc($this->q($base_query));
			$address = $address.'&id_user='.$id_user;
			$photoalb_h = '<span class="cur">Фотографии пользователя </span> <span class="cin">&nbsp;&raquo;&nbsp;</span>
						   <a href="index.php?go=profile&id_user='.$id_user.'" class="user">'.$row['author'].'</a>';

			$html = '<td colspan="11" valign="top" align="left" height="40"></td>
				   <td width=51 colspan=2 valign=top >'.$band.'&nbsp;</td></tr>
        		  <tr> 
          		  <td width=24><p></p></td>';			

		} elseif($mode == 'fr_lastphoto') {
			$photoalb_h = '<span class="cur">Фотографии друзей </span>';

			$html = '<td colspan="11" valign="top" align="left" height="40"></td>
				   <td width=51 colspan=2 valign=top >'.$band.'&nbsp;</td></tr>
        		  <tr> 
          		  <td width=24><p></p></td>';	

		} elseif($mode == 'usr_albs') {
			$row = mysql_fetch_assoc($this->q($base_query));
			if($row == null) {
				$row = mysql_fetch_assoc(
				$this->q("
					   	SELECT `album`.`name` 'albname', `user`.`name` 'username', `user`.`id_user` 'id_user' 
						FROM `album` 
						LEFT JOIN `user` ON `user`.`id_user`=`album`.`id_user`
						WHERE `id_album`=".$id_album) 
						);
			}
			$photoalb_h = '<span class="header1">Фото в альбоме</span>';

			$photoalb_h2 = '<a href="index.php?go=profile&id_user='.$id_user.'" class=user>'.$row['username'].'</a>
						   <span class="cin">&nbsp;&raquo;&nbsp;</span> <span class="cur">'.$row['albname'].'</span>';

			$html = '<td colspan="11" valign="top" align="left" height="40">'.$photoalb_h2.'</td>
				   <td width=51 colspan=2 valign=top >'.$band.'&nbsp;</td></tr>
        		  <tr> 
          		  <td width=24><p></p></td>';
		} elseif($mode == 'all_lastphotos') {
			$html = '<td colspan="11" valign="top" align="left" height="40"></td>
				   <td width=51 colspan=2 valign=top >'.$band.'&nbsp;</td></tr>
        		  <tr> 
          		  <td width=24><p></p></td>';	
		}

		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		//Записываем время окончания в другую переменную
		$tend = $mtime;
		//Вычисляем разницу
		$totaltime = ($tend - $tstart);
		//Выводим на экран
		$t = "";
		if($_SESSION['rights'] == 100)
		$t = sprintf("<font size=1> %f сек!&nbsp;&nbsp;&nbsp;</font>", $totaltime);

		$photoalb = new section($photoalb_h.$t, $left_col, $right_col);
		$photoalb->add_phts_in_alb($albpht, $html, false);


		$album = new content($photoalb, $navig_box.sh_my_album_photos_spacers());
		$this->set_content($album);
	}


	function exif_info($id_photo, $id_group) {
		$query = "SELECT `id_photo`, `photo`.`name` 'pname', `medium`, `original`, alb.`name` 'albname', `photo`.`id_album` 'id_album',
							  DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'pubdate', `scope`, `descr`, `photo`.`id_user` 'id_author'
				  FROM `photo` LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`			
				  WHERE `photo`.`id_photo`=".$id_photo;		
		$res = $this->q($query);
		$row = mysql_fetch_assoc($res);


		$photo = new medium_photo($row['id_photo'], $row['pname'], $row['medium'], $row['original'], $row['pubdate'], null, "",
		$row['descr'], "", $row['id_album'], '', $row['id_author'], $id_group, 'forbid');


		$photoalb_h = '<span class="header1">Exif info</span>';

		$exifinfo = "<p class=text>";

		$ext = strrchr($row['medium'], ".");
		if( $ext == '.JPG' or $ext == '.JPEG' or $ext == '.jpg' or $ext == '.jpeg' ) {
			$exif = exif_read_data("../files/".$row['id_author']."/".$row['id_album']."/".$row['original'], 0, true);
			foreach ($exif as $key => $section) {
				foreach ($section as $name => $val) {
					if($name != 'MakerNote')
					$exifinfo .= "$key.$name: $val<br />\n";

				}
			}
		}
		$exifinfo .= "</p>";

		$photoalb = new section($photoalb_h, 11, 3);
		$photoalb->add_photo_data($photo, $exifinfo);

		$photo_data = new content($photoalb, sh_my_album_photos_spacers());
		$this->set_content($photo_data);
	}

	function my_album_photos($id_album, $page) {

		if($id_album == 0) {
			$row_album = mysql_fetch_assoc($this->q("SELECT `id_album` FROM `album` WHERE `id_user`=".$_SESSION['id_user']." LIMIT 0, 1"));
			if($row_album['id_album'] != null)
			$id_album = $row_album['id_album'];
		}
		$base_query = "SELECT `id_photo`, `photo`.`name` 'pname', `medium`, `original`, alb.`name` 'albname',
							  DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'publdate', `scope`, `descr`
			FROM `photo` LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`			
			WHERE `photo`.`id_album`=".$id_album." AND `photo`.`id_user`=".$_SESSION['id_user']." ORDER BY `pubdate` DESC";

		$per_page = 9;
		$all_photos = mysql_num_rows($this->q($base_query));

		$all_pages = ceil($all_photos/$per_page);

		if($page > $all_pages and $all_pages != 0) $page = $all_pages;

		$range_bottom = ($page - 1)*$per_page;

		$_SESSION['ph_page'] = $page;
		$_SESSION['alb_ses'] = $id_album;
		$_SESSION['left'] = 'albums';
		$address = 'index.php?go=albums&album_num='.$id_album."&id_user=".$_SESSION['id_user'];

		$left_col = 11; $right_col = 3;
		$navig_box = navig($address, $page, $all_pages, ($left_col+$right_col) );

		$alb_photos = $this->q($base_query." LIMIT $range_bottom, $per_page");

		$noalbum = false;
		$i = 0;

		if(mysql_num_rows($alb_photos)) {
			while ($row_alb_photos = mysql_fetch_assoc($alb_photos) and $i < 9) {

				if(!$i++)
				$albumname = $row_alb_photos['albname'];

				//$tags = photo_tags($row_alb_photos['id_photo']);

				$comments = $this->q("SELECT * FROM `comment` WHERE `id_photo`=".$row_alb_photos['id_photo']);

				$num_com = mysql_num_rows($comments);
				$albpht[$i] = new medium_photo($row_alb_photos['id_photo'], $row_alb_photos['pname'], $row_alb_photos['medium'],
				$row_alb_photos['original'], $row_alb_photos['publdate'], $num_com, $row_alb_photos['scope'],
				$row_alb_photos['descr'], $tags, $id_album, $_SESSION['user'], $_SESSION['id_user'], 0);

			}
			$band = '<span class="cur">&nbsp;</span>
				   <a href="index.php?go=band&id_album='.$id_album.'&id_user='.$_SESSION['id_user'].'" class=link>лента</a>&nbsp;';
		} else {

			$row = mysql_fetch_assoc($this->q("SELECT `name` FROM `album` WHERE `id_album`=".$id_album.
			" AND `id_user`=".$_SESSION['id_user']));
			$albumname = $row['name'];

			if(empty($albumname))
			$noalbum=true;
			else
			$noalbum = false;

			$band = '';
		}

		if(!$noalbum) {
			$album_name = '<td colspan="11" valign="top" align="left" height="40">
				   <span class="help">Мой альбом: </span> <span class="cur">'.$albumname.'</span>
			 	   <span class="cin">&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</span>
				   <a href="index.php?go=edit_album&num_album='.$id_album.'" class="link">редактировать</a></td>
				   <td width=51 colspan=2 valign=top >'.$band.'&nbsp;</td></tr>
        		  <tr> 
          		  <td width=24><p></p></td>';

			$photoalb_h = '<span class="header1">Фото в альбоме</span><span class="cin">&nbsp;&nbsp;&raquo;&nbsp;</span>
				<a href="index.php?go=add_photo&alb_num='.$id_album.'&left=albums" class="link">Добавить фото в альбом</a></span>';
		} else
		$photoalb_h = '<span class="header1">Фото</span><span class="cin">&nbsp;&nbsp;&raquo;&nbsp;</span>
				<a href="index.php?go=add_photo&alb_num=0" class="link">Добавить фото</a>';		

		$photoalb = new section($photoalb_h, $left_col, $right_col);
		$photoalb->add_phts_in_alb($albpht, $album_name, $noalbum);


		$album = new content($photoalb, $navig_box.sh_my_album_photos_spacers());
		$this->set_content($album);
	}

	function show_photo_src($id_photo, $mode, $id_group) {

		$group_where = "";
		if(	isset($id_group) ) {
			$row_type = mysql_fetch_assoc( $this->q("SELECT `type` FROM `group` WHERE `id_group`=".$id_group) );

			if( isset($_SESSION['id_user']) ) {
				if($row_type['type'] == 'private') {
					if($_SESSION['rights'] < 100)
					$group_where = "OR (".$_SESSION['id_user']." IN ( SELECT `id_user` FROM `group_member` WHERE `id_group`=".
					$id_group." AND `connected`='yes') )";
					elseif ($_SESSION['rights'] == 100)
					$group_where = "OR 1";
				} else
				$group_where = "OR 1";
			} else {
				if($row_type['type'] != 'private')
				$group_where = "OR 1";
			}
		}
		if ($_SESSION['rights'] == 100)
		$group_where = "OR 1";

		$go = '';
		if($mode == 'original') {

			if( isset($_SESSION['id_user']) ) {

				$f = "( ( `scope_orgnl`='friends' AND ".$_SESSION['id_user']." IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= `id_user`) OR `id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope_orgnl`='onlyme' AND `id_user`=".$_SESSION['id_user'].") OR  (`scope_orgnl` = 'all') ".$group_where." )";
			} else {
				$f = "(`scope_orgnl` = 'all')";
			}
			$go = 'location:index.php?go=photo&photo_num='.$id_photo;

		} else {
			if( isset($_SESSION['id_user']) ) {

				$f = "( ( `scope`='friends' AND ".$_SESSION['id_user']." IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= `id_user`) OR `photo`.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND `photo`.`id_user`=".$_SESSION['id_user'].") OR  (`scope` = 'all') ".$group_where." ) ";
			} else {
				$f = "(`scope` = 'all' ".$group_where.")";
			}
		}

		$res = $this->q("SELECT `original`, `maxi`, `medium`, `small`, `id_album`, `id_user`
						 FROM `photo` 
						 WHERE ".$f." AND `id_photo`=".$id_photo);
		$row = mysql_fetch_assoc($res);

		if($row[$mode]) {
			header('Content-type: image/jpeg');
			header('Pragma: cache');
			header('Cache-Control: cache');
			header('Expires:'.gmdate('D, d M Y H:i:s', time()+7*24*3600 )." GMT");
			//echo 'Content-type: image/jpeg';
			readfile("../files/".$row['id_user']."/".$row['id_album']."/".$row[$mode]);
		} elseif($go != '')
		header($go);
	}

	function show_alb_src($id_album) {
		$res = $this->q("SELECT `image`, `id_user` FROM `album` WHERE `id_album`=$id_album");
		$row = mysql_fetch_assoc($res);

		if($row['image']) {
			if($row['image'] != 'emptyalbum.gif') {
				header('Content-type: image/jpeg');
				header('Pragma: cache');
				header('Cache-Control: cache');
				header('Expires:'.gmdate('D, d M Y H:i:s', time()+7*24*3600 )." GMT");
				//echo 'Content-type: image/jpeg';
				readfile("../files/".$row['id_user']."/".$id_album."/".$row['image']);
			} else {

				header('Content-type: image/jpeg');
				header('Pragma: cache');
				header('Cache-Control: cache');
				header('Expires:'.gmdate('D, d M Y H:i:s', time()+7*24*3600 )." GMT");
				readfile("images/".$row['image']);
			}
		}
	}

	// $mode - 'c комментариями', 'с формой для комментариев'
	function show_photo($id_photo, $came, $page, $mode, $id_comment, $error='') {

		if( isset($_SESSION['id_user']) ) $f = "( ( `scope`='friends' AND ".$_SESSION['id_user']." IN (
				SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= idauthor) OR usr.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND usr.`id_user`=".$_SESSION['id_user'].") OR  (`scope` = 'all') )";
		else $f = "(`scope` = 'all')";

		$query = "SELECT `photo`.`id_user` 'idauthor', `id_photo`, `photo`.`name` 'pname', `medium`, `maxi`,
						  DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'pubdate',
						  DATE_FORMAT(`shootdate`, '%e.%m.%Y') 'made',  
						  alb.`name` 'albname', `scope_orgnl`, `photo`.`id_album` 'idalbum',  
						  `rating`, `descr`, usr.`name` 'author', `ascope`, `cmt_rgt`, INET_NTOA(`photo`.`ip`) 'ip' 
				  FROM `photo` LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`
				  			   LEFT JOIN `user` usr ON `photo`.`id_user`= usr.`id_user`				               
				  WHERE ".$f." AND `photo`.`id_photo`=".$id_photo;

		$row_photos = mysql_fetch_assoc( $this->q($query) );

		// определение уровня доступа для юзера
		if( isset($_SESSION['id_user']) ) {
			switch ($row_photos['scope_orgnl']) {
				case 'friends':
					$frnd_res = $this->q("SELECT `id_author` FROM `friends` WHERE ".$_SESSION['id_user']."
							  IN (SELECT `id_friend` FROM `friends` WHERE `friends`.`id_author`=".$row_photos['idauthor'].")");
					if( mysql_num_rows($frnd_res) or $_SESSION['id_user'] == $row_photos['idauthor'] )
					$original = true;
					else
					$original = false;
					break;

				case 'onlyme':
					$original = ($_SESSION['id_user'] == $row_photos['idauthor']) ? true : false;
					break;

				case 'all':
					$original = true;
			}
		} else {
			if($row_photos['scope_orgnl'] == 'all')
			$original = true;
			else
			$original = false;
		}

		if($mode == 'comments')
			$comments_query = "SELECT `id_comment`, `comment`.`comment_txt` 'commenttxt', u.`name` 'user', `anonymous`,
				              	  IF(u.`hideemail`='checked', '', u.`email`) AS email, 
								  DATE_FORMAT(`comment`.`date`, '%e.%m.%Y<font size=1> %k:%i:%s</font>') 'date',
								  u.`id_user` 'id_user', `avatar`, INET_NTOA(`comment`.`ip`) 'ip', `id_cmnt_prnt` 
							  FROM `comment` 
							  LEFT JOIN `user` u ON `comment`.`id_user` = u.`id_user`
							  WHERE `comment`.`id_photo`=".$id_photo." ORDER BY `comment`.`date` ASC";
		elseif($mode == 'form_comments') {
			if($id_comment != null)
				$comments_query = "SELECT `id_comment`, `comment`.`comment_txt` 'commenttxt', u.`name` 'user', `anonymous`,
					              	  IF(u.`hideemail`='checked', '', u.`email`) AS email, 
								      DATE_FORMAT(`comment`.`date`, '%e.%m.%Y<font size=1> %k:%i:%s</font>') 'date',
								  	  u.`id_user` 'id_user', `avatar`, INET_NTOA(`comment`.`ip`) 'ip' 
								  FROM `comment` 
								  LEFT JOIN `user` u ON `comment`.`id_user` = u.`id_user`
								  WHERE `comment`.`id_photo`=".$id_photo." AND `comment`.`id_comment`=".$id_comment." 
								  ORDER BY `comment`.`date` ASC";
		}		
		$left_col = 4; $right_col = 4;
		/*
		$comments = $this->q($comments_query);

		$num_com = mysql_num_rows($comments);
		$per_page = 10;

		$all_pages = ceil($num_com/$per_page);
		if($page > $all_pages and $all_pages != 0) $page = $all_pages;
		$range_bottom = ($page - 1)*$per_page;
		$_SESSION['com_page'] = $page;
		$address = 'index.php?go=photo&photo_num='.$id_photo;

		$navig_box = navig($address, $page, $all_pages, ($left_col+$right_col), 'com_page');
		*/

		if($mode == 'form_comments') {
			// Определение разрешения комментировать для пользователя
			if( isset($_SESSION['id_user']) ) {		
				if($_SESSION['id_user'] != $row_photos['idauthor']) {
					if($row_photos['cmt_rgt'] == 'friends') {
						$friends = $this->q("SELECT * FROM `friends` WHERE `id_author`=".$row_photos['idauthor']." AND `id_friend`=".$_SESSION['id_user']);
						$num_rows = mysql_num_rows($friends);
						if($num_rows > 0) $allow_comm = true;
					} elseif($row_photos['cmt_rgt'] == 'forbid_all') {
						$allow_comm = false;
					} elseif ($row_photos['cmt_rgt'] == 'all')
					$allow_comm = true;
				} else
					$allow_comm = true;

				$row = $this->q("SELECT * FROM `ban_comment` WHERE `id_author`=".$row_photos['idauthor']." AND `id_user`=".$_SESSION['id_user']);
	
				if(mysql_num_rows($row))
					$allow_comm = false;

				$comment_form = '';
				if($allow_comm)
					$comment_form = sh_add_comment_form($id_photo, $id_comment, $error);

			} else
				$comment_form = '';
		}
		
		//$comments = $this->q($comments_query." LIMIT $range_bottom, $per_page");
		$comments = $this->q($comments_query);
		if($row_comments = mysql_fetch_assoc($comments)) {
			$last_id_comm = 0; $i = 0; //$nest = array();
			do {

				/*
				// анонимный режим
				if($row_comments['anonymous'] == 'yes') {
				$row_comments['user'] = 'аноним';
				$row_comments['email'] = '';
				$avatar = "<img src='images/anonym.gif'>";
				$row_comments['id_user'] = 0;
				} else {
				$avatar = empty($row_comments['avatar']) ? "<img src='images/anonym.gif'>" : "<a href='index.php?go=profile&id_user=".$row_comments['id_user']."'>
				<img src='images/".$row_comments['avatar']."' border=0></a>";
				}
				*/
				
				$avatar = empty($row_comments['avatar']) ? "<img src='images/anonym.gif'>" : "<a href='index.php?go=profile&id_user=".$row_comments['id_user']."'>
					<img src='images/".$row_comments['avatar']."' border=0></a>";	

				$del_comm = '';
				if($_SESSION['id_user'] == $row_photos['idauthor'] or $_SESSION['rights'] == 100)
					$del_comm = '<a href="handler.php?handle=del_comment&id_comment='.$row_comments['id_comment'].'" class=link>удалить комментарий</a>';
				
				if($last_id_comm == $row_comments['id_cmnt_prnt'] and $last_id_comm != 0)
					$nest[$row_comments['id_comment']] = prev($nest) + 15; 
				elseif($row_comments['id_cmnt_prnt'] == 0)
					$nest[$row_comments['id_comment']] = 0;
				else {
					$nest[$row_comments['id_comment']] = $nest[$row_comments['id_cmnt_prnt']] + 15;	
				}
					
				$body_com .= sh_comments_body($row_comments['id_user'], $row_comments['user'], $avatar, $row_comments['email'], $row_comments['date'], 
							 $row_comments['commenttxt'], $row_comments['ip'], $id_photo, $row_comments['id_comment'], $nest[$row_comments['id_comment']], $del_comm);
							 
				$last_id_comm = $row_comments['id_user'];
				$i++; 
			} while( $row_comments = mysql_fetch_assoc($comments) );
		}

		$cmnts = sh_comments($body_com, $id_photo);

		$header = "<span class=header1>Фото</span>";

		$name = "";
		if($row_photos['pname'])
			$name = "&nbsp;<span class=cin>&nbsp;&raquo;&nbsp;</span>
					<span class=cur>".$row_photos['pname']."</span>";
		if($row_photos['ascope'] == 'all')
			$albname = "<a href='index.php?go=albums&alb_num=".$row_photos['idalbum']."&id_user=".$row_photos['idauthor']."' class=link title='альбом'>".$row_photos['albname']."</a>";
		else
			$albname = "<span class=cur title='альбом'>".$row_photos['albname']."</span>";
		
		$header2 = "<a href='index.php?go=profile&id_user=".$row_photos['idauthor']."' class=user title='автор'>".$row_photos['author']."</a>
					<span class=cin>&nbsp;&raquo;&nbsp;</span>".$albname.$name;

		$html = '<td colspan="7" height=30 valign="top">'.$header2.'</td>
				 </tr><tr>
				 <td width="24" valign=top><p></p></td>';		

		$photoalb = new section($header, $left_col, $right_col);

		$ip = "";
		if($_SESSION['rights'] == 100)
			$ip = "&nbsp;&nbsp; IP:".$row_photos['ip'];

		$albpht = new maxi_photo($row_photos['id_photo'], $row_photos['pname'], $row_photos['maxi'], $row_photos['pubdate'].$ip,
		$original, $row_photos['descr'], $row_photos['idalbum'],  $row_photos['albname'], $row_photos['author'],
		$row_photos['idauthor'], $row_photos['made'], 0);

		$photoalb->add_html( $html.$albpht->output() );

		/*
		$navig_box = '<tr><td height="25" colspan="8" valign="top"></td></tr> '.$navig_box;
		$photo_con = new content( $photoalb, $cmnts.$navig_box.$comment_form );
		*/
		
		if($mode == 'form_comments') {
			if($id_comment != null)
				$photo_con = new content( $photoalb, $cmnts.$comment_form );
			else 
				$photo_con = new content( $photoalb, $comment_form );
		} elseif ($mode = 'comments')
			$photo_con = new content( $photoalb, $cmnts.'<tr><td colspan="6" height="40"></td></tr>');
				
		$this->set_content($photo_con);

		$ref = $_SERVER['HTTP_REFERER'];
		if( $ref != null and $mode == 'comments') {
			if( !$came ) {
				if( isset($_SESSION['id_user']) ) {
					if( $_SESSION['id_user'] != $row_photos['author'])
						$this->q("UPDATE `photo` SET `rating`=".($row_photos['rating']+1)." WHERE `id_photo`=".$id_photo);
				} else {
					$this->q("UPDATE `photo` SET `rating`=".($row_photos['rating']+1)." WHERE `id_photo`=".$id_photo);
				}
			}
		}
	}

	// $mode grant, ungrant
	function grant_moderator($id_user, $id_group, $mode) {
		$res = $this->q("SELECT * FROM `group`
						 WHERE `id_author`=".$_SESSION['id_user']." AND `id_group`=".$id_group);
		if(mysql_num_rows($res)) {
			if($mode == 'grant') {
				$_SESSION['gr_rights'] = 50;
				$this->q("UPDATE `group_member` SET `rights`=50 WHERE `id_user`=".$id_user." AND `id_group`=".$id_group);
			} elseif ($mode == 'ungrant') {
				$_SESSION['gr_rights'] = 1;
				$this->q("UPDATE `group_member` SET `rights`=1 WHERE `id_user`=".$id_user." AND `id_group`=".$id_group);
			}
		}
	}

	function discuss($id_discuss) {
		$res_dis = $this->q("
			SELECT `discuss`.`title` 'title', `group`.`id_group` 'id_group', `group`.`title` 'grp_ttl' 
			FROM `discuss`, `group` 
			WHERE `id_discuss`=".$id_discuss." AND `group`.`id_group`=`discuss`.`id_group`");

		$row_dis = mysql_fetch_assoc($res_dis);
		$header = "<span class=header1>Дискуссия</span>";

		$f = "";
		if(isset($_SESSION['id_user'])) {
			if($_SESSION['rights'] < 100) {
				$f = " (`type`='private' AND (".$_SESSION['id_user']." IN (
								  			SELECT `id_user` FROM `group_member` WHERE `group_member`.`id_group`=`group`.`id_group` AND `group_member`.`connected`='yes') OR 
								  			".$_SESSION['id_user']."= `group`.`id_author`) ) OR";
			} else {
				$f = " (`type`='private') OR ";
			}
		}

		$res_post = $this->q("SELECT `id_post`, `posttext`, DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'pubdate',  `id_album`,
									 `photo`.`id_photo`, `user`.`name` 'author', `photo`.`id_user` 'idauthor', `type`, `maxi`,
									  DATE_FORMAT(`shootdate`, '%e.%m.%Y') 'made', `group`.`id_group` 'id_group', `group`.`id_author` 'group_author' 
							  FROM `post` 
							  LEFT JOIN `photo` ON `photo`.`id_photo` = `post`.`id_photo` 
							  LEFT JOIN `user` ON `user`.`id_user`=`photo`.`id_user`
							  LEFT JOIN `discuss` ON `discuss`.`id_discuss` = `post`.`id_discuss`
							  LEFT JOIN `group` ON `group`.`id_group` = `discuss`.`id_group`
							  WHERE `post`.`id_discuss`=".$id_discuss." AND 
							  	(".$f." (`type` = 'public') OR (`type` = 'public_reg') )
							  ORDER BY `id_post` DESC");

		$i = 1;
		if(mysql_num_rows($res_post)) {
			while ($row_post = mysql_fetch_assoc($res_post)) {

				$comments_query = "SELECT `id_dis_comment`, `dis_comment`.`comment_txt` 'commenttxt', u.`name` 'user',
			              	  IF(u.`hideemail`='checked', '', u.`email`) AS email, 
							  DATE_FORMAT(`dis_comment`.`date`, '%e.%m.%Y<font size=1> %k:%i:%s</font>') 'date',
							  u.`id_user` 'id_user', `avatar`, INET_NTOA(`dis_comment`.`ip`) 'ip_comment' 
						  FROM `dis_comment` 
						  LEFT JOIN `user` u ON `dis_comment`.`id_user` = u.`id_user`
						  WHERE `dis_comment`.`id_post`=".$row_post['id_post']." ORDER BY `dis_comment`.`date` ASC";
				$comments = $this->q($comments_query);

				if(mysql_num_rows($comments)) {
					$body_com = "";
					while( $row_comments = mysql_fetch_assoc($comments) ) {
						$avatar = empty($row_comments['avatar']) ? "<img src='images/anonym.gif'>" : "<a href='index.php?go=profile&id_user=".$row_comments['id_user']."'>
					<img src='images/".$row_comments['avatar']."' border=0></a>";

						if($_SESSION['rights'] == 100 or $_SESSION['gr_rights'] > 49) {
							$del_post = '<a href="handler.php?handle=del_comment&id_dis_comment='.$row_comments['id_dis_comment'].'" class=link>удалить комментарий</a>';
							$body_com .= sh_comments_body($row_comments['id_user'], $row_comments['user'], $avatar, $row_comments['email'], $row_comments['date'],
							$row_comments['commenttxt'], $row_comments['ip_comment'],  $row_post['id_photo'], $row_comments['id_dis_comment'], 0, $del_post);
						} else
						$body_com .= sh_comments_body($row_comments['id_user'], $row_comments['user'], $avatar, $row_comments['email'], $row_comments['date'],
						$row_comments['commenttxt'], $row_comments['ip_comment'], $row_post['id_photo'], $row_comments['id_dis_comment'], 0, $del_post);
					}
				} else {
					$body_com = "";
				}

				$posts[$row_post['id_post']] = new post($row_post['id_post'], $row_post['id_photo'],  $row_post['posttext'], $row_post['maxi'],
				$row_post['id_album'], $row_post['idauthor'], $row_post['author'], $row_post['made'], $row_dis['id_group'],
				sh_comments($body_com, /* В ФУНКЦИИ ДОЛЖНО БЫТЬ $id_photo */ $id_post, true));
			}

			$add_post = '';
			if( isset($_SESSION['id_user']) ) {
				$mem = mysql_num_rows($this->q("SELECT `id_user` FROM `group_member` WHERE `id_user`=".$_SESSION['id_user']." AND `connected`='yes' AND `id_group`=".$row_dis['id_group']));
				$auth = mysql_num_rows($this->q("SELECT `id_group` FROM `group` WHERE `id_author`=".$_SESSION['id_user']." AND `id_group`=".$row_dis['id_group']));
				$su = ($_SESSION['rights'] == 100) ? true : false;
				if( $mem or $auth or $su) {
					$add_post = '<span class=cin>&nbsp;&raquo;&nbsp;</span> <a href="index.php?go=add_post&id_discuss='.$id_discuss.'&id_group='.$row_dis['id_group'].'" class=link>добавить пост</a>';
				}
			}

			$html = '
			<td colspan="7" height=51 align="left" valign="middle"> <a href="index.php?go=group&id_group='.$row_dis['id_group'].'" class=link title="группа">'.$row_dis['grp_ttl'].'</a>
				<span class=cin>&nbsp;&raquo;&nbsp;</span> <span class="cur">'.$row_dis['title'].'</span>'.$add_post.'</td>
			</tr>';			
		}

		$discuss = new section($header, 4, 4);

		$html .= '
		<tr bgcolor="#EFEFEF"> 
          <td height="1" bgcolor="#EFEFEF" width="24"></td>
          <td bgcolor="#EFEFEF" width="60"></td>
          <td bgcolor="#EFEFEF" width="10"></td>
          <td bgcolor="#EFEFEF" width="345"></td>
          <td bgcolor="#EFEFEF" width="118"></td>
          <td bgcolor="#EFEFEF" width="118"></td>
          <td bgcolor="#EFEFEF" width="20"></td>
          <td bgcolor="#EFEFEF" width="31"></td>
        </tr>
		<tr bgcolor="#EFEFEF">
			<td width="24" valign=top><p></p></td>';		

		$add_commlink = '';
		if($_SESSION['rights'] == 100 or $this->membership($row_dis['id_group'])) {
			if(is_array($posts)) {
				foreach ($posts as $id_post => $post)
				$add_commlink[$id_post] = '<a href="index.php?go=post_comm&id_post='.$id_post.'" class=link>Добавить комментарий</a>';
			}
		}

		$discuss->add_post_array($html, $posts, $add_commlink);

		$con = new content( $discuss);
		$this->set_content( $con );
	}


	function add_dis_comment_form($id_post, $error="") {

		$admin_where = "";
		if($_SESSION['rights'] == 100) $admin_where = " OR 1";

		$query = "SELECT `posttext`, `photo`.`id_photo` 'id_photo', `photo`.`id_user` 'idauthor', `user`.`name` 'author',`discuss`.`title` 'dsc_title',
						 `photo`.`name` 'pname', `medium`, `maxi`, `group`.`title` 'grp_title', `group`.`id_group` 'id_group',
						  DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'pubdate', `discuss`.`id_discuss` 'id_discuss', 
						  DATE_FORMAT(`shootdate`, '%e.%m.%Y') 'made', alb.`id_album` 'id_album'	  
				  FROM `post` 
				  LEFT JOIN `user` ON `user`.`id_user`=`post`.`id_author` 
				  LEFT JOIN `discuss` ON `discuss`.`id_discuss`=`post`.`id_discuss`
				  LEFT JOIN `group` ON `group`.`id_group`=`discuss`.`id_group`
				  LEFT JOIN `photo` ON `photo`.`id_photo`=`post`.`id_photo`
				  LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`
				  WHERE `id_post`=".$id_post." AND 
					( (`type`='private' AND (".$_SESSION['id_user']." IN (
						SELECT `id_user` FROM `group_member` WHERE `group_member`.`id_group`=`group`.`id_group`) OR 
						".$_SESSION['id_user']."= `group`.`id_author`) ) OR 
					(`type` = 'public') OR (`type` = 'public_reg') ".$admin_where.")";

		$row_post = mysql_fetch_assoc( $this->q($query) );

		if($_SESSION['rights']==100 or $this->membership($row_post['id_group'])) {

			if($row_post) {
				$comments_query = "SELECT `id_dis_comment`, `dis_comment`.`comment_txt` 'commenttxt', u.`name` 'user',
					              	  IF(u.`hideemail`='checked', '', u.`email`) AS email, 
									  DATE_FORMAT(`dis_comment`.`date`, '%e.%m.%Y<font size=1> %k:%i:%s</font>') 'date',
									  u.`id_user` 'id_user', `avatar`, INET_NTOA(`dis_comment`.`ip`) 'ip_comment' 
								  FROM `dis_comment` 
								  LEFT JOIN `user` u ON `dis_comment`.`id_user` = u.`id_user`
								  WHERE `dis_comment`.`id_post`=".$id_post." ORDER BY `dis_comment`.`date` ASC";
				$comments = $this->q($comments_query);

				$comment_form = sh_add_comment_form(0, 0, $error, $row_post['id_discuss'], $id_post);


				if(mysql_num_rows($comments)) {
					$comments = $this->q($comments_query);

					while( $row_comments = mysql_fetch_assoc($comments) ) {
						if($row_comments['anonymous'] == 'yes') {
							$row_comments['user'] = 'аноним';
							$row_comments['email'] = '';
							$avatar = "<img src='images/anonym.gif'>";
							$row_comments['id_user'] = 0;
						} else {
							$avatar = empty($row_comments['avatar']) ? "<img src='images/anonym.gif'>" : "<a href='index.php?go=profile&id_user=".$row_comments['id_user']."'>
							<img src='images/".$row_comments['avatar']."' border=0></a>";			
						}

						$body_com .= sh_comments_body($row_comments['id_user'], $row_comments['user'], $avatar, $row_comments['email'],
						$row_comments['date'], $row_comments['commenttxt'], $row_comments['ip_comment'], $row_post['id_photo'], $row_comments['id_dis_comment'], 0);
					}
				}

				// ФУНКЦИЯ ПРИНИМАЕТ $id_photo
				$cmnts = sh_comments($body_com, $id_post);

				$header = "<span class=header1>Добавление комментария</span>";

				$photoalb = new section($header, 4, 4);

				$html = '<td colspan="7" height=51 align="left" valign="middle"> <a href="index.php?go=group&id_group='.$row_post['id_group'].'" class=link title="группа">'.$row_post['grp_title'].'</a>
						 <span class=cin>&nbsp;&raquo;&nbsp;</span> <a href="index.php?go=discuss&id_discuss='.$row_post['id_discuss'].'" class=link title="дискуссия">'.$row_post['dsc_title'].'</a> 
						 </td></tr>
						 <tr><td width="24" valign=top><p></p></td>';

				$post = new post($id_post, $row_post['id_photo'],  $row_post['posttext'], $row_post['maxi'], $row_post['id_album'],
				$row_post['idauthor'], $row_post['author'], $row_post['made'], $row_post['id_group'],
				$cmnts);

				$photoalb->add_html( $html.$post->output());

				$photo_con = new content( $photoalb, $cmnts.$comment_form );
				$this->set_content($photo_con);
			}
		}
	}

	function agreement() {
		$this->set_title("[Flogr.ru] Пользовательское соглашение");
		$agr = sh_agreement();

		$reg_ok_sec = new section("<span class=header1>Пользовательское соглашение</span>", 2, 2, 1, sh_out($agr, 'left'));
		$reg_ok = new content($reg_ok_sec);
		$this->set_content($reg_ok);
	}

	function help() {
		$this->set_title("[Flogr.ru] Помощь");
		$helptxt = "<p class=text><a href='index.php?go=faq' class=link>FAQ</a></p>";

		$helptxt_sec = new section("<span class=header1>Помощь</span>", 2, 2, 1, sh_out($helptxt, 'left'));
		$helptxt_con = new content($helptxt_sec);
		$this->set_content($helptxt_con);
	}

	function faq() {
		$this->set_title("[Flogr.ru] Часто задаваемые вопросы");
		$helptxt = sh_faq();
		$helptxt_sec = new section("<span class=header1>FAQ</span>", 2, 2, 1, sh_out($helptxt, 'left'));
		$helptxt_con = new content($helptxt_sec);
		$this->set_content($helptxt_con);
	}

	function band($page, $mode, $id_album=0, $id_user=0) {

		if( isset($_SESSION['id_user']) ) $f = " ( ( `scope`='friends' AND ".$_SESSION['id_user']." IN (
				SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`= idauthor) OR usr.`id_user`=".$_SESSION['id_user'].") OR 
					     		( `scope`='onlyme' AND usr.`id_user`=".$_SESSION['id_user'].") OR  (`scope` = 'all') ) ";
		else $f = " (`scope` = 'all') ";
		/*
		$base_query = "SELECT `photo`.`id_user` 'idauthor', `photo`.`id_photo` 'id_photo', `photo`.`name` 'pname', `medium`, `maxi`,
		DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'publdate',
		DATE_FORMAT(`shootdate`, '%e.%m.%Y') 'made', COUNT(`comment`.`id_comment`) AS 'comnum',
		alb.`name` 'albname', `scope_orgnl`, `photo`.`id_album` 'idalbum',
		`rating`, `descr`, usr.`name` 'author'
		FROM `photo`
		LEFT JOIN `comment` ON `comment`.`id_photo` = `photo`.`id_photo`
		LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`
		LEFT JOIN `user` usr ON `photo`.`id_user`= usr.`id_user`
		WHERE ".$f."
		GROUP BY `comment`.`id_photo`";
		*/
		$base_query = "SELECT `photo`.`id_user` 'idauthor', `photo`.`id_photo` 'id_photo', `photo`.`name` 'pname', `medium`, `maxi`,
						  DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'publdate',
						  DATE_FORMAT(`shootdate`, '%e.%m.%Y') 'made', 
						  alb.`name` 'albname', `scope_orgnl`, `photo`.`id_album` 'idalbum',  
						  `rating`, `descr`, usr.`name` 'author' 
				  FROM `photo` 		  
						       LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`
				  			   LEFT JOIN `user` usr ON `photo`.`id_user`= usr.`id_user`			               
				  WHERE ".$f;

		if($mode == 'album')
		$base_query .= " AND `photo`.`id_album`=".$id_album." ORDER BY `pubdate` DESC";
		elseif ($mode == 'all_lastphotos')
		$base_query .= " ORDER BY `pubdate` DESC";
		elseif ($mode == 'usrsphoto')
		$base_query .= " AND usr.`id_user`=".$id_user."
							 ORDER BY `pubdate` DESC";
		elseif ($mode == 'fr_lastphoto') {
			if( isset($_SESSION['id_user']) ) $user = $_SESSION['id_user'];
			else $user = 0;

			$base_query .="AND `photo`.`id_user` IN (
					SELECT `id_friend` FROM `friends` frnd WHERE frnd.`id_author`=".$user.") 
				ORDER BY `pubdate` DESC";
		}

		//$res = $this->q($base_query);

		$per_page = 9;
		$all_photos = mysql_num_rows($this->q($base_query));

		$all_pages = ceil($all_photos/$per_page);

		if($page > $all_pages and $all_pages != 0) $page = $all_pages;

		$range_bottom = ($page - 1)*$per_page;

		$res = $this->q($base_query." LIMIT $range_bottom, $per_page");


		$i = 0;
		while ( $row_photos = mysql_fetch_assoc($res) ) {
			if( isset($_SESSION['id_user']) ) {
				switch ($row_photos['scope_orgnl']) {
					case 'friends':
						$frnd_res = $this->q("SELECT `id_author` FROM `friends` WHERE ".$_SESSION['id_user']."
								  IN (SELECT `id_friend` FROM `friends` WHERE `friends`.`id_author`=".$row_photos['idauthor'].")");
						if( mysql_num_rows($frnd_res) or $_SESSION['id_user'] == $row_photos['idauthor'] )
						$original = true;
						else
						$original = false;
						break;

					case 'onlyme':
						$original = ($_SESSION['id_user'] == $row_photos['idauthor']) ? true : false;
						break;

					case 'all':
						$original = true;
				}
			}
			else {
				if($row_photos['scope_orgnl'] == 'all')
				$original = true;
				else
				$original = false;
			}

			if(!$i) {
				$id_author = $row_photos['idauthor'];
				if($mode == 'album') {
					$header = '<span class=header1>Лента</span>';
					$header2 = '<a href="index.php?go=profile&id_user='.$id_author.'" class=user>'.$row_photos['author'].'</a>
						<span class=cin>&nbsp;&raquo;&nbsp;</span> <span class=cur>'.$row_photos['albname'].'</span>';

				} elseif($mode == 'all_lastphotos') {
					$header = '<span class=header1>Лента последних фото</span>';
				} elseif ($mode == 'usrsphoto') {
					$header = '<span class=header1>Лента</span> <span class=cin>&nbsp;&raquo;&nbsp;</span>
					<a href="index.php?go=profile&id_user='.$id_author.'" class=user>'.$row_photos['author'].'</a>';	
				} elseif ($mode == 'fr_lastphoto') {
					$header = '<span class=header1>Лента друзей</span>';
				}
			}
			$num_comm = mysql_num_rows( $this->q("SELECT * FROM `comment` WHERE `id_photo`=".$row_photos['id_photo']) );
			$albpht[$i++] = new maxi_photo($row_photos['id_photo'], $row_photos['pname'], $row_photos['maxi'], $row_photos['publdate'],
			$original, $row_photos['descr'], $row_photos['idalbum'],  $row_photos['albname'], $row_photos['author'],
			$row_photos['idauthor'], $row_photos['made'], $num_comm);

		}
		$photoalb = new section($header, 4, 4);

		if($mode=='album')
		$compact = 'go=albums&alb_num='.$id_album.'&id_user='.$id_author;
		elseif ($mode == 'all_lastphotos')
		$compact = 'go=all_lastphotos';
		elseif ($mode == 'usrsphoto')
		$compact = 'go=usrsphoto&id_user='.$id_author;
		elseif ($mode == 'fr_lastphoto')
		$compact = 'go=fr_lastphoto';

		$html = '<td colspan="4" height=51 valign="top">'.$header2.'</td>
				<td colspan="3" height=51 align="right" valign="top"><span class="cur">&nbsp;</span><a href="index.php?'.$compact.'" class=link>компактно</a>&nbsp;&nbsp;</td>
				 </tr><tr>
				 <td width="24" valign=top><p></p></td>';		

		$photoalb->add_maxi_array($html, $albpht);

		if($mode == 'album')
		$address = 'index.php?go=band&id_album='.$id_album;
		else {
			if($mode != 'usrsphoto')
			$address = 'index.php?go=band&mode='.$mode;
			else
			$address = 'index.php?go=band&mode='.$mode.'&id_user='.$id_user;
		}
		$navig_box = navig($address, $page, $all_pages, 8);

		$photo_con = new content( $photoalb, $navig_box );
		$this->set_content($photo_con);

	}
	/*
	function change_descr($id_photo, $descr) {
	$this->q("UPDATE `photo` SET `descr`='".addslashes($descr)."' WHERE `id_photo`=".$id_photo);
	}
	*/

	function change_scope_form($id_photo, $error='') {
		$header = "<span class=header1>Изменение области видимости</span>";

		$query = "SELECT `id_photo`, `photo`.`name` 'pname', `medium`, `original`, alb.`name` 'albname', `scope`, `descr`,
				 DATE_FORMAT(`pubdate`, '%e.%m.%Y&nbsp;&nbsp;%k:%i:%s') 'pubdate', alb.`id_album` 'idalbum' 
			FROM `photo` LEFT JOIN `album` alb ON `photo`.`id_album`=alb.`id_album`
			WHERE  `photo`.`id_user`=".$_SESSION['id_user']." AND `medium`!='' AND `photo`.`id_photo`=".$id_photo;

		$row_photos = mysql_fetch_assoc($this->q($query));

		$comments = $this->q("SELECT `comment`.`comment_txt` 'commenttxt', `comment`.`date` 'date', u.`name` 'user', `anonymous`
							  FROM `comment` 
							  LEFT JOIN `user` u ON `comment`.`id_user` = u.`id_user`
							  WHERE `comment`.`id_photo`=".$id_photo);

		$num_com = mysql_num_rows($comments);

		if( isset($_SESSION['id_user']) and $_SESSION['id_user'] == $row_photos['author'] ) {
			$scope_vis = $row_photos['scope'];
		} else {
			$scope_vis = "";
		}

		$albpht[1] = new medium_photo($row_photos['id_photo'], $row_photos['pname'], $row_photos['medium'], $row_photos['original'],
		$row_photos['pubdate'], $num_com, $scope_vis, $row_photos['descr'], $row_photos['tag'],
		$row_photos['idalbum'], $_SESSION['user'], $_SESSION['id_user'], 0, 'forbid');


		$album_name = '<td colspan="11" valign="middle" align="right" height="40">
					<span class="help">Альбом: </span> <span class="cur">'.$row_photos['albname'].'</span></td>
					<td width=51 colspan=2></td>
        </tr>
        <tr> 
          <td width=24><p></p></td>';

		$photoalb = new section($header, 11, 3);
		$photoalb->add_phts_in_alb($albpht, $album_name, true);

		$scope_box[1] = $row_photos['scope'];

		if($error != '')
		$scope_box = $_SESSION['scopebox'];

		$scope_form = sh_change_scope_form($id_photo, $error, $scope_box);

		$scope_con = new content($photoalb, $scope_form.sh_my_album_photos_spacers());
		$this->set_content($scope_con);
	}

	function change_scope($id_photo, $scope_box) {

		if( !isset($scope_box) )
		$res = "Область видимости не задана";
		else {
			$this->q("UPDATE `photo` SET `scope`='".$scope_box[0]."' WHERE `id_photo`=".$id_photo);
			$res = 1;
		}

		return $res;
	}

	function add_comment($id_photo, $comment, $anonymous, $id_comment) {
		$error = '';

		if( isset($_SESSION['id_user']) )
		$user = $_SESSION['id_user'];
		else
		$user = 0;

		$t = trim(strip_tags($comment));
		if( empty($t) ) $error ='Пустой комментарий';


		if( isset($anonymous) or $user==0 )
		$anon = "'yes'";
		else
		$anon = "'no'";

		if(empty($error)) {
			$query = "INSERT INTO `comment`(`id_user`, `anonymous`, `id_photo`, `comment_txt`, `date`, `ip`, `id_cmnt_prnt`)
					  VALUES(".$user.", ".$anon.", ".$id_photo.", '".addslashes(trim(nl2br(strip_tags($comment))))."', NOW(), INET_ATON('".get_ip()."'), ".$id_comment.")";				
			$this->q($query);

			return 1;
		} else
		return $error;
	}

	function add_dis_comment($id_post, $comment) {
		$error = '';

		if( isset($_SESSION['id_user']) )
		$user = $_SESSION['id_user'];
		else
		$user = 0;

		$t = trim(strip_tags($comment));
		if( empty($t) ) $error ='Пустой комментарий';

		if(empty($error)) {

			$query = "INSERT INTO `dis_comment`(`id_user`, `id_post`, `comment_txt`, `date`, `ip`)
				  VALUES(".$user.", ".$id_post.", '".addslashes(trim(nl2br(strip_tags($comment))))."', NOW(), INET_ATON('".get_ip()."'))";				
			$this->q($query);

			return 1;
		} else
		return $error;
	}

	function set_title($new_tle) {
		$this->_title = $new_tle;
	}

	function set_content($new_cnt) {
		$this->__ = $new_cnt;
	}

	function set_left($new_lft) {
		$this->_left = $new_lft;
	}

	function find($search, $select) {

		if($select == 'tag') {
			$res = $this->q("SELECT * FROM `tag` WHERE `name` LIKE '".addslashes($search)."%'");
			if(mysql_num_rows($res))
			//array()
			while ($row = mysql_fetch_assoc($res)){
				$qres[$row['id_tag']] = $row['name'];
			}
			else
			$qres = 0;
		} elseif ($select == 'group') {
			$res = $this->q("SELECT * FROM `group` WHERE `title` LIKE '%".addslashes($search)."%'");
			if(mysql_num_rows($res))
			//array()
			while ($row = mysql_fetch_assoc($res)){
				$qres[$row['id_group']] = $row['title'];
			}
			else
			$qres = 0;
		}

		return $qres;
	}

	function search_res($qres, $mode='tag') {
		if( is_array($qres) ) {
			$i = 0;
			$search_res = '';
			if($mode == 'tag') {
				foreach ($qres as $id_tag => $tagname) {
					$search_res .= "<p>".(++$i).". <a href='index.php?go=photo_tag&id_tag=".$id_tag."' class=link >".$tagname."</a></p>";
				}
			} elseif ($mode == 'group')
			foreach ($qres as $id_group => $groupname)
			$search_res .= "<p>".(++$i).". <a href='index.php?go=group&id_group=".$id_group."' class=link >".$groupname."</a></p>";
		} else {
			$search_res = 'Ничего не найдено';
		}

		$search_sec = new section("<span class=header1>Результаты поиска</span>", 2, 2, 1, sh_out($search_res, 'left'));
		$search_sec_con = new content($search_sec);
		$this->set_content($search_sec_con);
	}

	function site_map() {

		$profile = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=profile&id_user='.$_SESSION['id_user'].'" class="link">Профиль</a>': 'Профиль';
		$profile_ed = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=prfl_edit&id_user='.$_SESSION['id_user'].'" class="link">Редактирование</a>': 'Редактирование';
		$albums = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=albums&id_user='.$_SESSION['id_user'].'" class="link">Альбомы</a>': 'Альбомы';
		$add_album = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=add_album" class="link">Добавление альбома</a>': 'Добавление альбома';
		$all_album = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=all_albums&id_user='.$_SESSION['id_user'].'&left=albums" class="link">Все альбомы</a>': 'Все альбомы';
		$all_my_photo = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=all_myphotos&left=albums" class="link">Все мои фото</a>': 'Все мои фото';
		$add_photo = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=add_photo&alb_num=0" class="link">Добавить фото</a>': 'Добавить фото';
		$friend_list = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=fr_list&id_user='.$_SESSION['id_user'].'" class="link">Список</a>': 'Список';
		$add_friends = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=fr_add&id_user='.$_SESSION['id_user'].'" class="link">Добавление</a>': 'Добавление';
		$friends_photo = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=fr_lastphoto" class="link">Фото друзей</a>': 'Фото друзей';
		$groups = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=groups" class="link">Список моих групп</a>': 'Список моих групп';
		$add_group = (isset($_SESSION['id_user'])) ? '<a href="index.php?go=add_group" class="link">Добавление группы</a>': 'Добавление группы';

		$map = ' <div align=left class=cur>Пользователь</div>
            <blockquote class="text">'.$profile.'<br>
              '.$profile_ed.'<br>
              <a href="index.php?go=reg_form" class="link">Регистрация</a><br>
              <a href="index.php?go=passrec" class="link">Восстановление пароля</a></blockquote>
            <p class="cur">Мои фото</p>
            <blockquote class="text">'.$albums.'<br>
              '.$add_album.'<br>
              '.$all_album.'<br>
              '.$all_my_photo.'<br>
              '.$add_photo.'</blockquote>
            <p class="cur">Друзья</p>
            <blockquote class="text"> 
              <p>'.$friend_list.'<br>
                '.$add_friends.'<br>
                '.$friends_photo.'<br></p>
            </blockquote>
            <p class="cur">Группы</p>
            <blockquote> 
              <p class="text">'.$groups.'<br>
                <a href="index.php?go=all_groups" class="link">Все группы</a><br>
                '.$add_group.'</p>
            </blockquote>
            <p class="cur">Главная</p>
            <blockquote> 
              <a href="index.php?go=all_lastphotos" class="link">Последние фото</a><br>
              <a href="index.php?go=all_popphotos" class="link">Популярные фото</a>
            </blockquote>
              <a href="index.php?go=cntcts" class="link">Контакты</a><br>
              <a href="index.php?go=agreement" class="link">Пользовательское соглашение</a><br><br>
              <a href="index.php?go=help" class="link">Помощь</a><br>
              &nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?go=faq" class="link">FAQ</a><br><br>
              <a href="index.php?go=all_tags" class="link">Теги</a><br>
              <a href="index.php?go=allnews" class="link">Новости</a><br>';

		$map_sec = new section("<span class=header1>Карта сайта</span>", 2, 2, 1, sh_out($map, 'left'));
		$map_con = new content($map_sec);
		$this->set_content($map_con);
	}
}


class content {
	private $top;
	private $bottom;
	private $__='';
	private $col;

	function __construct($sec, $spacers="") {
		if( !db::check_connect() )
		db::__construct();

		$this->col = $sec->f_col + $sec->s_col;
		$this->top = '<table border="0" cellpadding="0" cellspacing="0" width="656">
						<tr>
							<td height="10" width colspan='.$this->col.' valign="top">
							<img src="images/body_r1_c1.png" width="605" height="10" border="0" alt=""></td>
        				</tr>';

		$this->bottom = $spacers.'</table>';
		$this->__ = $sec->output();
	}

	function add_sec($sec) {
		$this->col = $sec->f_col + $sec->s_col;
		$this->__ = $sec->output();
	}

	function append($tbl_body) {
		$this->__ .= $tbl_body;
	}

	function prepend($tbl_body) {
		$this->__ = $tbl_body.$this->__;
	}

	function output() {
		return $this->top.$this->__.$this->bottom;
	}
}


class photo extends db {
	protected $path;
	protected $path2big;
	/* The date of the photo on which it was made
	OR
	the date on wich it was uploaded? */
	protected $date;
	protected $__;
	protected $author;
	public $id_author;
	function __construct($path, $path2big, $date, $author, $id_author, $show_athr=true, $adm_del='yes') {
		if( !db::check_connect() )
		db::__construct();

		$this->path = $path;
		$this->date = $date;
		$this->path2big = $path2big;
		$this->id_author = $id_author;

		if($show_athr) {
			$author_field = '
			<tr> 
             <td height="22" colspan="2" valign="top">&nbsp;<a href="index.php?go=profile&id_user='.$id_author.'" class="user">'.$author.'</a></td>
            </tr>';	
		} else {
			$author_field = '';
		}

		$this->__ = sh_photo($path, $path2big, $date, $author_field, $adm_del);
	}

	function output() {
		return $this->__;
	}
}

class medium_photo extends photo {
	protected $scope;
	protected $album;
	protected $descr;
	public $comments;
	public $id_photo;

	function __construct($id_photo, $name, $path, $path2big, $date, $comments, $scope, $descr, $tag, $id_album, $author, $id_author,
	$id_group, $eddel='allow', $album='', $main_page=null) {


		if(  $eddel == 'allow' ) // разрешить отображение интерфейса удаления/редактирования
		parent::__construct($path, $path2big, $date, $author, $id_author, false, $main_page);
		elseif( $eddel = 'forbid')
		parent::__construct($path, $path2big, $date, $author, $id_author, true, $main_page);

		$this->comments = $comments;
		$this->scope = $scope;
		$this->album = $album;

		$this->id_photo = $id_photo;
		$tag = $this->photo_tags($id_photo);
		$this->__ = sh_medium_photo($id_photo, $name, $path, $path2big, $date, $comments, $scope,
		$descr, $tag[0], $id_album, $eddel, $album, $author, $id_author, $main_page, $id_group);
	}

	function photo_tags($id_photo) {
		if($id_photo == null) $id_photo = 0;

		$res = $this->q("SELECT `tag`.`id_tag` 'id_tag', `tag`.`name` 'tag_name' FROM `tag_photo`
							 LEFT JOIN `tag` ON `tag_photo`.`id_tag`=`tag`.`id_tag`
							 WHERE `id_photo`=".$id_photo);
		if(mysql_num_rows($res)) {
			$row_tag = mysql_fetch_assoc($res);
			$tags[0] = $row_tag['tag_name'];
			$tags[1] = "<a href='index.php?go=photo_tag&id_tag=".$row_tag['id_tag']."&ph_page=1' class=taglink>".$row_tag['tag_name']."</a>";
			while ($row_tag = mysql_fetch_assoc($res) ) {
				$tags[0] .= ", ".$row_tag['tag_name'];
				$tags[1] .= ", <a href='index.php?go=photo_tag&id_tag=".$row_tag['id_tag']."&ph_page=1' class=taglink>".$row_tag['tag_name']."</a>";
			}
		} else {
			$tags = array("", "");
		}
		return $tags;
	}
}

class maxi_photo extends medium_photo {
	private $made;

	function __construct($id_photo, $name, $path, $date, $original, $descr, $id_album,
	$album, $author, $id_author, $made, $comments, $discuss='', $id_group=0, $id_post=0) {

		parent::__construct($id_photo, $name, $path, $path2big, $date, $comments, '', $descr, $tag,
		$id_album, $author, $id_author, 'forbid', $album);

		$this->made = $made;

		$tags = $this->photo_tags($id_photo);

		if($id_group == 0)
		$photo_info = sh_maxi_photoinfo($tags[1], $id_author, $author, $made);
		else {
			$id_group_author = $this->find_group_author($id_group);
			$photo_info = sh_photopost_info($tags[1], $id_author, $author, $made);
		}
		$this->__ = sh_maxi_photo($id_photo, $path, $id_album, $album, $date, $original, $name,
		$descr, $id_author, $photo_info, $discuss, $id_group, $id_group_author, $id_post);
	}

	function find_group_author($id_group) {
		$row = mysql_fetch_assoc( $this->q("SELECT `id_user` FROM `group`, `user`
										WHERE `group`.`id_group`=".$id_group." AND `group`.`id_author`=`user`.`id_user`") );
		return $row['id_user'];
	}
}

class album extends db {
	private $id_album;
	private $num_alb;
	private $image;
	private $date;
	private $checked;
	private  $__;

	public $name;

	function __construct($id_user, $id_album, $num_alb, $name, $image, $date, $checked=false, $del=true) {
		$this->id_album = $id_album;
		$this->num_alb = $num_alb;
		$this->name = $name;
		$this->date = $date;
		$this->checked = $checked;
		$this->image = $image;
		$this->__ = sh_album($id_album, $num_alb, $name, $image, $date, $checked, $del, $id_user);
	}

	function output() {
		return $this->__;
	}
}

class section {
	private $title;
	private $place;

	public $f_col;
	public $s_col;
	public $__;

	//	private $photos;// an array

	function __construct($title, $f_col, $s_col, $place=1, $fill="") {
		$this->title = $title;
		$this->f_col = $f_col;
		$this->s_col = $s_col;
		$this->place = $place;
		$this->__ = sh_section($title, $fill, $f_col, $s_col, $place);
	}

	function merge($add_sec) {
		$this->__.=$add_sec->__;
	}

	function add_photos($pht_array, $rows=1) {
		$fill = '';
		$all_col = $this->f_col + $this->s_col;
		if(is_array($pht_array)) {
			foreach($pht_array as $i => $photo) {
				$fill .= ($i == 3 or $i == 8) ? sh_section_fill($photo->output(), "colspan=2") : sh_section_fill($photo->output());

				if($i == 4 and $rows == 2)
				$fill .= '<td width="31">&nbsp;</td></tr><tr><td height="13" colspan="'.$all_col.'" valign="top"></td></tr>
					<tr><td width="24"><p></p></td>'; 

			}
		} else
		$fill = sh_section_fill("<span class=help>Фото не добавлены</span>");

		$this->__ = sh_section($this->title, $fill, $this->f_col, $this->s_col, $this->place);
	}


	function add_maxi_array( $html, $pht_array) {
		$fill = $html;

		if( is_array($pht_array) ) {
			$i = 0; $j = 0;
			foreach ( $pht_array as $maxi ) {
				$fill .= $maxi->output();

				$photo_cmnts = '
				<tr>
					<td width="24" height="75" valign=top></td>
					<td colspan="7" align="left" valign="top">
					<a href="index.php?go=photo&photo_num='.$maxi->id_photo.'&id_user='.$maxi->id_author.'" class=link><font size=3><b>Комментарии ('.$maxi->comments.')</b></font></a></td>
				</tr>';

				$fill .= '<td width="31"></td></tr>
				'.$photo_cmnts.'
				<tr><td width="24" valign=top></td>';
			}
			$fill .= '<td colspan="6" align="right" valign="middle"></td>';
		} else
		$fill = sh_section_fill("<span class=help>Фото не добавлены</span>");

		$this->__ = sh_section( $this->title, $fill, $this->f_col, $this->s_col, $this->place );
	}


	/*
	*	Adding posts in a group.
	*	$post_array is an array of ojects of type "post".
	*	The array HAS TO HAVE id_posts as indexes of respective elements.
	*	$html is an initial html text.
	*/
	function add_post_array($html, $post_array, $add_commlink) {
		$fill = $html;

		if( is_array($post_array) ) {
			foreach ( $post_array as $id_post => $post) {
				$fill .= $post->output();

				$comments = $post->output_com();

				$add_comments = '<tr bgcolor="#EFEFEF">
									<td height=50>&nbsp;</td>
								 	<td colspan='.($this->f_col + $this->s_col-3).' align=right valign=top>'.$add_commlink[$id_post].'</td>
								 	<td>&nbsp;</td>
								 	<td>&nbsp;</td>
								 </tr>
								 <tr >
								 	<td height=10 colspan='.($this->f_col + $this->s_col).' align=right valign=top></td>
								 </tr>'; 

				$fill .= '<td width="31"></td></tr>
				'.$comments.$add_comments.'
				<tr><td width="24" valign=top></td>';
			}
			$fill .= '<td colspan="6" align="right" valign="middle"></td>';
		} else {
			//$
			$fill = $html.'<td colspan='.($this->f_col + $this->s_col-2).' align=left>&nbsp;</td>';

		}//
		$this->__ = sh_section( $this->title, $fill, $this->f_col, $this->s_col, $this->place );
	}

	function add_albums($alb_array) {
		$fill = '';
		if(is_array($alb_array))
		foreach($alb_array as $i => $album) {
			$fill .= (!(($i-3)%5) ) ? sh_section_fill($album->output(), "colspan=2") : sh_section_fill($album->output());

			if( !(++$i%5) ) // dividing into fives
			$fill .= '<td width="31">&nbsp;</td>
        					</tr><td height="20" colspan="13" valign="top">&nbsp;</td><tr>	 
          				  <td width="24"><p></p></td>';
		}
		else
		$fill = sh_section_fill("<span class=help>Альбомы не добавлены</span>");

		$this->__ = sh_section($this->title, $fill, $this->f_col, $this->s_col, 1);
	}

	function add_phts_in_alb($pht_array, $album_name, $noalbum) {
		$fill = $album_name; $i = 0;
		$_n = '<td>&nbsp;</td>
        				</tr>
        				  <tr> 
          					<td height="25" colspan="14" valign="top">&nbsp;</td>
        				  </tr>
        				  <tr>           				  
        				  <td valign="top">&nbsp;</td>';

		if(is_array($pht_array)) {
			foreach ($pht_array as $ind => $mphoto) {
				$fill .= sh_section_fill($mphoto->output(), "colspan=3");
				if( !(++$i%3)) // dividing into threes
				$fill .= $_n;
			}
		} elseif(!$noalbum) {
			$fill .= ' <tr><td height="50" colspan="14" align="middle" valign="bottom" class=text>Нет фото</td></tr>';
		}
		$this->__ = sh_section($this->title, $fill, $this->f_col, $this->s_col, $this->place);
	}

	function add_photo_data($photo, $data) {
		$fill = sh_section_fill($photo->output(), "colspan=3");
		$fill .='<td colspan="8" valign="top">'.$data.'</td>';
		$this->__ = sh_section($this->title, $fill, $this->f_col, $this->s_col, $this->place);
	}

	function add_html($html) {
		$this->__ = sh_section($this->title, $html, $this->f_col, $this->s_col, $this->place);
	}

	function output() {
		return $this->__;
	}
}

class album_sec {
	private $title;
	public $__;

	function __construct($title, $albm_array, $page, $allpages, $init_link, $id_author) {
		$this->title = $title;
		$fill = '';
		$i = 1;
		// $_n is a sort of the symbol of the end of a string
		$_n = <<<HTM
		  <td width="13" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
        </tr>
        <tr> 
          <td valign="top"></td>
HTM;

		if(is_array($albm_array)) {
			$fill = sh_album_section_fill($albm_array[1]->output());
			for(;$i < count($albm_array);) {
				if( !($i++%2)) //output by two
				$fill .= $_n;
				$fill .= sh_album_section_fill($albm_array[$i]->output());

			}

			$this->__ = sh_album_section($title, $page, $allpages, $id_author, $fill, $init_link);
		} else {
			$fill = sh_album_section_fill("<span class=help>Альбомы не добавлены</span>");
			$this->__ = sh_album_section("<span class=header1>Альбомы не добавлены </span>", $page, $allpages, $id_author);
		}

	}

	function output() {
		return $this->__;
	}
}


class post {
	private $photo; // type maxi_photo

	private $id_post;
	private $posttext;
	public  $id_group;
	private $comments;
	private $__;

	function __construct($id_post, $id_photo, $posttext, $maxi_path, $id_album, $id_author,
	$author, $shootdate, $id_group, $comments) {

		$this->posttext = $posttext;
		$this->id_group = $id_group;
		$this->comments = $comments;
		$this->id_post = $id_post;

		$this->photo = new maxi_photo($id_photo, "", $maxi_path, "", true, "", $id_album, "",
		$author, $id_author, $shootdate, -1, $posttext, $id_group, $id_post);

		$this->__ = $this->photo->output();
	}

	function output() {
		return $this->__;
	}

	function output_com() {
		return $this->comments;
	}
}
//------------------------------------- End of classes -------------------------------------------------//


function navig($address, $curpage, $allpages, $cols, $pagename='ph_page') {
	$pages = array_fill(1, 7, '');

	if($allpages > 7) {
		if($curpage > 4 and $curpage < ($allpages-3)) {
			$pages[4] = '<span class="navigcur">'.$curpage.'</span>';
			$pages[3] = '<a href="'.$address.'&'.$pagename.'='.($curpage-1).'" class="navig">'.($curpage-1).'</a>';
			$pages[5] = '<a href="'.$address.'&'.$pagename.'='.($curpage+1).'" class="navig">'.($curpage+1).'</a>';

			$pages[2] = $pages[6] = '<span class="dots">...</span>';
			$pages[1] = '<a href="'.$address.'&'.$pagename.'=1" class="navig">1</a>';
			$pages[7] = '<a href="'.$address.'&'.$pagename.'='.$allpages.'" class="navig">'.$allpages.'</a>';
		} elseif ($curpage <= 4) {
			for ($i=1; $i < 6; $i++)
			if($i != $curpage)
			$pages[$i] = '<a href="'.$address.'&'.$pagename.'='.$i.'" class="navig">'.$i.'</a>';
			else
			$pages[$i] = '<span class="navigcur">'.$curpage.'</span>';

			$pages[6] = '<span class="dots">...</span>';
			$pages[7] = '<a href="'.$address.'&'.$pagename.'='.$allpages.'" class="navig">'.$allpages.'</a>';

		} elseif ($curpage >= ($allpages-3) ) {
			for($i = $allpages, $j=7; $j > 2; $i--, $j--) {
				if($i != $curpage)
				$pages[$j] = '<a href="'.$address.'&'.$pagename.'='.$i.'" class="navig">'.$i.'</a>';
				else
				$pages[$j] = '<span class="navigcur">'.$curpage.'</span>';

			}
			$pages[2] = '<span class="dots">...</span>';
			$pages[1] = '<a href="'.$address.'&'.$pagename.'=1" class="navig">1</a>';
		}

	} else {
		$i = $allpages;
		$page=1;
		while ($i--) {
			if($page != $curpage)
			$pages[$page]='<a href="'.$address.'&'.$pagename.'='.$page.'" class="navig">'.$page++.'</a>';
			else
			$pages[$page]='<span class="navigcur">'.$page++.'</span>';
		}
	}

	if($allpages > 1)
	$page_sequence = implode('&nbsp;&nbsp;', $pages);
	else
	$page_sequence = '&nbsp;';

	//$previous = $next = '';
	settype($allpages, 'integer');
	if($allpages > 1) {
		if($curpage > 1 and $allpages > 0) {

			$previous = '<a href="'.$address.'&'.$pagename.'='.($curpage-1).'">
			<img name="body_r41_c2" src="images/body_r41_c2.png" width="21" height="25" border="0" alt=""></a></td>
	          <td width="110" align="center"><a href="'.$address.'&'.$pagename.'='.($curpage-1).'" class="navig">Предыдущая</a>';
		} else {
			$previous = '<img name="body_r41_c2" src="images/body_r41_c2_na.gif" width="21" height="25" border="0" alt=""></td>
	          <td width="110" align="center"><span class="navigna">Предыдущая</span>';
		}

		if($curpage < $allpages and $allpages > 0) {
			$next = '<a href="'.$address.'&'.$pagename.'='.($curpage+1).'" class="navig">Следующая</a></td>
        	  <td width="21"><a href="'.$address.'&'.$pagename.'='.($curpage+1).'">
        	  <img name="body_r41_c14" src="images/body_r41_c14.png" width="21" height="25" border="0" alt=""></a>';
		} else {
			$next = '<span class="navigna">Следующая</span></td><td width="21">
					 <img name="body_r41_c14" src="images/body_r41_c14_na.gif" width="21" height="25" border="0" alt="">';
		}
	} else {
		$previous = '&nbsp;</td><td width="110" align="center">&nbsp;';
		$next = '&nbsp;</td><td width="21">&nbsp;';
	}
	$navig_box= sh_navig($address, $previous, $next, $pagename, $page_sequence, $curpage, $allpages, $cols);

	return $navig_box;
}

function make_taglist($tag=0) {

	$res = mysql_query("SELECT DISTINCT `tag`.`id_tag` 'id_tag', `tag`.`name` 'name'
						FROM `tag_photo`
						LEFT JOIN `tag` ON `tag`.`id_tag`=`tag_photo`.`id_tag`
						LEFT JOIN `photo` ON `photo`.`id_photo`=`tag_photo`.`id_photo`
						WHERE `photo`.`id_user`=".$_SESSION['id_user']."					 
						ORDER BY `tag_photo`.`rate` DESC LIMIT 0, 10") 		   
	or die('Query: ['.mysql_error().']');

	if(mysql_num_rows($res)) {
		$taglist = '<br><select name="tag_select" id="tag_select" onChange=\'edit_tagselect(this, "tag", "addphoto");\' size="5" multiple>
                        	<option>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>';
		while($row = mysql_fetch_assoc($res)) {
			$sel = ($tag == $row['id_tag']) ? "selected" : "";
			$taglist .= "<option value='".$row['name']."' $sel>".$row['name']."</option>";
		}
		$taglist .= "</select>";
	} else
	$taglist = "<span class=formcomm>Теги не добавлены</span>";

	return $taglist;
}

function make_albumlist($alb, $id_user) {
	$res = mysql_query("SELECT `id_album`, `name` FROM `album` WHERE `id_user`=".$id_user) or die('Query: ['.mysql_error().']');
	if(mysql_num_rows($res)) {
		$alblist = "<option value=0></option>";
		while ($row = mysql_fetch_assoc($res)) {
			$sel = ($alb == $row['id_album']) ? "selected" : "";
			$alblist .= "<option value=".$row['id_album']." $sel>".$row['name']."</option>";
		}
		$alblist = '<tr align="left">
    	    <td width="222" rowspan="2" align="right" valign="middle" class="rheader">
    	    <font color=#FF0000>*</font>Альбом:</td>
    	    <td width="18" height="39" valign="top">&nbsp;</td>
    	    <td width="321" valign="middle"> <select name="alb_sel" id="alb_sel" onChange="ClearField(\'new_alb\');">
    	    '.$alblist.'
    	    </select></td>
    	 </tr>';
	} else {
		$alblist = '<tr align="left">
    	    <td width="222" rowspan="2" align="right" valign="bottom" class="rheader">
    	    <font color=#FF0000>*</font>Альбом:<br><br></td>
    	    <td width="18" height="39" valign="top">&nbsp;</td>
    	    <td width="321" valign="middle"></td>
    	 </tr>';		
	}
	return $alblist;
}


 // Функция создающая уменьшенную копию фотографии $filename,
  // которая помещается в файл $smallimage
  // Уменьшенный вариант имеет ширину и высту равную
  // $w и $h пикселам, соответственн.
  function resizeimg($filename, $smallimage, $w, $h, $degrees=0)
  {
    // Имя файла с масштабируемым изображением
    //$filename = "../".$filename;
	// Имя файла с уменьшенной копией.
    //$smallimage = "../".$smallimage;    
    // определим коэффициент сжатия изображения, которое будем генерить
    $ratio = $w/$h;
    // получим размеры исходного изображения
    $size_img = getimagesize($filename);
    // получим коэффициент сжатия исходного изображения
    $src_ratio=$size_img[0]/$size_img[1];

    // Здесь вычисляем размеры уменьшенной копии, чтобы при масштабировании сохранились 
    // пропорции исходного изображения
	if ($ratio < $src_ratio)
	{
	  $h = $w/$src_ratio;
	}
	else
	{
	  $w = $h*$src_ratio;
	}
    // создадим пустое изображение по заданным размерам 
    $dest_img = imagecreatetruecolor($w, $h); 
    if($degrees != 0)      
		$dest_img = imagerotate($dest_img, $degrees, 0);
	//$dest_img = imageRotateBicubic($dest_img, $degrees);
	
	$w = imagesx($dest_img);
	$h = imagesy($dest_img);
		
    switch($size_img[2]) {
		case 1: $src_img = imagecreatefromgif($filename); break;
		case 2: $src_img = imagecreatefromjpeg($filename); break;
		case 3: $src_img = imagecreatefrompng($filename); break;
		case 6: $src_img = imagecreatefromwbmp($filename); break;
    }
    if($degrees != 0)
    	$src_img = imagerotate($src_img, $degrees, 0);
    //$src_img = imageRotateBicubic($src_img, $degrees);
    
    $size_img[1] = imagesy($src_img);
    $size_img[0] = imagesx($src_img);
    // масштабируем изображение     функцией imagecopyresampled()
    // $dest_img - уменьшенная копия
    // $src_img - исходной изображение
    // $w - ширина уменьшенной копии
    // $h - высота уменьшенной копии        
    // $size_img[0] - ширина исходного изображения
    // $size_img[1] - высота исходного изображения
    imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $w, $h, $size_img[0], $size_img[1]);                
    // сохраняем уменьшенную копию в файл 
    
    switch($size_img[2]) {
		case 1:  imagegif($dest_img, $smallimage); break;
		case 2:  imagejpeg($dest_img, $smallimage); break;
		case 3:  imagepng($dest_img, $smallimage); break;
    }         
    
    // чистим память от созданных изображений
    imagedestroy($dest_img);
    imagedestroy($src_img);
    return true;         
}



  function resizeimg2($filename, $smallimage, $w, $h, $degrees=0) {
    // Имя файла с масштабируемым изображением
    //$filename = "../".$filename;
	// Имя файла с уменьшенной копией.
    //$smallimage = "../".$smallimage;    
    // определим коэффициент сжатия изображения, которое будем генерить
//    $ratio = $w/$h;
    // получим размеры исходного изображения
    $size_img = getimagesize($filename);
    // получим коэффициент сжатия исходного изображения
//    $src_ratio=$size_img[0]/$size_img[1];

    // Здесь вычисляем размеры уменьшенной копии, чтобы при масштабировании сохранились 
    // пропорции исходного изображения
//	if ($ratio<$src_ratio)
//	{
//	  $h = $w/$src_ratio;
//	}
//	else
//	{
//	  $w = $h*$src_ratio;
//	}
    // создадим пустое изображение по заданным размерам 
    $dest_img = imagecreatetruecolor($w, $h);       
	
    if($degrees != 0)
    	$dest_img = imagerotate($dest_img, $degrees, 0);	
	//$dest_img = imageRotateBicubic($dest_img, $degrees);
    
	switch($size_img[2]) {
		case 1:  $src_img2 = imagecreatefromgif($filename); break;
		case 2:  $src_img2 = imagecreatefromjpeg($filename); break;
		case 3:  $src_img2 = imagecreatefrompng($filename); break;
		//case 6:  $src_img2 = imagecreatefromwbmp($filename); break;
    }
	if($degrees != 0)
    		$src_img2 = imagerotate($src_img2, $degrees, 0);
		//$src_img2 = imageRotateBicubic($src_img2, $degrees);
    
	// масштабируем изображение     функцией imagecopyresampled()
    // $dest_img - уменьшенная копия
    // $src_img - исходной изображение
    // $w - ширина уменьшенной копии
    // $h - высота уменьшенной копии        
    // $size_img[0] - ширина исходного изображения
    // $size_img[1] - высота исходного изображения
    // сохраняем уменьшенную копию в файл 

    if  ($size_img[0]<=$size_img[1]) {
    	$src_img = imagecreatetruecolor($size_img[0], $size_img[0]);
	
		if($degrees != 0)  
    		$src_img = imagerotate($src_img, $degrees, 0);
 		//	$src_img = imageRotateBicubic($src_img, $degrees);   	
  	 	
 		imagecopy($src_img, $src_img2, 0, 0, 0, 0, $size_img[0], $size_img[0]);
  	  	imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $w, $h, $size_img[0], $size_img[0]);
    }
    else {
    	$src_img = imagecreatetruecolor($size_img[1], $size_img[1]);
    	
    	if($degrees != 0)
    		$src_img = imagerotate($src_img, $degrees, 0);
    		//$src_img = imageRotateBicubic($src_img, $degrees);
    	
   		imagecopy($src_img, $src_img2, 0, 0, 0, 0, $size_img[1], $size_img[1]);
    	imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $w, $h, $size_img[1], $size_img[1]);
    }

    
    switch($size_img[2]) {
		case 1:  imagegif($dest_img, $smallimage); break;
		case 2:  imagejpeg($dest_img, $smallimage); break;
		case 3:  imagepng($dest_img, $smallimage); break;
		//case 6:  image2wbmp($dest_img, $smallimage); break;		
    }
    
    // чистим память от созданных изображений
    imagedestroy($dest_img);
    imagedestroy($src_img);
    return true;
}

  
//ImageRotateRightAngle
function imageRotateBicubic( $imgSrc, $angle ) {
   // ensuring we got really RightAngle (if not we choose the closest one)
   $angle = min( ( (int)(($angle+45) / 90) * 90), 270 );

   // no need to fight
   if( $angle == 0 )
       return( $imgSrc );

   // dimenstion of source image
   $srcX = imagesx( $imgSrc );
   $srcY = imagesy( $imgSrc );

   switch( $angle )
       {
       case 90:
           $imgDest = imagecreatetruecolor( $srcY, $srcX );
           for( $x=0; $x<$srcX; $x++ )
               for( $y=0; $y<$srcY; $y++ )
                   imagecopy($imgDest, $imgSrc, $srcY-$y-1, $x, $x, $y, 1, 1);
           break;

       case 180:
           $imgDest = ImageFlip( $imgSrc, IMAGE_FLIP_BOTH );
           break;

       case 270:
           $imgDest = imagecreatetruecolor( $srcY, $srcX );
           for( $x=0; $x<$srcX; $x++ )
               for( $y=0; $y<$srcY; $y++ )
                   imagecopy($imgDest, $imgSrc, $srcY-$y-1, $srcX-$x-1, $x, $y, 1, 1);
           break;
       }

   return( $imgDest );
}

function get_ip() {
   if($ip = $_SERVER["HTTP_CLIENT_IP"]) return $ip; 

   if($ip = $_SERVER["HTTP_X_FORWARDED_FOR"]) { 
      if($ip == '' || $ip == "unknown")
          $ip = $_SERVER["REMOTE_ADDR"]; 
       
      return $ip; 
   }

   if($ip = $_SERVER["REMOTE_ADDR"]) return $ip;
}
?>
