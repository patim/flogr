<?php 
/////////////////////////////////////////////////////////////////////
// Static(mostly) html for the photo website
// Maxim Zalutskiy
// 2008
/////////////////////////////////////////////////////////////////////

//#######################################################3
//
//			FOR PH_BLOG		
//
//
//#######################################################3

	/*------------------------------------------------/
	/						  /
	/		FOR CLASS ph_blog_page		  /
	/						  /
	/------------------------------------------------*/

function sh_top() {

if( isset($_SESSION['id_user']) ) {
	$go = 'index.php?go=profile&id_user='.$_SESSION['id_user'];
	//$go_frnds = 'index.php?go=friends&id_user='.$_SESSION['id_user'];
	$go_frnds = 'index.php?go=fr_lastphoto';
	$go_group = 'groups';
} else {
	$go_group = 'all_groups';
	$go = $go_frnds = 'enter.php';
}
if( isset($_GET['search'])) {
	$search_txt = "value='".urldecode($_GET['search'])."'";
	
	if($_GET['mode'] == 'group') {
		$group_check = 'selected';
		$tag_check = '';
	} else {
		$group_check = '';
		$tag_check = 'selected';		
	}
	
} else 
	$search_txt = "value='поиск'";
	
return <<<HTM
<table border="0" cellpadding="0" cellspacing="0" width="1000">       
        <tr> 
          <td width="51" height="1" valign="top"><img src="images/spacer.gif" width="51" height="1" border="0" alt=""></td>
          <td colspan="8" valign="top"></td>
          <td width="64" valign="top"><img src="images/spacer.gif" width="64" height="1" border="0" alt=""></td>
          <td width="47" valign="top"><img src="images/spacer.gif" width="29" height="1" border="0" alt=""></td>
          <td width="129" valign="top"><img src="images/spacer.gif" width="13" height="1" border="0" alt=""></td>
          <td width="105" valign="top"><img src="images/spacer.gif" width="15" height="1" border="0" alt=""></td>
        </tr>
        <tr> 
          <td rowspan="3" valign="top"><img name="top_r1_c1" src="images/top_r1_c1.jpg" width="51" height="93" border="0" alt=""></td>
          <td height="40" colspan="12" valign="top"><img name="top_r1_c2" src="images/top_r1_c2.jpg" width="949" height="40" border="0" alt=""></td>
        </tr>
        <tr> 
          <td height="43" colspan="9" valign="top" background="images/top_r2_c2.jpg" class="beta">
          <a href="index.php?left=shortnews" title="На главную" class="fotoblog">Flogr.ru</a> beta</td>
          <td colspan="3" rowspan="2" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
              
              <tr> 
                <td width="68" height="19" valign="top"><img src="images/body.jpg" width="68" height="19"></td>
                <td width="95"><img src="images/abovemap.gif"></td>
                <td width="28"><img src="images/aboveqm.jpg"></td>
                <td width="90"><img src="images/abovehelp.jpg"></td>
              </tr>
              <tr> 
                <td height="17" valign="top" background="images/contacts.gif"><a href="index.php?go=cntcts" class="link">Контакты</a></td>
                <td valign="top" background="images/site_map.gif"><a href="index.php?go=site_map" class="link">Карта сайта</a></td>
                <td valign="top"><img name="qm" src="images/qm.jpg" width="28" height="17" border="0" alt=""></td>
                <td valign="top" background="images/help.jpg"><span class="text">&nbsp;</span><a href="index.php?go=help" class="link">Помощь</a></td>
              </tr>
              <tr> 
                <td height="17" colspan="4" valign="top">
                <img name="top_r4_c29" src="images/top_r4_c29.jpg" width="281" height="17" border="0" alt=""></td>
              </tr>
            </table></td>
        </tr>
        <tr> 
          <td height="10" colspan="9" valign="top">
          <img name="top_r5_c2" src="images/top_r5_c2.jpg" width="668" height="10" border="0" alt=""></td>
        </tr>
        <tr> 
          <td rowspan="2" valign="top"><img name="top_r6_c1" src="images/top_r6_c1.png" width="51" height="41" border="0" alt=""></td>
          <td height="4" colspan="11" valign="top">
          <img name="top_r6_c2" src="images/top_r6_c2.png" width="844" height="4" border="0" alt=""></td>
          <td background="images/find_btn_top.gif"></td>
        </tr>
        <tr> 
          <td width="36" height="37" valign="top">
          <img name="top_r7_c2" src="images/top_r7_c2.gif" width="36" height="37" border="0" alt=""></td>
          <td width="91" valign="middle" background="images/Profile.jpg"><a href="$go" class="topmenu">Профиль</a></td>
          <td width="48" valign="top"><img name="top_r7_c6" src="images/top_r7_c6.jpg" width="48" height="37" border="0" alt=""></td>
          <td width="95" valign="middle" background="images/Friends.jpg"><a href="$go_frnds" class="topmenu">Друзья</a></td>
          <td width="44" valign="top"><img name="top_r7_c12" src="images/top_r7_c12.gif" width="44" height="37" border="0" alt=""></td>
          <td width="87" valign="middle" background="images/groups.jpg"><a href="index.php?go=$go_group" class="topmenu">Группы</a></td>
          <td width="40" valign="top"><img name="top_r7_c19" src="images/top_r7_c19.gif" width="40" height="37" border="0" alt=""></td>
          <td width="163" valign="middle" background="images/my_photos.jpg">
          <a href="index.php?go=all_myphotos&left=albums" class="topmenu">Мои фото</a></td>
			<form action="handler.php" method="post">
          <td colspan="2" valign="middle" background="images/searchb.jpg">
          	<input name="search" type="text" id="search" onfocus="if (this.value == 'поиск') this.value = '';"
						onblur="if (this.value == '') this.value = 'поиск';" size="14" class="searchbox" $search_txt>
          </td>
          <td align="center" valign="middle" background="images/roller_boxb.jpg"> 
            <select name="select" class="searchbox">
                <option value="tag" $tag_check >по тегам</option>
				<option value="group" $group_check>по группам</option>
              </select></td>
            <td valign="middle" background="images/find_btn.gif">
            <input name="handle" type="hidden" id="handle" value="find">
            <input name="Submit" type="submit" class="findbtn" value="Найти"> 
            </td>
        </form>
		</tr>
      </table>
HTM;
}


function sh_bottom (){
return <<<HTM
<table border="0" cellpadding="0" cellspacing="0" width="100%">       
        <tr> 
          <td height="94" width="100%" background="images/bottom_r1_c1.jpg"></td>
          <td><img name="bottom_r1_c34" src="images/bottom_r1_c34.jpg" width="118" height="94" border="0" alt=""></td>
        </tr>
      </table>
HTM;
}


function sh_copyright() {
return <<<HTM
<table border="0" cellpadding="0" cellspacing="0" width="344">
        
        <tr> 
          <td width="50" height="54" valign="top">&nbsp;</td>
          <td width="294" valign="top" class="help"><br><br><br>
            Электронный адрес: <a href="mailto:info@flogr.ru" class='extlink'>info@flogr.ru</a></td>
        </tr>
        <tr> 
          <td height="26" valign="top" background="images/col_left.gif">&nbsp;</td>
          <td valign="middle" class="help">© "Коламбия Телеком" 2007</td>
        </tr>
      </table>
HTM;
}

// should be changed
function sh_pop_tag($body) {
return <<<HTM
<table width="344" border="0" cellpadding="0" cellspacing="0" height="142">
        <tr> 
          <td width="51" rowspan="7" valign="middle"><img src="images/poptagleft.jpg" width="51" height="143" align="bottom"></td>
          <td height="30" colspan="2" valign="top" background="images/poptag_left.jpg"><span class="header1">Популярные 
            теги</span> <span class="cin">&nbsp;&raquo;&nbsp;</span> <a href="index.php?go=all_tags" class="link">облако тегов</a></td>
        </tr>
		$body
        <tr> 
          <td height="9"></td>
          <td></td>
        </tr>
      </table>
HTM;
}

/*
	The main pattern function that is responsibile for the output of the page.
*/
function sh_show($title, $top, $auth, $pop_tag, $left, $content, $copyright, $bottom, $btm_menu) {
return <<<HTM
<html>
<head>
<title>$title</title>
<script type="text/javascript" src="jscript.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<meta http-equiv="Cache-Control" content="private">
<meta name="keywords" content="фото галерея, фото девушек, фото, фотоблог, фото блог, фотогалерея, волгоградские фотографы, фотографии волгоградские, фото Волгоград, фотки волгоград"/>
<meta name="description" content="фотоблог, фото блог, фотогалерея, фото Волгоград, фотографии волгоградские, волгоградские фотографы, фотки волгоград"/>

<link href="phb_styles.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF">
<table width="1000" border="0" cellpadding="0" cellspacing="0">
  <tr> 
    <td height="135" colspan="3" valign="bottom"> $top</td>
  </tr>
<tr> 
    <td width="344"  valign="top" >
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
        
        <tr> 
          <td width="344" height="59" valign="top">$auth</td>
        </tr>
        <tr> 
          <td height="130" valign="top">$pop_tag</td>
        </tr>
        <tr> 
          <td valign="top">$left</td>
        </tr>
      </table></td>
    <td colspan="2" rowspan="2" valign="top">$content</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td colspan="3">&nbsp;</td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <!--DWLayoutTable-->
  <tr> 
    <td width="344" height="80" background="images/prebottom.jpg">$copyright</td>
    <td width="605" valign="top" background="images/prebottom.jpg">$btm_menu</td>
    <td width="100%" background="images/prebottom.jpg">&nbsp;</td>
  </tr>
</table>
$bottom
</body>
</html>
HTM;
}


function sh_bottom_menu() {
if(isset($_SESSION['id_user'])) {
	$id_user = $_SESSION['id_user'];
	return <<<HTM
<table border="0" cellpadding="0" cellspacing="0" width="605">
        <tr> 
          <td width="94" height="20" class="cur">Мои фото</td>
          <td width="511"><a href="index.php?go=albums&id_user=$id_user" class="link">Альбомы</a> 
            &nbsp;&nbsp; <a href="index.php?go=all_albums&id_user=$id_user" class="link">Архив</a>&nbsp;&nbsp; 
            <a href="index.php?go=add_photo&alb_num=0" class="link">Добавить</a></td>
        </tr>
        <tr> 
          <td height="20" class="cur">Группы</td>
          <td valign="top"><a href="index.php?go=groups" class="link">Мои группы</a> 
            &nbsp;&nbsp;<a href="index.php?go=all_groups" class="link">Посмотреть 
            все</a> &nbsp;&nbsp;<a href="index.php?go=add_group" class="link">Добавить</a></td>
        </tr>
        <tr> 
          <td height="20" class="cur">Друзья</td>
          <td><a href="index.php?go=fr_list&id_user=$id_user" class="link">Список друзей</a> 
            &nbsp;&nbsp;<a href="index.php?go=fr_add&id_user=$id_user" class="link">Добавить</a>&nbsp;&nbsp; 
            <a href="index.php?go=fr_lastphoto" class="link">Последние фото друзей</a> 
            &nbsp;&nbsp;<a href="#" class="link">Поиск</a></td>
        </tr>
        <tr> 
          <td height="18" class="cur">Информация</td>
          <td><a href="index.php?go=profile&id_user=$id_user" class="link">Профиль</a> 
            &nbsp;&nbsp; <a href="index.php?go=agreement" class="link">Пользовательское 
            соглашение</a>&nbsp;&nbsp; <a href="index.php?go=help" class="link">Помощь</a></td>
        </tr>
        <tr> 
          <td height="0"></td>
          <td></td>
        </tr>
      </table>
HTM;
} else {
return <<<HTM
<table border="0" cellpadding="0" cellspacing="0" width="605">
        <!--DWLayoutTable-->
        <tr> 
          <td width="94" height="20" valign="middle" class="cur">Информация</td>
          <td width="511" valign="top"><a href="index.php?go=agreement" class="link">Пользовательское 
            соглашение</a>&nbsp;&nbsp; <a href="index.php?go=help" class="link">Помощь</a></td>
        </tr>
        <tr> 
          <td height="60">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      </table>
HTM;
}
}

function sh_friends($id_user, $friend_str, $add_form) {
	
return <<<HTM
		<td colspan="4" valign="top">&nbsp;</td>
        </tr>
        <tr>
          <td width="24"><p></p></td> 
          <td height="84" colspan="3" valign="top"><p>$friend_str</p>
			$add_form
          </td>
        </tr>
        <tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="25" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="413" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="187" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
        <tr> 
          <td height="20" colspan="3" valign="top">&nbsp;</td>
HTM;
}

function sh_profile($id_user, $fio, $name, $domain, $avatar, $email, $hideemail, $num_photo, $friends, 
					$friendto, $groups, $in_groups, $reg_date, $deladd_to_frnds, $ip, $last_ip) {

if( isset($_SESSION['id_user']) and $name == $_SESSION['user'])
	$field = '<td width="74" valign="top" bgcolor="#CCD9F2"><a href="index.php?go=prfl_edit&id_user='.$id_user.'" class="link">изменить</a></td>';
else 
	$field = '<td width="74" valign="top">&nbsp;</td>';	

$look = '';	
$see_alb = '<span class=text>нет</span>';
if($num_photo > 0) {
	$see_alb = '<a href="index.php?go=albums&id_user='.$id_user.'&left=usr_albs" class="link">смотреть</a>';
	if( empty($domain) )
		$look = '<a href="index.php?go=usrsphoto&id_user='.$id_user.'" class="link">смотреть</a>';
	else 
		$look = '<a href="index.php?go=usrsphoto&domain='.$domain.'&ph_page=1" class="link">смотреть</a>';   
}

if($hideemail == 'checked') {
	if( $id_user != $_SESSION['id_user'] ) {
		$email = '';
	} $emailfield = $email.'&nbsp;&nbsp;&nbsp;<span class=formcomm>скрытый</span>';
	
} else
	$emailfield = $email.'&nbsp;&nbsp;&nbsp;<span class=formcomm>видимый</span>';
/*
if( isset($_SESSION['id_user']) and $id_user != $_SESSION['id_user'] ) {
	$add_to_friends = "<a href='handler.php?handle=add_friend&id_user=".$_SESSION['id_user']."&friend_select=".$id_user."' class=link>добавить в друзья</a>";
} else $add_to_friends = "";
	*/

$ip_field = '';
if($_SESSION['rights'] == 100) {
	$ip_field = '<tr> <td height="40" colspan="2" align="right" valign="middle" class="rheader"> 
    	        IP:</td><td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
        	  	<td colspan="4" valign="middle" bgcolor="#FFFFFF" class="text">'.$ip.'</td></tr>
        	  	<tr> <td height="40" colspan="2" align="right" valign="middle" class="rheader"> 
    	        IP последней авторизации:</td><td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
        	  	<td colspan="4" valign="middle" bgcolor="#FFFFFF" class="text">'.$last_ip.'</td></tr>';
}
return <<<HTM
          <td colspan="6" valign="bottom" class="text">&nbsp;</td>
        </tr>
        <tr> 
          <td height="40" colspan="2" align="right" valign="middle" class="rheader">Фио: 
          </td>
          <td width="19" valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" align="left" valign="middle" bgcolor="#FFFFFF" class="text">$fio</td>
        </tr>
        <tr> 
          <td height="40" colspan="2" align="right" valign="middle" class="rheader"> 
            имя:</td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" valign="middle" bgcolor="#FFFFFF" class="text"> <b>$name</b>&nbsp;&nbsp;&nbsp;$deladd_to_frnds</td>
        </tr>
        $ip_field
        <tr> 
          <td height="40" colspan="2" align="right" valign="middle" class="rheader"> 
            аватар:</td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td height="48" colspan="4" align="left" valign="middle" bgcolor="#FFFFFF"> 
            $avatar</td>
        </tr>        
        <tr> 
          <td height="44" colspan="2" align="right" valign="middle" class="rheader">e-mail:&nbsp;&nbsp; 
          </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" valign="middle" bgcolor="#FFFFFF" class="formcomm"> 
            <p class="text">$emailfield</p></td>
        </tr>
        <tr> 
          <td height="44" colspan="2" align="right" valign="middle" class="rheader">дата 
            регистрации:&nbsp;&nbsp; </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" valign="middle" class="formcomm"> <p class="text">$reg_date</p></td>
        </tr>		
        <tr align="center" valign="middle"> 
          <td height="46" colspan="2" align="right" valign="middle" class="rheader">фотографии:&nbsp;&nbsp; 
          </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" align="left" valign="middle" class="text">$num_photo&nbsp;&nbsp;&nbsp;
          	$look</td>
        </tr>        
        <tr align="center" valign="middle"> 
          <td height="46" colspan="2" align="right" valign="middle" class="rheader">Альбомы:&nbsp;&nbsp; 
          </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" align="left" valign="middle" class="text">$see_alb</td>
        </tr>                
        <tr align="center" valign="middle"> 
          <td height="26" colspan="2" align="right" valign="middle" class="rheader">друзья:&nbsp;&nbsp; 
          </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" align="left" valign="middle" class="text">$friends</td>
        </tr>
        
        <tr align="center" valign="middle"> 
          <td height="20" colspan="2" align="right" valign="middle" class="rheader">&nbsp;&nbsp; 
          </td>
          <td height="20" valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td height="20" colspan="4" align="left" valign="middle" class="text">&nbsp;</td>
        </tr>
                
        <tr align="center" valign="middle"> 
          <td height="26" colspan="2" align="right" valign="middle" class="rheader">в 
            друзьях у:&nbsp;&nbsp; </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" align="left" valign="middle" class="text">$friendto</td>
        </tr>
        <tr align="center" valign="middle"> 
          <td height="46" colspan="2" align="right" valign="middle" class="rheader">мои группы:&nbsp;&nbsp; 
          </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" align="left" valign="middle" class="text">$groups</td>
        </tr>
        <tr align="center" valign="middle"> 
          <td height="46" colspan="2" align="right" valign="middle" class="rheader">состою в группах:&nbsp;&nbsp; 
          </td>
          <td valign="top" bgcolor="#E8EEF9">&nbsp;</td>
          <td colspan="4" align="left" valign="middle" class="text">$in_groups</td>
        </tr>        
		<tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="25" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="225" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="19" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="170" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="112" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="74" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>		
        <tr align="center" valign="middle"> 
          <td height="20" colspan="5" align="right" valign="top">&nbsp;</td>
          $field
HTM;
}

function sh_group_form($title, $type, $descr, $error, $mode, $id_group=0) {
if( !empty($error) )
	$error = '<font color="#FF0000" class="formcomm">'.$error.'<br><br></font>';

switch ($type) {
	case 'public':
		$pubchk = 'checked';
	break;
	
	case 'public_reg':
		$readonlychk = 'checked';
	break;
	
	case 'private':
		$prvtchk = 'checked';		
	break;		
}

if($mode == 'add_group') {
	$type_field = <<<HTM
          <tr> 
            <td height="20" colspan="2" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Тип: 
            </td>
            <td valign="top">&nbsp;</td>
            <td colspan="4" align="left" valign="middle" class="formcomm"> <input type="radio" name="type" value="public" $pubchk>
              публичная 
              <input type="radio" name="type" value="public_reg" $readonlychk>
              публичная с регистрацией 
              <input type="radio" name="type" value="private" $prvtchk>
              частная</td>
            <td>&nbsp;</td>
          </tr>	
HTM;
	$botton = 'Добавить';
} elseif( $mode == 'ed_group') {
	$group_input = '<input name="id_group" type="hidden" id="id_group" value="'.$id_group.'">';
	$botton = 'Изменить';
}
return <<<HTM
          <td colspan="6" height="76" valign="middle" class="text"><p>Введите 
              данные о группе</p>
            <p><span class="formcomm">Поля отмеченные <font color=#FF0000>*</font> 
              обязательны к заполнению</span></p> $error</td>
          <td>&nbsp;</td>
        </tr>
        <form action="handler.php" method="post" enctype="multipart/form-data">
          <tr> 
            <td height="22" colspan="2" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Название: 
            </td>
            <td width="19" valign="top">&nbsp;</td>
            <td colspan="4" align="left" valign="middle"><input name="title" type="text" id="title" size="40" value="$title"></td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td height="20" colspan="7" valign="top">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
			$type_field
          <tr> 
            <td height="20">&nbsp;</td>
            <td width="225">&nbsp;</td>
            <td>&nbsp;</td>
            <td width="170">&nbsp;</td>
            <td width="150">&nbsp;</td>
            <td width="36">&nbsp;</td>
            <td width="31">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td height="86" colspan="2" align="right" valign="middle" class="rheader">Описание:</td>
            <td valign="top">&nbsp;</td>
            <td colspan="5" align="left" valign="middle"><textarea name="descr" cols="35" rows="4" id="descr">$descr</textarea> 
            </td>
          </tr>
          <tr> 
            <td height="20" colspan="7" valign="top">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td height="78">&nbsp;</td>
            <td colspan="4" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
                <!--DWLayoutTable-->
                <tr> 
                  <td width="562" height="75" valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                      <tr> 
                        <td height="27" colspan="3" valign="middle" class="formcomm">&nbsp;&nbsp;Допустимые форматы изображения: <b>jpeg</b>, <b>png</b>, <b>gif</b></td>
                      </tr>
                      <tr> 
                        <td width="224" height="48" align="right" valign="middle" class="rheader">Изображение:</td>
                        <td width="19" valign="top">&nbsp;</td>
                        <td width="319" align="left" valign="middle"><input name="group_im" type="file" id="group_im"></td>
                      </tr>
                      <tr> 
                        <td height="27" colspan="3" valign="middle" class="formcomm">&nbsp;&nbsp;изображение 
                          будет уменьшено до размеров 38х38</td>
                      </tr>
                      <tr> 
                        <td height="1"><img src="images/spacer.gif" alt="" width="224" height="1"></td>
                        <td><img src="images/spacer.gif" alt="" width="19" height="1"></td>
                        <td><img src="images/spacer.gif" alt="" width="319" height="1"></td>
                      </tr>
                    </table></td>
                </tr>
              </table></td>
            <td colspan="2" valign="top">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td height="36" colspan="8" valign="top">&nbsp;</td>
          </tr>
          <tr align="center" valign="middle"> 
            <td height="22" colspan="2" align="right" valign="middle"> <input name="Submit" type="submit" class="bigbtn" value="$botton"></td>
            <td colspan="5" align="right" valign="middle">$group_input<input name="handle" type="hidden" id="handle" value="$mode"> 
            </td>
            <td>&nbsp;</td>
          </tr>
        </form>
        <tr> 
          <td height="20" colspan="5" valign="top">&nbsp;</td>
          <td valign="top">&nbsp;</td>
HTM;
}

function sh_reg($error='', $fio='', $name='', $domain='', $email='', $hideemail='', $id_user=0) {
if( !$id_user ) {
	$captcha = '<tr> 
            <td height="57" colspan="2" align="right" valign="middle" class="rheader"><font color="#FF0000">*</font>введите 
              число:</td>
            <td valign="top"></td>
            <td width="169" valign="middle"><img src="index.php?go=captcha" width="88" height="31"></td>
            <td valign="middle"><input name="captcha" type="text" id="captcha" size="4"></td>
            <td valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
            <td width="31" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
          </tr>';
	$comments = 'Пожалуйста указывайте существующий email';
	$new = '<font color="#FF0000">*</font>';
	$back = '';
	$value = 'Послать';
	$handle = 'user_reg';
	$star = '<font color="#FF0000">*</font>';
	$avatar = 'аватар:';
	$terms = 'Регистрируясь Вы соглашаетесь с положениями <a href="index.php?go=agreement" class=link>Пользовательского соглашения</a><br>';
} else {
	$terms = '';
	$captcha = '<tr> 
            <td height="25"></td>
            <td width="225"></td>
            <td valign="top"></td>
            <td colspan="4" valign="bottom"><span class="formcomm">Необходимо 
              ввести старый пароль,чтобы изменения вступили в силу</span></td>
          </tr>
          <tr> 
            <td height="40" colspan="2" align="right" valign="middle" class="rheader"><font color="#FF0000">*</font> 
              старый пароль:</td>
            <td valign="top"></td>
            <td colspan="4" valign="middle"> <input name="oldpass" type="password" id="oldpass" size="15"> 
            </td>
          </tr>';
	$back = '<a href="index.php?go=profile&id_user='.$id_user.'" class=link>Назад на профиль</a><br>';
	$comments = '<br><br>';
	$new = 'новый';
	$value = 'Изменить';
	$handle = 'user_edit';
	$star = '';
	$avatar = 'новый аватар:';	
}

return <<<HTM
         <td colspan="6" valign="bottom" class="text">$back
<font color="#FF0000" class="formcomm">$error<br><br></font>$terms<br>Символом <font color="#FF0000">*</font> отмечены обязательные поля</td>
        </tr>
        <form action="handler.php" method="post" enctype="multipart/form-data">
          <tr> 
            <td height="40" colspan="2" align="right" valign="middle" class="rheader">Фио: 
            </td>
            <td width="19" valign="top">&nbsp;</td>
            <td colspan="4" align="left" valign="middle">
            <input name="id_user" type="hidden" id="id_user" value="$id_user">
            <input name="fio" type="text" id="fio" size="50" value='$fio'></td>
          </tr>
          <tr> 
            <td height="40" colspan="2" align="right" valign="middle" class="rheader"> 
              <font color="#FF0000">*</font>имя:</td>
            <td valign="top">&nbsp;</td>
            <td colspan="4" valign="middle"> <input name="name" type="text" id="name" size="15" value='$name'> 
              <span class="formcomm">Данное имя будет использоваться при входе</span></td>
          </tr>
          <tr> 
            <td height="43" colspan="2" align="right" valign="middle" class="rheader">виртуальный 
              домен:&nbsp; </td>
            <td valign="top">&nbsp;</td>
            <td colspan="4" valign="middle" class="formcomm"> <input name="domain" type="text" id="domain" size="20" value=$domain>
              .flogr.ru </td>
          </tr>
          <tr> 
            <td height="46" colspan="2" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
            <td valign="top"></td>
            <td colspan="4" valign="top" class="formcomm">Если виртуальный домен 
              указан то дуступ к Вашим фото будет возможен по адресу <strong>http://виртуальный_домен.flogr.ru 
              </strong></td>
          </tr>
          <tr> 
            <td height="40" colspan="2" align="right" valign="middle" class="rheader"> 
              $avatar</td>
            <td valign="top">&nbsp;</td>
            <td colspan="4" valign="middle"> 
              <input name="avatar" type="file" id="avatar"> </td>
          </tr>
          <tr> 
            <td height="40" colspan="2" align="right" valign="middle" class="rheader">$new пароль: 
            </td>
            <td valign="top">&nbsp;</td>
            <td colspan="4" valign="middle"> <input name="pass" type="password" id="pass" size="15"></td>
          </tr>
          <tr> 
            <td height="40" colspan="2" align="right" valign="middle" class="rheader">$star подтверждение 
              пароля:</td>
            <td valign="top">&nbsp;</td>
            <td colspan="4" valign="middle"> <input name="confpass" type="password" id="confpass" size="15"></td>
          </tr>
          <tr> 
            <td height="69" colspan="2" align="right" valign="middle" class="rheader"><font color="#FF0000">*</font>e-mail:<br> 
              &nbsp;<br> &nbsp; </td>
            <td valign="top">&nbsp;</td>
            <td colspan="4" valign="middle" class="formcomm"> <input name="email" type="text" id="email" size="20" value=$email> 
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
              <input name="hideemail" type="checkbox" id="checked" value="checked" $hideemail>
              скрывать email<br>
            	<br>
              $comments</td>
          </tr>
			$captcha          
          <tr align="center" valign="middle"> 
            <td height="80" colspan="2" align="right" valign="middle"> <input name="Submit" type="submit" class="bigbtn" value="$value"> 
            </td>
            <td colspan="3" align="right" valign="middle"><input name="handle" type="hidden" id="handle" value="$handle"> 
            </td>
            <td colspan="2" align="left" valign="middle"> <input name="Reset" type="reset" class="findbtn" id="Reset" value="Сброс"></td>
          </tr>
		    </form>
		 <tr align="center" valign="middle"> 
          <td >&nbsp;</td>
          <td width="225">&nbsp;</td>
          <td>&nbsp;</td>
          <td width="170">&nbsp;</td>
          <td width="112">&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>    
        <tr align="center" valign="middle"> 
          <td height="20" colspan="5" valign="top">&nbsp;</td>
          <td width="74" valign="top">&nbsp;</td>
HTM;
}


function sh_taginfo($id_author, $id_tag, $tag, $photo_num, $author) {

if($photo_num > 0)	
	$see_photos = '<a href="index.php?go=photo_tag&id_tag='.$id_tag.'">смотреть</a>';
else 
	$see_photos = '';	
return <<<HTM
			          <td colspan="4" valign="top">&nbsp;</td>
        </tr>
        <tr>
          <td width="24"><p></p></td> 
          <td height="84" colspan="3" valign="top"><p><span class="rheader">$tag</span></p>
            <p class="text">Фотографий: $photo_num $see_photos</p>
            <p class="text">Автор: <a href="index.php?go=profile&id_user=$id_author" class=user>$author</a></p>
            </td>
        </tr>
        <tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="25" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="413" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="187" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
        <tr> 
          <td height="20" colspan="3" valign="top">&nbsp;</td>	
HTM;
}

function sh_alltags($tags) {

return <<<HTM
	          <td colspan="4" valign="top">&nbsp;</td>
        </tr>
        <tr>
          <td width="24"><p></p></td> 
          <td height="84" colspan="3" valign="top"><div align=left class=text>
              $tags</div></td>
        </tr>
        <tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="25" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="413" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="187" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
        <tr> 
          <td height="20" colspan="3" valign="top">&nbsp;</td>	
HTM;
}

function sh_out($txt, $align='center') {
return <<<HTM
	          <td colspan="4" valign="top">&nbsp;</td>
        </tr>
        <tr>
          <td width="24"><p></p></td> 
          <td height="84" colspan="4" valign="top"><div align=$align class=text>$txt</div></td>
        </tr>
        <tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="25" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="413" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="187" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
        <tr> 
          <td height="20" colspan="3" valign="top">&nbsp;</td>	
HTM;
}
//-------------------------------------------------------------------------------------------


	/*------------------------------------------------/
	/												  /
	/				FOR CLASS photo					  /
	/												  /
	/------------------------------------------------*/
	
function sh_photo($path, $id_photo, $date, $author_field, $adm_del) {

$del_from_main = '';
if($_SESSION['rights'] == 100) {
	if($adm_del == 'yes')
		$del_from_main = '<a href="handler.php?handle=mn_page&id_photo='.$id_photo.'&rem_add=remove"><img src="images/remove.gif" width="17" height="17" border="0" title="убрать с главной"></a>';
	else 	
		$del_from_main = '<a href="handler.php?handle=mn_page&id_photo='.$id_photo.'&rem_add=add"><img src="images/add.gif" width="17" height="17" border="0" title="добавить на главную"></a>';	
}
	
return <<<HTM
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#BCBCBC">
              
              <tr> 
                <td width="108" height="131" valign="middle" bgcolor="#FFFFFF" >
                <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
                    
                    <tr> 
                      <td width="108" height="108" align="center" valign="middle" colspan=2>
                      <a href="index.php?go=photo&photo_num=$id_photo">
                      <img src="index.php?go=photo_small&photo_num=$id_photo" border="0" align="absmiddle"></a>
                      </td>
                    </tr>
                    $author_field
                    <tr> 
                      <td width="90" height="23" valign="middle" class="date">&nbsp;$date</td>
                      <td width="18" align="left" valign="middle" class="date" >$del_from_main</td>
                    </tr>
                  </table></td>
              </tr>
</table>
HTM;
}

/*
function sh_photo_descr($id_photo, $photo_descr, $mode='change') {

if($mode == 'change') {	
	$form = '<form action="handler.php" method="post">';
	$endform = '</form>';
	$btn = '  <input name="id_photo" type="hidden" id="id_photo" value='.$id_photo.'>
			  <input name="handle" type="hidden" id="handle" value="change_descr">
              <input name="Submit" type="submit" class="bigbtn" value="Изменить">';
	
	$descr = '<br>
               <textarea name="descr" cols="35" rows="4" id="descr" class="formcomm">'.$photo_descr.'</textarea>
               <br>
              <br>';
}
else {
	$form = $endform = $btn = '';
	$descr = $photo_descr;
}
return <<<HTM
     <tr> 
          <td height="20" colspan="14" valign="top">&nbsp;</td>
        </tr>
		$form
        <tr> 
          <td height="75" valign="top">&nbsp;</td>
          <td width="21" valign="top">&nbsp;</td>
          <td width="110" align="right" valign="middle" class="rheader">Описание:&nbsp;&nbsp;</td>
          <td colspan="9" valign="top"> <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
              
              <tr> 
                <td width="448" height="73" valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                    
                    <tr> 
                        <td width="17" height="73" align="right" valign="middle" class="rheader"><br>
                        </td>
                      <td width="431" align="left" valign="middle" class="formcomm">
							$descr
                          </td>
                    </tr>
                  </table></td>
              </tr>
            </table></td>
          <td valign="top">&nbsp;</td>
          <td valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td height="37" colspan="11"><img src="images/spacer.gif" alt="" width="8" height="8"></td>
            <td align="right" valign="bottom">
			$btn
          </td>
          <td valign="top">&nbsp;</td>
          <td valign="top">&nbsp;</td>
        </tr>
		$endform 
HTM;
}
*/

function sh_maxi_photoinfo($tags, $id_author, $author, $made) {
if(!empty($tags))
	$tags = ';&nbsp;&nbsp;<span class="cur">тег(и): </span>'.$tags;

if(!empty($made))
	$made = ';&nbsp;&nbsp;<span class="cur">сделано: </span> <span class="date">'.$made.'</span>';
	
return <<<HTM
        <tr> 
          <td height="20" valign="top">&nbsp;</td>
          <td colspan="7" align="left" valign="top"><span  class="cur">автор: </span> <a href='index.php?go=profile&id_user=$id_author' class=user>$author</a>$tags$made</td>
        </tr>
HTM;
}

function sh_photopost_info($tags, $id_author, $author, $made) {
if(!empty($tags))
	$tags = ', <span class="cur">теги: </span>'.$tags;

if(!empty($made))
	$made = ', <span class="date">'.$made.'</span>';

return <<<HTM
        <tr bgcolor="#EFEFEF"> 
          <td height="20" valign="top" >&nbsp;</td>
          <td colspan="7" align="left" valign="top"><a href='index.php?go=profile&id_user=$id_author' class=user>$author</a>$made$tags</td>
        </tr>
HTM;
}

function sh_maxi_photo($id_photo, $path, $id_album, $alb, $date, $original, $name, $descr, 
					   $id_author, $photo_info, $discuss, $id_group, $id_group_author, $id_post ) {

if( $id_author != null and $id_album != null and $path != null) {
	$size = getimagesize('../files/'.$id_author.'/'.$id_album.'/'.$path);
}	


$width = $size[0];
$height = $size[1];	

$cellw = $width+10; $cellh = $height+10;

if( !empty($discuss))
	$bgcolor = 'bgcolor="#EFEFEF"';

$group = ($id_group) ? '&id_group='.$id_group : '';	
	
$edit = '<tr '.$bgcolor.'> 
          		<td height="15" valign="top">&nbsp;</td>
          		<td colspan="3" align="left" valign="middle"><a href="index.php?go=exif&id_photo='.$id_photo.$group.'" class=link>просмотр Exif данных</a></td>
          		<td colspan="4" align="right" valign="middle"></td>
        	</tr>';

if( $id_author == $_SESSION['id_user'] and empty($discuss) )
	$edit = '<tr> 
          		<td height="15" valign="top">&nbsp;</td>
          		<td colspan="3" align="left" valign="middle"><a href="index.php?go=exif&id_photo='.$id_photo.$group.'" class=link>просмотр Exif данных</a></td>
          		<td colspan="4" align="right" valign="middle">
          		<a href="index.php?go=ed_photo&ph_num='.$id_photo.'" class="link">редактировать 
           		 фото</a><span class="cin">&nbsp;&nbsp;&raquo;&nbsp;</span></td>
        	</tr>';



if( $id_photo != null) {
	if($original) {
		$img = '<a href="index.php?go=photo_orgnl&photo_num='.$id_photo.$group.'" title="Оригинальное изображение">
				<img src="index.php?go=photo_maxi&photo_num='.$id_photo.$group.'" border=0 width='.$width.' height='.$height.'></a>';
	} else 
		$img = '<img src="index.php?go=photo_maxi&photo_num='.$id_photo.$group.'" border=0 width='.$width.' height='.$height.'>';
} else {
	$img = '<img src="images/photo_del.gif" border=0 width=618 height=468>';
}
//if( empty($descr) ) $descr = 'Нет описания';
$del_post_f = '';
if( !empty($discuss) ) {
	$discuss_field = '<tr bgcolor="#EFEFEF">
				<td height="30">&nbsp;</td>
				<td colspan="7" align="left" valign="top" class=text>'.$discuss.'</td>
				</tr>';
	
	$del_post = '';
	
	settype($_SESSION['gr_rights'], 'integer');
	if($_SESSION['gr_rights'] > 49 or $_SESSION['rights'] == 100)
		$del_post = '<a href="handler.php?handle=del_post&id_post='.$id_post.'" class="link">удалить пост</a>';
		
	$del_post_f = '<tr '.$bgcolor.'>
		  <td height="20">&nbsp;</td>		       		 
          <td colspan="7" align="right" valign="top" class=text>'.$del_post.'</td>
		</tr>';
}

$descr_f = '';
if( !empty($descr) ) {
	$descr_f = '<tr> 
          <td height="20">&nbsp;</td>
          <td colspan="7" valign="middle" class="formcomm">'.$descr.'</td>
        </tr>
        <tr> 
          <td height="10" valign="top"></td>
          <td colspan="7" valign="top"></td>
        </tr>';
}



return <<<HTM
          <td colspan="7" align="right" valign="middle"></td>
        </tr>
        $del_post_f				       
        $discuss_field
        <tr $bgcolor> 
          <td height="298">&nbsp;</td>
          
          <td colspan="7" valign="top">
          
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
              
              <tr> 
                <td height=$cellh width=$cellw valign="top">         
          
    		<table width="100%" border="0" cellpadding="4" cellspacing="1" bgcolor="#BCBCBC">
              <tr> 
                <td height=$height width=$width align="center" valign="middle" bgcolor="#FFFFFF">$img</td>
              </tr>
            </table>
                      
			</td>
              <td >&nbsp;</td>
              </tr>
            </table>            
          </td>
          
        </tr>
        <tr $bgcolor> 
          <td height="21">&nbsp;</td>
          <td colspan="4" valign="top" class="date">$date</td>
          <td colspan="3" align="right" valign="middle">
          <a href="index.php?go=scope&id_photo=$id_photo" class="vis" title='Область видимости' >$scope</a>&nbsp;&nbsp</td>
        </tr>
		$descr_f
		$photo_info
		$edit
		 <tr $bgcolor> 
          <td height="1"><img src="images/spacer.gif" alt="" width="24" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="60" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="10" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="345" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="118" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="48" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="20" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
        <tr $bgcolor> 
          <td height="15" colspan="6" valign="top"></td>
          <td valign="top"></td>
HTM;
}

function sh_medium_photo($id_photo, $name, $path, $path2big, $date, $comments, $scope, 
						 $descr, $tag, $id_album, $eddel, $album, $author, $id_author, $mp, $id_group) {
if(!$comments)
	$msg = "Вы действительно желаете удалить данное фото?";
else 
	$msg = "Данное фото содержит комментарии. Удалить фото вместе с комментариями?";

if($_SESSION['rights'] == 100) {
	if($mp == 'yes')
		$mainpage = '<a href="handler.php?handle=mn_page&id_photo='.$id_photo.'&rem_add=remove"><img src="images/remove.gif" width="17" height="17" border="0" title="убрать с главной"></a>';
	elseif($mp == 'no') 
		$mainpage = '<a href="handler.php?handle=mn_page&id_photo='.$id_photo.'&rem_add=add"><img src="images/add.gif" width="17" height="17" border="0" title="добавить на главную"></a>';
}
if($name != '')
	$name_field = '<tr><td height="15" colspan="2" valign="top" class="cur">'.$name.'</td></tr>';
else 
	$name_field = '<tr><td height="15" colspan="2" valign="top" class="noname">нет названия</td></tr>';

if($eddel == 'allow') {
	$del_btn = <<<HTM
	<a onclick="if( confirm('$msg') ) return true; else return false;" href="index.php?go=del_photo&ph_num=$id_photo" title='Удалить'>
	<img src="images/del.gif" width="16" height="16" border="0"></a>
HTM;

	$ed_btn = <<<HTM
	<a href="index.php?go=ed_photo&ph_num=$id_photo" title='Редактировать'><img src="images/edit2.gif" width="16" height="16" border="0"></a>
HTM;
} elseif($eddel == 'forbid') {
	$ed_btn = '';
	$del_btn = '';
	$edit_auth = '<tr> 
    	            <td height="17" colspan="2" valign="top">
        	        <a href="index.php?go=profile&id_user='.$id_author.'" class="user">'.$author.'</a></td>
            	  </tr>';
}

$hint = '';
//if( !empty($descr) ) $hint = 'Описание: '.$descr.'; ';
if( !empty($tag) ) $hint .= 'Тег: '.$tag.'; ';
if( !empty($album) ) $hint .= 'Альбом: '.$album.'; ';

switch ($scope) {
	case 'all': $scope = 'все'; break;
	case 'friends': $scope = 'друзья'; break;
	case 'onlyme': $scope = 'только я'; break;
}

if($id_photo != null)
	$size =  getimagesize('../files/'.$id_author."/".$id_album."/".$path);

$cmnts = '';
if( !is_null($comments) )
	$cmnts = '<a href="index.php?go=photo&photo_num='.$id_photo.'&id_user='.$id_author.'" class="link">комментариев ('.$comments.')</a>';
		
if($id_group) {	
	$group = '&id_group='.$id_group;
	$img = '<img src="index.php?go=photo_medium&photo_num='.$id_photo.$group.'" border="0" title="'.htmlspecialchars($hint).'">';
} elseif($id_group == 0) {
	$img = '<a href="index.php?go=photo&photo_num='.$id_photo.'&id_user='.$id_author.'"><img src="index.php?go=photo_medium&photo_num='.$id_photo.$group.'" border="0" alt="" title="'.htmlspecialchars($hint).'"></a>';		
}

if( $size[0] > $size[1]) {
	//vert
	$align = '            <tr> 
                            <td width="169" height="16" valign="top"></td>
                            <td width="16" valign="top">'.$del_btn.'</td>
                          </tr>
                          <tr> 
                            <td height="152" colspan="2" align="center" valign="middle">'.$img.'</td>
                          </tr>
                          <tr> 
                            <td height="17"></td>
                            <td valign="bottom">'.$ed_btn.'</td>                            
                          </tr>';	
} else {
	$align = '            <tr> 
                            <td width="16" rowspan="3" valign="top" >&nbsp;</td>
                            <td width="153" rowspan="3" align="center" valign="middle">'.$img.'</td>
                            <td width="16" height="16" valign="top" >'.$del_btn.'</td>
                          </tr>
                          <tr> 
                            <td height="153" valign="top" >&nbsp;</td>
                          </tr>
                          <tr>
                            <td valign="bottom" height="16">'.$ed_btn.'</td>
                          </tr>';
}	

return <<<HTM
<table width="100%" border="0" cellpadding="0" cellspacing="0">              
              <tr> 
                <td height="187" colspan="2" valign="top">
                <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#BCBCBC">                    
                    <tr> 
                      <td width="185" height="185" valign="top" bgcolor="#FFFFFF"> 
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">                          
						$align
                        </table></td>
                    </tr>
                  </table></td>
              </tr>
              <tr> 
                <td width="121" height="21" valign="bottom" class="date">$date</td>
                <td width="66" valign="middle" align="right"> 
                <a href="index.php?go=scope&id_photo=$id_photo" class="vis" title='Область видимости' >$scope</a>&nbsp;&nbsp;</td>
              </tr>
				$name_field
              <tr> 
                <td height="17" valign="top">$cmnts</td>
                <td align="right" valign="middle">$mainpage&nbsp;</td>
              </tr>
              $edit_auth              
            </table>
HTM;
}
//-------------------------------------------------------------------------------------------	



	/*------------------------------------------------/
	/												  /
	/				FOR CLASS section				  /
	/												  /
	/------------------------------------------------*/

function sh_section($title, $fill, $first_col, $second_col, $place) {

$bg_underneath = '';	
if($place == 1) {
	$underneath = '<img src="images/under_header1_1.jpg">';
	$bg = "header1_1.jpg";
} else 
	$bg = "header1_1_s.jpg";

$all_col = $first_col + $second_col;
return <<<HTM
		<tr> 
          <td height="31" colspan="$first_col" valign="middle" background="images/$bg">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$title</td>
          <td colspan="$second_col" valign="top"><img src="images/header1_2.jpg" width="217" height="31"></td>
        </tr>
        <tr> 
          <td height="10" colspan="$all_col" valign="top">$underneath</td>
        </tr>
        <tr> 
          <td width="24"><p></p></td>
			$fill
          <td width="31">&nbsp;</td>
        </tr>
        <tr> 
          <td height="20" colspan="$all_col" valign="top">&nbsp;</td>
        </tr>
HTM;
}

function sh_section_fill($photo, $atrib='') {
return <<<HTM
          <td $atrib valign=top>$photo</td>
          <td width=10><p></p></td>

HTM;
}

//-------------------------------------------------------------------------------------------



	/*------------------------------------------------/
	/												  /
	/					CLASS user				      /
	/												  /
	/------------------------------------------------*/

function sh_user_auth_form($cookuser="") {
return <<<HTM
<table width="344" border="0" cellpadding="0" cellspacing="0">
              <!--DWLayoutTable-->
              <tr > 
                <td width="51" rowspan="2" valign="top"><img src="images/welcome2_r1_c1.png" width="51" height="41"></td>
                <td width="293" height="10" valign="top"><img src="images/welcome2_r1_c2.jpg" ></td>
              </tr>
              <tr> 
                <form action="index.php" method="post">
                  <td height="31" valign="middle" background="images/auth_form.gif"> 
                    <span class="pass"> имя</span> <input name="login" type="text" class="login" id="login" size="10" value="$cookuser"> 
                    <span class="pass">пароль</span> <input name="pass" type="password" class="pass" id="pass" size="10"> 
                    <input name="go" type="hidden" id="go" value="auth_in"> <input name="enter" type="submit" class="findbtn" id="enter" value="Войти"></td>
                </form>
              </tr>
              <tr> 
                <td height="18" valign="top"><img src="images/auth_bottom1.jpg" width="51" height="18"></td>
                <td align="right" valign="top" background="images/auth_underneath.jpg"> <a href="index.php?go=passrec" class="reg">забыли 
                  пароль?&nbsp;&nbsp;&nbsp;&nbsp;</a><a href="index.php?go=reg_form" class="reg">регистрация&nbsp;&nbsp;</a></td>
              </tr>
            </table>
HTM;

/*
return <<<HTM
<table width="344" border="0" cellpadding="0" cellspacing="0">
              
              <tr > 
                <td width="51" rowspan="2" valign="top"><img src="images/welcome2_r1_c1.png" width="51" height="41"></td>
                <td height="10" colspan="3" valign="top"><img src="images/welcome2_r1_c2.jpg" ></td>
              </tr>
              <tr> 
                <form action="index.php" method="post">
                  <td height="31" colspan="3" valign="middle" background="images/auth_form.gif"> 
                    <span class="pass"> имя</span> <input name="login" type="text" class="login" id="login" size="10" value="$cookuser"> 
                    <span class="pass">пароль</span> <input name="pass" type="password" class="pass" id="pass" size="10"> 
                    <input name="go" type="hidden" id="go" value="auth_in"> <input name="enter" type="submit" class="findbtn" id="enter" value="Войти"></td>
                </form>
              </tr>
              <tr> 
                <td height="18" valign="top"><img src="images/auth_bottom1.jpg" width="51" height="18"></td>
                <td width="85" valign="top"><img src="images/welcome2_r6_c2.gif" width="85" height="18"></td>
                <td width="192" align="right" valign="top" background="images/welcome2_r6_c4.gif">
                <a href="index.php?go=passrec" class="reg">забыли пароль?&nbsp;&nbsp;&nbsp;&nbsp;</a><a href="index.php?go=reg_form" class="reg">регистрация</a></td>
                <td width="16" background="images/welcome_corner.jpg"></td>
              </tr>
            </table>
HTM;
*/
}	


function sh_user_welcome($name) {
$go = 'index.php?go=profile&id_user='.$_SESSION['id_user'];
$name_shrt = $name;
if( strlen($name) > 12 )
			$name_shrt = substr($name, 0, 12)."...";
					
return <<<HTM
<table border="0" cellpadding="0" cellspacing="0" width="344">
              <!--DWLayoutTable-->
              <tr> 
                <td width="51" rowspan="4"><img name="welcome2_r1_c1" src="images/welcome2_r1_c1.png" width="51" height="41" border="0" alt=""></td>
                <td height="10" colspan="4" valign="top"><img name="welcome2_r1_c2" src="images/welcome2_r1_c2.jpg" width="293" height="10" border="0" alt=""></td>
              </tr>
              <tr> 
                <td height="5" colspan="4" valign="top"><img name="welcome2_r2_c2" src="images/welcome2_r2_c2.jpg" width="293" height="5" border="0" alt=""></td>
              </tr>
              <tr> 
                <td width="80" height="18" valign="middle" bgcolor="#D5FD6B" class="welcome">Здравствуйте,</td>
                <td width="160" valign="middle" background="images/userbg.gif"><a href="$go" class="user">$name_shrt</a></td>
                <td width="33" valign="top" bgcolor="#C4EF4A"><a href="index.php?go=auth_out&left=shortnews" class="exit">Выйти</a></td>
                <td width="20" valign="top"><img name="welcome2_r3_c13" src="images/welcome2_r3_c13.gif" width="20" height="18" border="0" alt=""></td>
              </tr>
              <tr> 
                <td height="8" colspan="4" valign="top"><img name="welcome2_r5_c2" src="images/welcome2_r5_c2.gif" width="293" height="8" border="0" alt=""></td>
              </tr>
              <tr> 
                <td height="18"><img src="images/auth_bottom1.jpg" width="51" height="18"></td>
                <td colspan="4" valign="top"><img name="welcome2_r6_c2" src="images/auth_underneath.jpg" width="293" height="18" border="0" alt=""></td>
                </tr>
            </table>
HTM;
/*
return <<<HTM

<table border="0" cellpadding="0" cellspacing="0" width="344">
  <tr> 
    <td width="51" rowspan="4"><img name="welcome2_r1_c1" src="images/welcome2_r1_c1.png" width="51" height="41" border="0" alt=""></td>
    <td height="10" colspan="4" valign="top"><img name="welcome2_r1_c2" src="images/welcome2_r1_c2.jpg" width="293" height="10" border="0" alt=""></td>
  </tr>
  <tr> 
    <td height="5" colspan="4" valign="top"><img name="welcome2_r2_c2" src="images/welcome2_r2_c2.jpg" width="293" height="5" border="0" alt=""></td>
  </tr>
  <tr> 
    <td width="85" height="18" valign="middle" bgcolor="#D5FD6B" class="welcome">Здравствуйте,</td>
    <td width="138" valign="middle" bgcolor="#C9F457"><a href="$go" class="user">$name</a></td>
    <td width="50" valign="middle" bgcolor="#C4EF4A"><a href="index.php?go=auth_out&left=shortnews" class="exit">Выйти</a></td>
    <td width="20" valign="top"><img name="welcome2_r3_c13" src="images/welcome2_r3_c13.gif" width="20" height="18" border="0" alt=""></td>
  </tr>
  <tr> 
    <td height="8" colspan="4" valign="top"><img name="welcome2_r5_c2" src="images/welcome2_r5_c2.gif" width="293" height="8" border="0" alt=""></td>
  </tr>
  <tr> 
    <td height="18"><img src="images/auth_bottom1.jpg" width="51" height="18"></td>
    <td><img name="welcome2_r6_c2" src="images/welcome2_r6_c2.gif" width="85" height="18" border="0" alt=""></td>
    <td colspan="2" valign="top" background="images/welcome2_r6_c4.gif"></td>
    <td background="images/welcome_corner.jpg"></td>
  </tr>
</table>
HTM;*/
}

//-------------------------------------------------------------------------------------------

function sh_album($id_album, $num_alb, $name, $image, $date, $checked, $del, $id_user) {

$al = "<a href='index.php?go=albums&alb_num=$id_album&id_user=$id_user' class=albumlink>";
$al_end = "</a>";
	
if($checked) {
	//$al = $al_end = "";
	$name = "<span class=albcur>$name</span> <span class=formcomm>($num_alb)</span>";
	$color = "#93C9FF";
	$bgcolor = '#EAF3FF';
} else {
	
	$name = "<a href='index.php?go=albums&alb_num=$id_album&id_user=$id_user' class=album>$name</a> 
			<span class=formcomm> <font color='#0066CC'>($num_alb)<font></span>";
	$color = "#BCBCBC";
	$bgcolor = '#EFEFEF';	
}


if($num_alb > 0)
	$msg = 'Данный альбом содержит фотографии. Удалить альбом вместе со всеми фото?';
else 
	$msg = 'Вы действительно желаете удалить данный альбом?';

if($del)
	$delbtn = <<<HTM
<a onclick="if( confirm('$msg') ) return true; else return false;" 
	                        				href="?go=del_album&alb_num=$id_album" title='Удалить альбом'> 
                              <img src="images/del_alb.gif" border="0" align="absmiddle"></a>	
HTM;
else 
	$delbtn = '&nbsp;';
	
return <<<HTM
<table width="100%" border="0" cellpadding="0" cellspacing="0">
              
              <tr> 
                <td width="110" height="133" valign="top"> 
                <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="$color">                    
                    <tr> 
                      <td width="108" height="131" valign="top" bgcolor="#FFFFFF" >
                      <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="$bgcolor">                          
                          <tr> 
                            <td width="108" height="108" align="center" valign="middle" colspan=2>
                            	$al<img src="index.php?go=alb_im&id_alb=$id_album" border="0" align="absmiddle">$al_end
                            </td>
                          </tr>
                          <tr> 
                            <td height="23" width="91" valign="middle" class="date" >&nbsp;$al
							$date
							$al_end</td>
	                        <td width="17" valign="middle">$delbtn</td>						
                          </tr>
                        </table></td>
                    </tr>
                  </table></td>
              </tr>
              <tr> 
                <td height="36" valign="top">$name</td>
              </tr>
            </table>
HTM;
}
//-------------------------------------------------------------------------------------------


function sh_album_section_fill($album) {
return <<<HTM
    	      <td width="110" height="169" valign="top">$album</td>
    	      <td width="30" valign="top">&nbsp;</td>
HTM;
}

function sh_album_section($title, $page, $allpages, $id_author, $fill='', $init_link='') {

if(isset($_SESSION['id_user']) and $_SESSION['id_user'] == $id_author)
	$left = "&left=albums";
else 
	$left = "&left=usr_albs";	
	
if($allpages != 0) {
	$a = ($fill != '') ? "<a href='index.php?go=all_albums&id_user=".$id_author.$left."' class=link>Все альбомы</a>" : "&nbsp;";
	
	if($page > 1) 
		$back = "<a href='".$init_link."&alb_page=".($page-1)."' class=albnavig >&laquo;</a>";
	else 
		$back = '&nbsp;&nbsp;&nbsp;&nbsp;';
	
	
	if($page < $allpages) 
		$forward = '<a href="'.$init_link.'&alb_page='.($page+1).'" class="albnavig">&raquo;</a>';
	else 
		$forward = '&nbsp;&nbsp;&nbsp;';
		
	$navig = $back.'&nbsp;&nbsp;<span class=albnavig_pg>'.$page.' ('.$allpages.')</span>&nbsp;&nbsp;'.$forward;
} else $navig = '&nbsp;';

if(isset($_SESSION['id_user']) and $_SESSION['id_user'] == $id_author) {
	$add_alb = '<a href="index.php?go=add_album" class="link">Добавить альбом</a>';
} else 
	$add_alb = '&nbsp;';

return <<<HTM
<table border="0" cellpadding="0" cellspacing="0" width="344">
        
        <tr> 
          <td width="51" height="22" valign="top"><img src="images/poptagleft2.gif" width="51" height="22"></td>
          <td colspan="5" valign="top">$title</td>
        </tr>
        <tr> 
          <td height="13"><img src="images/poptagleft3.gif"></td>
          <td colspan="5" valign="top"></td>
        </tr>
          <td valign="top"><img src="images/poptagleft4.jpg" width="51" height="109"></td>
      	$fill
          <td width="13" valign="top">&nbsp;</td>
      	<tr> 
          <td height="30" valign="top">&nbsp;</td>
          <td colspan="3" valign="top" align=middle>$navig</td>
          <td width=30 valign="top">&nbsp;</td>
          <td width=13 valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td height="20" valign="top">&nbsp;</td>
          <td valign="top" width=110>$a</td>
          <td colspan="4" valign="top">$add_alb</td>
        </tr>
        <tr> 
          <td height="20"><img src="images/spacer.gif" alt="" width="51" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="110" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="30" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="110" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="30" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="13" height="1"></td>
        </tr>
      </table>
HTM;
}

function sh_pop_groups($body) {
return <<<HTM
		</td>
        </tr>        
		<tr> 
          <td height="5" valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td height="147" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
              <!--DWLayoutTable-->
              <tr> 
                <td width="51" height="22"></td>
                <td colspan="2" valign="top"><span class="header1">Популярные 
                  группы </span><span class="cin">&nbsp;&raquo;&nbsp;&nbsp;</span><a href="index.php?go=all_groups" class="link">все группы</a></td>
              </tr>
              <tr> 
                <td height="13"></td>
                <td width="272"></td>
                <td width="21"></td>
              </tr>
              <tr> 
                <td height="155" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
                <td valign="top">$body </td>
                <td>&nbsp;</td>
              </tr>
            </table>
HTM;
}

	/*------------------------------------------------/
	/												  /
	/					NEWS					      /
	/												  /
	/------------------------------------------------*/

// TEMP FUNC
function sh_news() {
return <<<HTM
<table width="100%" border="0" cellpadding="0" cellspacing="0">
        
        <tr> 
          <td width="51" height="22" valign="top" background="images/poptagleft2.gif">&nbsp; 
          </td>
          <td colspan="2" valign="middle"><span class="header1">Новости</span><span class="cin">&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</span><a href="index.php?go=allnews" class="link">все 
            новости</a></td>
        </tr>
        <tr> 
          <td height="13"><img src="images/poptagleft3.gif"></td>
          <td width="255"></td>
          <td width="38"></td>
        </tr>
        <tr> 
          <td valign="top"><img src="images/poptagleft4.jpg" width="51" height="109"></td>
          <td valign="top"> <p><span class="date"> 9.11.2007. </span><a href="index.php?go=news&id_news=1" class="news">Торжественный момент настал! Сегодня день рождения нашего сайта. Мы благодарим...<br></a></p>
            <p><span class="date"> 12.10.2007. </span><a href="index.php?go=news&id_news=2" class="news">Подготовка к запуску нашего проекта завершается. Скоро он будет открыт для широких масс пользователей сети...<br>
              <br></a></p>
          </td>
          <td valign="top">&nbsp;</td>
        </tr>
      </table>
HTM;
}

//--------------------------------------------------------------------------------------------

function sh_album_form($id_alb, $mode, $scopebox="", $name="", $error="") {

if($id_alb!=0) $id_alb_input = '<input name="num_album" type="hidden" id="num_album" value='.$id_alb.'>';

if( $mode == "add_photo") {
	$allcheck = "checked";
} else {
	$allcheck = "";	
}

$friendscheck = $onlymecheck = '';
$alldis = $friendsdis = $onlymedis = '';

if(is_array($scopebox))
	foreach ($scopebox as $val) {
		if($val == 'onlyme') { 
			$onlymecheck = 'checked';
			$alldis = $friendsdis = 'disabled';
		}
		if($val == 'all') {
			$allcheck = 'checked';
		}
		if($val == 'friends') {
			$friendscheck = 'checked';
		}
	}

if($mode == 'add_album') {
	$but = 'Добавить';
}
elseif ($mode == 'edit_album') {
	$but = 'Изменить';
}

return <<<HTM
          <td colspan="7" valign="bottom" class="text" height=20>
          <font color=#FF0000 class=formcomm>$error</font> Введите данные об альбоме. Поля помеченные <font color=#FF0000>*</font>
          обязательны к заполнению
          </td>
        </tr>
        <form action="handler.php" method="post" enctype="multipart/form-data">
          <tr> 
            <td colspan="3" width=250 align="right" valign="middle" class="rheader" height=60><font color=#FF0000>*</font>Название: 
            </td>
            <td valign="top" width=19>&nbsp;</td>
            <td colspan="4" align="left" valign="middle" width=387>
            	<input name="album_name" type="text" id="album_name" size="40" value='$name'>
            </td>
          </tr>

<td valign="top">&nbsp;</td>


<td colspan="5" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
                <tr> 
                  <td width="493" height="48" valign="top" bgcolor="#FFFFFF">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                    <!--DWLayoutTable-->
                    <tr> 
                      <td width="224" height="133" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Область 
                        видимости:</td>
                      <td width="19">&nbsp;</td>
                      <td width="319" align="left" valign="middle"> <p class="formcomm"> 
                          <br>
                          <input name="scopebox[]" type="checkbox" $onlymecheck $onlymedis onClick="DisableAll(this, 'scopebox[]')" value="onlyme" >
                          Только для меня<br>
                          <br>
                          <input name="scopebox[]"  type="checkbox" $allcheck $alldis value="all" onClick="CheckAll(this, 'scopebox[]')">
                          Все <br>
                          <br>
                          <input name="scopebox[]"  type="checkbox" $friendscheck $friendsdis value="friends" onClick="UnCheckAll(this, 'scopebox[]', 1)">
                          Друзья<br>
                          <br>
                          При изменении видимости альбома, видимость фотографий 
                          входящих в альбом также изменится <br>
                          <br>
                        </p></td>
                    </tr>
                    <tr> 
                      <td height="1"><img src="images/spacer.gif" alt="" width="224" height="1"></td>
                      <td><img src="images/spacer.gif" alt="" width="19" height="1"></td>
                      <td><img src="images/spacer.gif" alt="" width="319" height="1"></td>
                    </tr>
                  </table>
                  </td>
                </tr>
              </table></td>


<!--
            <td colspan="5" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
                <tr> 
                  <td width="493" height="48" valign="top" bgcolor="#FFFFFF">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                      <tr> 
                        <td width="231" height="113" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Область 
                          видимости:</td>
                        <td width="20">&nbsp;</td>
                        <td width="261" align="left" valign="middle"><p class="formcomm"><br> 
                            <input name="scopebox[]" type="checkbox" $onlymecheck $onlymedis onClick="DisableAll(this, 'scopebox[]')" value="onlyme" >
                            Только для меня<br>
                            <br>
                            <input name="scopebox[]"  type="checkbox" $allcheck $alldis value="all" onClick="CheckAll(this, 'scopebox[]')">
                            Все <br>
                            <br>
                            <input name="scopebox[]"  type="checkbox"  $friendscheck $friendsdis value="friends" onClick="UnCheckAll(this, 'scopebox[]', 1)">
                            Друзья<br><br>
                            При изменении видимости альбома, видимость фотографий входящих в альбом также изменится
                          <br><br></p></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table></td>
            <td colspan="2" valign="top">&nbsp;</td>         
 -->       
          <tr> 
            <td width="25">&nbsp;</td>
            <td width="110">&nbsp;</td>
          	<td width="115">&nbsp;</td>
          	<td width="19">&nbsp;</td>
          	<td width="170">&nbsp;</td>
          	<td width="150">&nbsp;</td>
         	<td width="36">&nbsp;</td>
         	<td width="31">&nbsp;</td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td colspan="5" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
                
                <tr> 
                  <td width="493" height="109" valign="top" bgcolor="#FFFFFF">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                      
                      <tr> 
                        <td width="224" rowspan="2" align="right" valign="middle" class="rheader">Изображение:</td>
                        <td width="19" height="48" valign="top">&nbsp;</td>
                        <td width="319" align="left" valign="middle"><input name="album_im" type="file" id="album_im"></td>
                      </tr>
                      <tr> 
                        <td height="34" valign="top">&nbsp;</td>
                        <td align="left" valign="middle" class="help">Если изображение 
                          не загружено, то в качестве изображения для альбома 
                          будет использовано первое его фото</td>
                      </tr>
                      <tr align="left"> 
                        <td height="27" colspan="3" valign="middle" class="formcomm">&nbsp;&nbsp;изображение 
                          будет сжато чтобы подойти под размеры 100х100</td>
                      </tr>
                      <tr> 
                        <td height="1"><img src="images/spacer.gif" alt="" width="224" height="1"></td>
                        <td><img src="images/spacer.gif" alt="" width="19" height="1"></td>
                        <td><img src="images/spacer.gif" alt="" width="319" height="1"></td>
                      </tr>
                    </table></td>
                </tr>
              </table></td>
            <td colspan="2" valign="top">&nbsp;</td>
          </tr>
          <tr align="center" valign="middle"> 
            <td colspan="3" align="right" valign="bottom" height=40> 
              <input name="Submit" type="submit" class="bigbtn" value=$but></td>
            <td colspan="5" align="right" valign="middle">
            <input name="handle" type="hidden" id="handle" value=$mode> 
			$id_alb_input
            </td>
          </tr>
        </form>
        <tr> 
          <td colspan="6" valign="top">&nbsp;</td>
          <td valign="top">&nbsp;</td>
HTM;
}

function sh_comments_body($id_user, $user, $avatar, $email, $date, $comment, $ip, $id_photo, $id_comm, $nest, $del_comm='') {

if( !empty($del_comm) )	
	$del_comm = "&nbsp;&nbsp;".$del_comm;
	
if($_SESSION['rights'] == 100) $del_comm = $ip."&nbsp;&nbsp;".$del_comm;	
	
if( $id_user )
	$user_field = '<a href="index.php?go=profile&id_user='.$id_user.'" class=user>'.$user.'</a>
                      &nbsp;&nbsp;<a href="mailto:'.$email.'" class=link>'.$email.'</a>'.$del_comm;
else 
	$user_field = '<span class=cur>'.$user.'</span>'.$del_comm;

/*
return <<<HTM

					<tr> 
                      <td width="48" height="20" valign="top" class="text"><p>&nbsp;</p></td>
                      <td colspan="3" valign="middle" class="text"> <p>&nbsp; </p></td>
                    </tr>
                    
                    <tr> 
                      <td rowspan="2" valign="middle" align="center">$avatar</td>
                      <td width="15" rowspan="3" valign="top">&nbsp;</td>
                      <td width="505" height="28" valign="top" class="text"><p>$user_field</p></td>
                      <td width="11"></td>
                    </tr>
                    <tr> 
                      <td height="20" valign="bottom" class="text"><p>$comment</p></td>
                      <td></td>
                    </tr>
                    <tr> 
                      <td height="20" valign="top" class="text"><p>&nbsp; </p></td>
                      <td valign="top" class="text">
                      <p><span class=albumlink><font size="1">добавлено</font>&nbsp;<font size="1">$date</font></span></p></td>
                      <td></td>	
                    </tr>
HTM;
*/

return <<<HTM

                    <tr> 
                      <td height="20" valign="middle" class="text"> <p>&nbsp; </p></td>
                    </tr>
                    
					<tr> 
                      <td height="68" align="center" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
                          <!--DWLayoutTable-->
                          <tr> 
                            <td width="1" height="28" valign="top"><img src="images/spacer.gif" width="$nest" height="1"></td>
                            <td width="48" rowspan="2" align="right" valign="middle">$avatar</td>
                            <td width="14">&nbsp;</td>
                            <td width="507" valign="top" class="text"><p>$user_field</p></td>
                          </tr>
                          <tr> 
                            <td height="17"></td>
                            <td></td>
                            <td valign="bottom" class="text"><p>$comment</p></td>
                          </tr>
                          <tr> 
                            <td height="20"></td>
                            <td valign="top" class="text"><p></p></td>
                            <td></td>
                            <td valign="top" class="text">
                            <p><span class=albumlink><font size="1">добавлено</font>&nbsp;<font size="1">$date</font></span></p></td>
                          </tr>
                          <tr> 
                            <td height="23"></td>
                            <td valign="top" class="text"><p>&nbsp;</p></td>
                            <td></td>
                            <td valign="top" class="text"><a href="index.php?go=add_comment&id_photo=$id_photo&id_comment=$id_comm" class=link>ответить</a>&nbsp;</td>
                          </tr>
                        </table></td>
                    </tr>
                    
HTM;
}

function sh_comments($body, $id_photo, $group=false){
$bgcolor = 'bgcolor="#FFFFFF"';
if($group)
	$bgcolor = 'bgcolor="#EFEFEF"';

if(empty($body)) 
	$body = '<tr><td height="50" class=text align=center valign=middle>Нет комментариев</td></tr>';
	
return <<<HTM
        <tr $bgcolor> 
          <td height="100" valign="top">&nbsp;</td>
          <td>&nbsp;</td>
          <td colspan="6" valign="top">
          	<table width="100%" border="0" cellpadding="0" cellspacing="1" $bgcolor>
           	<tr> 
            	 <td width="570" height="50" valign="top" $bgcolor> 
            	 <table width="100%" border="0" cellpadding="0" cellspacing="0">
            	<tr> 
                      <td width="570" height="22" colspan="4" valign="middle" class="text"> 
                        <p class="header1">Комментарии&nbsp; <a href="index.php?go=add_comment&id_photo=$id_photo" class=link>добавить</a></p></td>
                </tr>			
           		$body
            	 </table>
            	 </td>
          	</tr>   
          	</table>
          </td>
        </tr>
HTM;
}

function sh_add_comment_form($id_photo, $id_comment, $error, $id_discuss=0, $id_post=0) {
$anonym_f = '		<tr> 
                      <td width="24" height="20" valign="top">&nbsp;</td>
                      <td width="24" valign="middle">&nbsp;</td>
                      <td width="513" valign="middle">&nbsp;</td>
                    </tr>';
$handle = 'add_dis_comment';
if($id_discuss == 0) {
	/*
	$anonym_f = '   <tr> 
                      <td width="24" height="40" valign="top">&nbsp;</td>
                      <td width="24" valign="middle"> <input name="anonymous" type="checkbox" value="anonymous"></td>
                      <td width="513" valign="middle" class="formcomm">анонимно</td>
                    </tr>';*/

	$handle = 'add_comment';
}
	
return <<<HTM
        <tr> 
          <td height=30>&nbsp;</td>	
          <td colspan="5" valign="top"><br><font color=#FF0000 class=formcomm>$error</font><br><br></td>
        </tr>
        <tr> 
          <td height=18></td>
          <td colspan="5" valign="top" class="text"><strong>Добавить комментарий</strong></td>
        </tr>     
		<tr> 
          <td height="230"></td>
          <td colspan="5" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
              
              <tr> 
                <td width="561" height="193" valign="top" bgcolor="#FFFFFF">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    
                    <form action="handler.php" method="post">
					$anonym_f
                    <tr> 
                      <td height="139" valign="top">&nbsp;</td>
                      <td colspan="2" valign="middle" class="formcomm"> Комментарий:<br> 
                        <textarea name="comment" cols="50" rows="6" id="comment"></textarea></td>
                    </tr>
                    <tr> 
                      <td height="36" valign="top">&nbsp;</td>
                      <td valign="top">&nbsp;</td>
                      <td align="right" valign="middle">
                      	<input name="id_comment" type="hidden" id="id_comment" value="$id_comment">
                      	<input name="id_photo" type="hidden" id="id_photo" value="$id_photo">
                      	<input name="id_post" type="hidden" id="id_post" value="$id_post">
                      	<input name="id_discuss" type="hidden" id="id_discuss" value="$id_discuss">
                      	<input name="handle" type="hidden" id="handle" value="$handle">
                        <input name="add_comment" type="submit" class="bigbtn" id="add_comment" value="Добавить"> 
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <input name="clear" type="reset" class="bigbtn" id="clean" value="Очистить">
                        &nbsp;&nbsp; </td>
                    </tr>
                    </form>
                  </table></td>
              </tr>
            </table></td>
          <td valign="top">&nbsp;</td>
        </tr>   
HTM;
}

function sh_change_scope_form($id_photo, $error, $scopebox) {

if(is_array($scopebox))
	foreach ($scopebox as $val) {
		if($val == 'onlyme') { 
			$onlymecheck = 'checked';
			$alldis = $friendsdis = 'disabled';
		}
		if($val == 'all') {
			$allcheck = 'checked';
		}
		if($val == 'friends') {
			$friendscheck = 'checked';
		}
	}
		
return <<<HTM
        <tr valign="middle"> 
          <td height="30"></td>		
          <td colspan="14"><font color=#FF0000 class=formcomm>$error</font></td>
        </tr>
		<form action="handler.php" method="post">
        <tr> 
          <td height="115" valign="top">&nbsp;</td>
          <td colspan="11" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
              
              <tr> 
                <td width="581" height="113" valign="top" bgcolor="#FFFFFF"> <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                    
                    <tr> 
                      <td width="231" height="113" align="right" valign="middle" class="rheader">Область 
                        видимости:</td>
                      <td width="19">&nbsp;</td>
                      <td width="331" align="left" valign="middle"><p class="formcomm"> 
                          <br>
                            <input name="scopebox[]" type="checkbox" $onlymecheck onClick="DisableAll(this, 'scopebox[]')" value="onlyme" >
                            Только для меня<br>
                            <br>
                            <input name="scopebox[]" $alldis type="checkbox" $allcheck value="all" onClick="CheckAll(this, 'scopebox[]')">
                            Все <br>
                            <br>
                            <input name="scopebox[]" $friendsdis type="checkbox" $friendscheck value="friends" onClick="UnCheckAll(this, 'scopebox[]', 1)">
                            Друзья<br>
                          <br>                      	
						  <input name="id_photo" type="hidden" id="id_photo" value="$id_photo">
                      	 <input name="handle" type="hidden" id="handle" value="change_scope">
                        </p></td>
                    </tr>
                  </table></td>
              </tr>
            </table></td>
          <td colspan="2" valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td height="40" valign="top">&nbsp;</td>
          <td colspan="11" align="right" valign="middle"><input name="Submit" type="submit" class="bigbtn" value="Изменить"></td>
          <td colspan="2" valign="top">&nbsp;</td>
        </tr>
		</form>

HTM;
}

function sh_discuss_from($photos, $id_group, $posttitle, $imradio, $posttext, $error, $id_discuss=0) {
if(!$id_discuss) {
	$disc_title = '<tr> 
    	      <td height="84"><p></p></td>
        	  <td colspan="2" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Заголовок:</td>
	          <td>&nbsp;</td>
    	      <td colspan="2" valign="middle"> <input name="posttitle" type="text" id="posttitle" value="'.$posttitle.'"></td>
        	  <td valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
    	    </tr>';
	$id_dis_f = '';
} else {
	$disc_title = '';
	$id_dis_f = '<input name="id_discuss" type="hidden" id="id_discuss" value='.$id_discuss.'>';	
}
return <<<HTM



          <td height=25 colspan="6" valign="middle"><font color=#FF0000 class=formcomm>$error</font><p><span class="formcomm">Поля отмеченные <font color=#FF0000>*</font> обязательны к заполнению</span></td>
        </tr>
        <form action="handler.php" method="post" enctype="multipart/form-data">
		$disc_title
        <tr> 
          <td height="20" colspan="7" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
        </tr>
        <tr> 
          <td height="20">&nbsp;</td>
          <td colspan="2" align="left" valign="top" class="rheader"><font color=#FF0000>*</font>Выберите 
            фото:</td>
          <td>&nbsp;</td>
          <td colspan="3" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
        </tr>
        <tr> 
          <td height="134" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td colspan="6" align="left" valign="top"> <div style="overflow: auto;  height: 130px; width: 590px;  padding: 5px"> 
          $photos     
          </div></td>
        </tr>
        <tr> 
          <td height="20" colspan="7" valign="top"><!--DWLayoutEmptyCell-->&nbsp;</td>
        </tr>
        <tr> 
          <td height="182"><p></p></td>
          <td colspan="2" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Текст 
            сообщения :</td>
          <td>&nbsp;</td>
          <td colspan="2" valign="middle"> <textarea name="posttext" cols="40" rows="10" id="posttext">$posttext</textarea></td>
          <td valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td height="40" colspan="5" valign="bottom" align=right>
		  <input name="handle" type="hidden" id="handle" value=add_discuss>$id_dis_f
		   <input name="id_group" type="hidden" id="id_group" value=$id_group> 
          <input name="Submit" type="submit" class="bigbtn" value="Добавить">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
          <td><!--DWLayoutEmptyCell-->&nbsp;</td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
        </form>
        <tr> 
          <td height="20"><img src="images/spacer.gif" alt="" width="25" height="1"></td>
          <td valign="middle"><img src="images/spacer.gif" alt="" width="67" height="1"></td>
          <td valign="top"><img src="images/spacer.gif" alt="" width="160" height="1"></td>
          <td valign="middle"><img src="images/spacer.gif" alt="" width="24" height="1"></td>
          <td valign="top"><img src="images/spacer.gif" alt="" width="162" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="187" height="1"></td>
HTM;
}

function sh_photo_form($id_album, $alb_name, $photoname, $tag_set, $album_set, $error, $new_alb, 
					   $tag, $descr, $scopebox, $scopeboxo, $groups, $cmt_rgt, $mode='add_photo', $id_photo=0) {
$id_user = $_SESSION['id_user'];
$tag = htmlspecialchars($tag);
if(!$id_album) {
	
	$album_field = '<td valign="top">&nbsp;</td>
            <td colspan="6" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
                
                <tr> 
                  <td width="562" height="75" valign="top" bgcolor="#FFFFFF">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                      '.$album_set.'			
                      <tr> 
                        <td height="48" valign="top">&nbsp;</td>
                        <td align="left" valign="middle" class="formcomm"> новый: 
                          <input name="new_alb" type="text" id="new_alb" size="30" maxlength="40" 
                          	value=\''.$new_alb.'\' onKeyPress="ClearSel(\'alb_sel\');"></td>
                      </tr>
                    </table></td>
                </tr>
              </table></td>
            <td colspan="2" valign="top">&nbsp;</td>
            <tr> 
            <td colspan="9" valign="top">&nbsp;</td>
            </tr>';
} else 
	$album_field = '<tr>
          <td height="66" colspan="2" align="right" valign="middle" class="rheader">Альбом:</td>
            <td colspan="2" valign="top">&nbsp;</td>            
            <td colspan="5" align="left" valign="middle">'.$alb_name.'</td>
		  </tr>';

$allcheck = "";	
$o_allcheck = "";
if( $mode == "add_photo") {
	if( empty($error) ) {
		$allcheck = "checked";
		$o_allcheck = "checked";
	}	
}

$friendscheck = $onlymecheck = '';
$alldis = $friendsdis = $onlymedis = '';

if(is_array($scopebox))
	foreach ($scopebox as $val) {
		if($val == 'onlyme') { 
			$onlymecheck = 'checked';
			$alldis = $friendsdis = 'disabled';
		}
		if($val == 'all') {
			$allcheck = 'checked';
		}
		if($val == 'friends') {
			$friendscheck = 'checked';
		}
	}

$o_friendscheck = $o_onlymecheck = '';	
	
if($scopeboxo == 'all')	
	$o_allcheck = 'checked';
elseif ($scopeboxo == 'friends')
	$o_friendscheck = 'checked';
elseif ($scopeboxo == 'onlyme')
	$o_onlymecheck = 'checked';

if($cmt_rgt == 'friends')
	$c_friends_check = 'checked';
elseif ($cmt_rgt == 'forbid_all')
	$c_forbidall_check = 'checked';	
	
if($mode == 'add_photo') {
	$button	= 'Добавить';
	$file = '         <tr> 
                        <td width="224" height="48" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Путь:</td>
                        <td width="19" valign="top">&nbsp;</td>
                        <td width="319" align="left" valign="middle"><input name="photofile" type="file" id="photofile"></td>
                      </tr>
                    <tr> 
                      <td height="20" align="right" valign="middle" class="formcomm">Игнорировать 
                        Exif ориентацию:</td>
                      <td valign="top">&nbsp;</td>
                      <td align="left" valign="middle">
<input name="Exif_ignore" type="checkbox" id="Exif_ignore" value="checkbox"></td>
                    </tr>
                    <tr> 
                      <td height="20" align="right" valign="middle" class="formcomm">&nbsp;</td>
                      <td valign="top">&nbsp;</td>
                      <td align="left" valign="middle">&nbsp;</td>
                    </tr>
                    <tr>                                           
                      ';
	$formats = '<p class=formcomm>Допустимые форматы изображения: <b>jpeg</b>, <b>png</b>, <b>gif</b></p>';
}elseif ($mode == 'ed_photo') {
	$file = '';
	$button = 'Изменить';
	$formats = '';
}

return <<<HTM
          <td colspan="8" valign="bottom" class="text"><font color=#FF0000 class=formcomm>$error</font>
          Введите данные о фото <p><span class="formcomm">Поля отмеченные <font color=#FF0000>*</font> обязательны к заполнению</span></p>
		  $formats 
		</td>
        </tr>
        <tr> 
          <td colspan="9" valign="bottom" class="text">&nbsp;</td>
        </tr>
        <form action="handler.php" method="post" enctype="multipart/form-data" name="addphoto" id="addphoto">
          $album_field
        	<tr> 
            <td valign="top">&nbsp;</td>
            <td colspan="6" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">                
                <tr> 
                  <td width="562" height="75" valign="top" bgcolor="#FFFFFF">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                 	  <tr> 
                        <td width="224" height="48" align="right" valign="middle" class="rheader">Имя фото:</td>
                        <td width="19" valign="top">&nbsp;</td>
                        <td width="319" align="left" valign="middle">
                        <input name="photoname" type="text" id="photoname" size="20" maxlength="20" value="$photoname"> 
                        <span class="formcomm">до 20-и символов</span></td>
                      </tr>     
					$file
					<tr> 
                      <td height="20" align="right" valign="middle" class="formcomm">отключить 
                        комментарии:</td>
                      <td valign="top">&nbsp;</td>
                      <td align="left" valign="middle"><input name="cmt_rgt[]" type="checkbox" id="cmt_rgt[]" value="forbid_all" $c_forbidall_check></td>
                    </tr>
					<tr> 
                      <td height="22" align="right" valign="middle" class="formcomm">комментировать 
                        только друзьям:</td>
                      <td valign="top">&nbsp;</td>
                      <td align="left" valign="middle"><input name="cmt_rgt[]" type="checkbox" id="cmt_rgt[]" value="friends" $c_friends_check></td>
                    </tr>
                    </table></td>
                </tr>
              </table></td>
            <td colspan="2" valign="top">&nbsp;</td>
          </tr>
         <tr> 
            <td colspan="9" valign="top">&nbsp;</td>
          </tr>
          <tr> 
            <td valign="top">&nbsp;</td>
            <td colspan="6" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
                <tr> 
                  <td width="562" height="48" valign="top" bgcolor="#FFFFFF">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                      
                      <tr> 
                        <td width="231" rowspan="2" align="right" valign="middle" class="rheader"><font color=#FF0000>*</font>Область 
                          видимости:</td>
                        <td width="20" height="26">&nbsp;</td>
                        <td width="154" valign="top" class="cin"><!--DWLayoutEmptyCell-->&nbsp;</td>
                        <td width="176" valign="middle" class="help">&nbsp;&nbsp;оригинал</td>
                      </tr>
                      <tr> 
                        <td height="87">&nbsp;</td>
                        <td align="left" valign="middle"><p class="formcomm"> 
                            <input name="scopebox[]" type="checkbox" $onlymecheck onClick="DisableAll(this, 'scopebox[]')" value="onlyme" >
                            Только для меня<br>
                            <br>
                            <input name="scopebox[]" $alldis type="checkbox" $allcheck value="all" onClick="CheckAll(this, 'scopebox[]')">
                            Все <br>
                            <br>
                            <input name="scopebox[]" $friendsdis type="checkbox" $friendscheck value="friends" onClick="UnCheckAll(this, 'scopebox[]', 1)">
                            Друзья<br>
                            <br>
                          </p></td>
                        <td valign="top"><p class="formcomm"> 
                            <input name="scopeboxo" type="radio"   value="onlyme" $o_onlymecheck>
                            Только для меня<br>
                            <br>
                            <input name="scopeboxo"  type="radio"  value="all" $o_allcheck>
                            Все <br>
                            <br>
                            <input name="scopeboxo"  type="radio"  value="friends" $o_friendscheck>
							Друзья<br>
                            <br>
                          </p></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table></td>
            <td colspan="2" valign="top">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="9" valign="top">&nbsp;</td>
          </tr>          
 		<tr> 
          <td height="81">&nbsp;</td>
          <td colspan="6" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
              <!--DWLayoutTable-->
              <tr> 
                <td width="582" height="80" valign="top" bgcolor="#FFFFFF"> <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                    <!--DWLayoutTable-->
                    <tr> 
                      <td width="231" height="80" align="right" valign="middle" class="rheader">Поместить 
                        в группы:</td>
                      <td width="20">&nbsp;</td>
                      <td width="331" valign="middle" class="formcomm">$groups</td>
                    </tr>
                  </table></td>
              </tr>
            </table></td>
          <td colspan="2">&nbsp;</td>
        </tr>
		<tr> 
            <td colspan="9" valign="top">&nbsp;</td>
        </tr>                  
          <tr> 
            <td valign="top">&nbsp;</td>
            <td colspan="6" valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#9A9A9A">
                <tr> 
                  <td width="562" height="75" valign="top" bgcolor="#FFFFFF">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                      <tr align="left"> 
                        <td width="231" height="39" align="right" valign="middle" class="rheader">Теги:</td>
                        <td valign="top">&nbsp;</td>
                        <td width="336" valign="middle"> 
                       	$tag_set
                       </td>
                      </tr>
                      <tr> 
                        <td height="48" align="right" valign="middle" class="formcomm">Используемые теги:</td>
                        <td width="18">&nbsp;</td>
                        <td align="left" valign="middle" class="formcomm"> 
                        <input name="tag" type="text" id="tag" size="45" value="$tag"></td>
                      </tr>
                      <tr align="left"> 
                        <td height="27" colspan="3" valign="middle" class="formcomm">&nbsp;&nbsp;ключевая 
                          фраза характеризующая фото (разные теги разделяются <b>запятыми</b>)</td>
                      </tr>
                    </table></td>
                </tr>
              </table></td>
            <td colspan="2" valign="top">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2" valign="top">&nbsp;</td>
            <td colspan="2" valign="top">&nbsp;</td>
            <td colspan="5" valign="top" class="formcomm">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="9" valign="top" class="help">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2" align="right" valign="middle" class="rheader">Описание:</td>
            <td colspan="2" valign="top">&nbsp;</td>
            <td colspan="5" align="left" valign="middle"><textarea name="descr" cols="35" rows="4" id="descr">$descr</textarea> 
            </td>
          </tr>

          <tr> 
            <td colspan="9" valign="top">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="3" align="right" valign="middle"> <input name="Submit" type="submit" class="bigbtn" value="$button"></td>
            <td colspan="3" align="right" valign="middle">
            <input name="id_photo" type="hidden" id="id_photo" value="$id_photo">
            <input name="handle" type="hidden" id="handle" value=$mode> 
            <input name="id_user" type="hidden" id="id_user" value=$id_user> 
              <input name="id_album" type="hidden" id="id_album" value="$id_album"> </td>
            <td width="68" align="right" valign="middle"> <input name="Reset" type="reset" class="findbtn" id="Reset" value="Сброс"></td>
            <td colspan="2" valign="top">&nbsp;</td>
          </tr>
        </form>
        <tr> 
          <td height="0"></td>
          <td width="224"></td>
          <td width="8"></td>
          <td width="10"></td>
          <td width="166"></td>
          <td width="96"></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr align="center" valign="middle"> 
          <td colspan="6" valign="top">&nbsp;</td>
          <td valign="top">&nbsp;</td>
          <td width="13" valign="top">&nbsp;</td>          
HTM;
}

function sh_agreement() {
return <<<HTM
<p class=text>Настоящее Пользовательское соглашение (далее - Соглашение) регламентирует отношения между 
		администрацией сервиса «Flogr.ru» (далее - Администрация) и физическим лицом (далее - Пользователь) 
		по размещению созданного Пользователем изображения (далее — Изображение)  на сайте  Flogr.ru 
		(по адресу <a href='/' class=link>http://flogr.ru</a>, далее — Сайт) или использованию 
		Сайта иным образом.</p>

		<p class=text>Регистрацией на Сайте Пользователь выражает свое согласие со всеми условиями настоящим Соглашения 
		и обязуется их соблюдать.</p>

		<p class=text>Пользователь получает право размещать на Сайте Изображения и текстовые сообщения на условиях 
		настоящего Соглашения. При размещении Изображения Администрация указывает имя (псевдоним), которое Пользователь 
		указал при регистрации на Сайте.</p>

		<p class=text>Изображение, размещенное на Сайте, считается собственностью разместившего его Пользователя. 
		Пользователи сайта не имеют никаких прав на использование Изображений других пользователей и несут полную 
		ответственность перед автором за неправомерное использование Изображений.</p>

		<p class=text>Пользователь соглашается с тем, что Изображение, размещаемое на сайте, будет доступно публично, 
		за исключением случаев, когда Пользователь установил ограничение доступа путем присвоения Изображению статуса 
		«Только я» или «Друзья». В этом случае Изображение будет доступно только Пользователю или указанной Пользователем 
		группе других Пользователей.</p>

		<p class=text>Размещаемые пользователем Изображения и текстовые сообщения не должны содержать:
		<br>
		- объекты, права интеллектуальной собственности на которые принадлежат третьим лицам;
		<br>
		- элементы порнографии;
		<br>
		- материалы, противоречащие законодательству РФ;
		<br>
		- материалы,  содержащие  ложную, оскорбляющую, вульгарную информацию, ненормативную лексику (в том числе 
		завуалированную);
		<br></p>
		
		<p class=text>Администрация имеет право удалить Изображения или текстовые сообщения, несоответствующие условиям 
		настоящего Соглашения, а также блокировать доступ к Сайту  нарушившего их Пользователя.</p>

		<p class=text>Администрация не несет ответственности за использование (как правомерное, так и не правомерное) 
		Изображений Пользователем или третьими лицами, включая (но не ограничиваясь) копирование, тиражирование и распространение.</p>

		<p class=text>Услуги Сайта предоставляются по принципу «как есть». Администрация не гарантирует бесперебойную и 
		безошибочную работу сайта, сохранность Изображений, текстовых сообщений и данных личного профиля пользователя. 
		Администрация не возмещает никакой ущерб, прямой или косвенный, причиненный Пользователю или третьим лицам в результате 
		использования или невозможности использования Сайта.</p>

		<p class=text>Администрация в любое время может изменить текст настоящего соглашения, находящийся по адресу 
		<a href='index.php?go=agreement' class=link>http://flogr.ru/index.php?go=agreement</a>.</p>
HTM;
}

function sh_faq() {
return <<<HTM
             <p class=header1>Основные вопросы</p>
              <p><span class="rheader">Что такое Flogr?</span><br>
                Flogr — это сервис, который поможет Вам наилучшим образом организовать 
                собственный фото-архив. Каждая фотографиям может быть снабжена 
                кратким описанием с указанием места и времени, а также технических 
                параметров съемки. </p>
              <p>Сортировка фотографий возможна по альбомам и с применением тэгов 
                — слов-меток, характеризующих каждую фотографию.</p>
              <p>Flogr поможет установить права доступа для разных категорий пользователей 
                к Вашим фотографиям. Каждому снимку по отдельности или целому 
                альбому можно указать степень конфиденциальности, например, спрятав 
                его ото всех, сделав его «видимым» для всех посетителей сайта 
                или только для Ваших друзей. </p>
              <p>Одна из важных особенностей Flogr'а — возможность организации 
                персонального фото-блога, в котором пользователь может размещать 
                снабженные комментариями фотографии, отобранные по определенному 
                критерию. </p>
              <p  class="header1"><br>
                Работа с фотографиями</p>
              <p><span class="rheader">Как загрузить фотографию?</span><br>
                Существует несколько способов загрузить новую фотографию. <br><br>
                1. На главной странице сайта через ссылку «Добавить»</p>
              <p><img src="images/help1.png" width="353" height="27"></p>
              <p>&nbsp;</p>
              <p>2. Через «Мои фото» в главном меню</p>
              <p><img src="images/help2.png" width="161" height="41"></p>
              <p> далее ссылка «Добавить фото»</p>
              <p> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="images/help2_1.png" width="250" height="27"></p>
              <p><br>
                3. Открыть альбом, в который нужно добавить фотографию, открыть 
                ссылку «Добавить фото в альбом»</p>
              <p><img src="images/help3.png" width="372" height="27"> </p>
              <p></p>
              <p>Результатом любого из этих действий будет открытие формы «Добавить 
                новое фото». Обязательные к заполнению поля: Альбом, Путь, Область 
                видимости. <br>
                «Альбом» - выберите существующий альбом из списка или введите 
                название нового.<br>
                «Имя фото» - Вы можете указать название фотографии (по умолчанию 
                название формируется из названия файла с фотографией).<br>
                «Путь» - локальный путь к файлу на Вашем компьютере.<br>
                «Область видимости» - укажите, кто сможет получить доступ к фотографии 
                (уменьшенному изображению и оригиналу).<br>
                «Поместить в группы» - Вы можете отметить галочками группы, в 
                которых желаете опубликовать загружаемую фотографию. Подробнее 
                о группах читайте в разделе «Работа с группами».<br>
                «Теги» - перечислите ключевые слова, характеризующие фотографию, 
                по которым впоследствии можно будет быстро найти нужные изображения.<br>
                «Описание» - по Вашему усмотрению это текстовое поле может содержать 
                описание сюжета фотографии, любую дополнительную информацию. </p>
              <p><span  class="rheader">Как создать альбом?</span><br>
                Откройте ссылку «Альбомы» на главной странице.</p>
              <p><img src="images/help1.png" width="353" height="27"></p>
              <p>В левой колонке, в нижней части блока «Ваши альбомы» откройте 
                ссылку «Добавить альбом».<br>
                В открывшейся форме введите название альбома, укажите область 
                видимости и фотографию — обложку.<br>
              </p>
              <p  class="rheader">&nbsp; </p>
              <p> <span class="rheader">Что такое теги?</span><br>
                Теги — это ключевые слова (текстовые метки), которые характеризуют 
                фотографию. Теги позволяют создать структуру хранения фотографий, 
                отличную от классической структуры альбомов. Слабое место парадигмы 
                альбомов — фотография не может находиться более, чем в одном альбоме, 
                хотя её тематика может соответствовать сразу нескольким альбомам.<br>
                Например, фотография из Вашей последней поездки в Турцию может 
                физически находиться в альбоме «Лето 2007» и при этом иметь теги 
                «лето», «отдых», «турция», «2007», «семья», «море». И Вы в любой 
                момент можете вывести на экран все фотографии, отмеченные тегом 
                «турция» или «море». Таким образом, будут образованы виртуальные 
                альбомы, а Ваша фотография сможет быть в нескольких виртуальных 
                альбомах.</p>
              <p> <span class="rheader">Что такое облако тегов?</span><br>
                Облако тегов — это специальная страница, на которой размещены 
                наиболее используемые теги всех пользователей flogr.ru.<br>
                В этом списке размер букв, которыми написан каждый тег, тем больше, 
                чем чаще он используется пользователями сайта.<br>
                <br>
                <span class="rheader">Зачем мне друзья?</span><br>
                Пользователи, которых Вы добавили в друзья, получают доступ к 
                области видимости «для друзей» Ваших фотографий. <br>
                Последние фотографии Ваших друзей доступны на странице «Друзья», 
                что избавляет от необходимости ежедневно просматривать альбомы 
                интересных Вам людей в поисках обновлений.<br>
                В свою очередь, Вы сможете получить доступ к фотографиям других 
                пользователей с областью видимости «для друзей», если эти пользователи 
                добавят в друзья Вас.<br>
                Добавление пользователя в друзья и удаление из друзей производится 
                из Профиля пользователя.</p>
              <p><span class="rheader">Могу ли я ограничить доступ других пользователей 
                к своим фотографиям?</span><br>
                Да, для этого существуют области видимости фотографий. В настоящее 
                время их всего три: «все», «друзья», «только я». Области видимости 
                задаются отдельно для уменьшенных фотографий и оригиналов.<br>
                «все» - фотографию смогут увидеть все посетители flogr.ru.<br>
                «друзья» - фотография кроме Вас будет доступна пользователям, 
                которых Вы добавили в друзья.<br>
                «только я» - фотография будет видна Вам и скрыта для всех остальных 
                пользователей сайта.<br>
                Область видимости может быть задана при загрузке фотографии на 
                сайт либо при последующем редактировании свойств фотографии. </p>
              <p class="header1"><br>Группы</p>
              <p><span class="rheader">Для чего нужны группы?</span><br>
                Группы — это объединения людей по интересам. Каждая группа имеет 
                тему (например «Галерея пользователей nextOne»), в рамках которой 
                происходит публикация фотографий и их обсуждение.</p>
              <p><span class="rheader">Какие существуют виды групп?</span><br>
                На нашем сайте есть три вида групп: публичная, публичная с регистрацией 
                и частная.<br>
                Публичная — просмотр свободный, для участия в дискуссиях достаточно 
                пройти автоматическую регистрацию.<br>
                Публичная с регистрацией — просмотр свободный, для участия в дискуссиях 
                необходима Ваша регистрация администратором группы.<br>
                Частная — для просмотра и участия в дискуссиях необходима Ваша 
                регистрация администратором группы.<br>
                Администратором группы является создавший её пользователь. Он 
                может назначить модераторов из числа участников группы. Модераторы 
                имеют права на удаление постов и комментариев.</p>
              <p><span class="rheader">Как разместить фотографию в группе?</span><br>
                Для того, чтобы опубликовать фотографию, её необходимо поместить 
                в нужную группу путем редактирования параметра «Поместить в группу» 
                в свойствах фотографии. Это можно сделать при загрузке фотографии 
                на сайт или, если фотография уже загружена, через ссылку «редактировать 
                фото»:<br>
			  </p>              
			  <p><img src="images/edit_photo.jpg" width="487" height="193"></p>
HTM;
}
//#######################################################3


function sh_navig($address, $previous, $next, $pagename, $pages, $curpage, $all_pages, $cols) {

if($pages != '&nbsp;')	
	$go2page = '<a href="javascript:multi_page_jump(\''.$pagename.'\', \''.$address.'\', '.($all_pages).');" class="navig">Перейти</a>';
else 
	$go2page = '&nbsp;';
	
return <<<HTM
	  <tr> 
          <td height="25" colspan="$cols" valign="top">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
              <tr> 
          		<td height="25" width="24" valign="top">&nbsp;</td>
          		<td width="21">$previous</td>
          		<td width="148" align="center">$pages</td>
          		<td width="101" align="center">$next</td>
          		<td valign="middle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$go2page</td>
      		  </tr>
            </table>
          </td>
      </tr>      		  
HTM;
}


function sh_add_photo_spacers() {
return <<<HTM
        <tr> 
          <td height="0"></td>
          <td width="224"></td>
          <td width="8"></td>
          <td width="10"></td>
          <td width="166"></td>
          <td width="96"></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>	
HTM;
}


function sh_mainpage_spacers() {
return <<<HTM
		<tr> 
          <td height="1"><img src="images/spacer.gif" width="25" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="110" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="10" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="110" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="10" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="110" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="10" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="54" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="56" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="10" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="110" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" width="10" height="1" border="0" alt=""></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
HTM;
}
/*
function sh_reg_ok_spacers() {
return <<<HTM
		<tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="25" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="413" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="187" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
HTM;
}
 */       
function sh_my_album_photos_spacers() {
return <<<HTM
       <tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="24" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="21" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="110" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="56" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="10" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="82" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="101" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="4" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="10" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="7" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="14" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="166" height="1"></td>
          <td valign="top"><img src="images/spacer.gif" width="10" height="1" border="0" alt=""></td>
          <td valign="top"><img src="images/spacer.gif" width="41" height="1" border="0" alt=""></td>
        </tr>
HTM;
}
function sh_commentform_spacers() {
return <<<HTM
        <tr> 
          <td height="1"><img src="images/spacer.gif" alt="" width="24" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="187" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="10" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="218" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="148" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="38" height="1"></td>
          <td><img src="images/spacer.gif" alt="" width="31" height="1"></td>
        </tr>
HTM;
}        
?>
