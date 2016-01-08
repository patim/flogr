<?php
/////////////////////////////////////////////////////////////////////
// The hub/swithc for the photo website
// Maxim Zalutskiy
// 2008
/////////////////////////////////////////////////////////////////////
include ('sh.inc.php');
include ('f.inc.php');

session_start();

$handle = $_REQUEST['handle'];

$page = new ph_blog_page("[Photo.Blog] Главная");
$go = '';
 
 //>>>/		      Handling of all the forms     	    \<<<\\
//___/-----------------------------------------------\___\\

switch($handle) {
	case 'user_reg':
		$pass = !empty($_POST['pass']) ? md5($_POST['pass']) : "";
		$confpass = !empty($_POST['confpass']) ? md5($_POST['confpass']) : "";
		
		$res = $page->_user->registration($_POST['fio'], $_POST['name'], $_POST['domain'], $_FILES['avatar'], $pass, $confpass, 
										  $_POST['email'], $_POST['captcha'], $_POST['hideemail']);
		if($res==1)
			$go = 'index.php?go=reg_ok';
		else {
			$error = $res;
			$go = 'index.php?go=reg_form&error='.$error.'&fio='.$_POST['fio'].'&name='.$_POST['name'].
				  '&email='.$_POST['email'].'&domain='.$_POST['domain'].'&hideemail='.$_POST['hideemail'];			
		}
	break;
	
	case 'passrec':
		
 		$res = $page->pass_send($_POST['name'], $_POST['email']);
		
		if($res == 1)
			$go = "index.php?go=pass_sent";
		else {
			$go = "index.php?go=passrec&error=".$res;
		}
		
	break;
	
	case 'user_edit':
		$pass = !empty($_POST['pass']) ? md5($_POST['pass']) : "";
		$confpass = !empty($_POST['confpass']) ? md5($_POST['confpass']) : "";
		
		if(isset($_POST['oldpass']))
			$oldpass = md5($_POST['oldpass']);
		elseif (isset($_POST['uid']))
			$oldpass = $_POST['uid'];
		
		$res = $page->_user->edit($_POST['id_user'], $_POST['fio'], $_POST['name'], $_POST['domain'], $_FILES['avatar'], $pass,  
								  $confpass, $_POST['email'], $_POST['hideemail'], $oldpass);
		if($res==1)
			$go = 'index.php?go=profile&id_user='.$_POST['id_user'];
		else {
			$error = $res;
			$go = 'index.php?go=prfl_edit&id_user='.$_POST['id_user'].'&error='.$error.'
				   &fio='.$_POST['fio'].'&name='.$_POST['name'].'&email='.$_POST['email'].'&hideemail='.$_POST['hideemail'].'&domain='.$_POST['domain'];			
		}
	break;	

	case 'add_comment':
		
		$res = $page->add_comment( $_POST['id_photo'], $_POST['comment'], $_POST['anonymous'], $_POST['id_comment'] );
		if($res == 1)
			$go = 'index.php?go=photo&photo_num='.$_POST['id_photo'].'&add_cmt=1&com_page='.$_SESSION['com_page'].'#comments';	
		else {
			$error = $res;
			$go = 'index.php?go=photo&error='.$error.'&photo_num='.$_POST['id_photo'].'&add_cmt=1'.'#comments';
		}

	break;		

	case 'add_dis_comment':
		
		$res = $page->add_dis_comment($_POST['id_post'], $_POST['comment']);
		if($res == 1)
			$go = 'index.php?go=discuss&id_discuss='.$_POST['id_discuss'];	
		else {
			$error = $res;
			$go = 'index.php?go=post_comm&error='.$error.'&id_post='.$_POST['id_post'].'&add_cmt=1';
		}

	break;
	
	case 'add_album':
		$res = $page->add_album( $_POST['album_name'], $_POST['scopebox'], $_FILES['album_im'], $album);
		$_SESSION['alb_ses'] = $album;
		
		if($res == 1) {
			unset($_SESSION['scopebox']);
			$go = 'index.php?go=albums&left=albums&alb_num='.$album."&id_user=".$_SESSION['id_user'];
		} else {
			$_SESSION['scopebox'] = $_POST['scopebox'];
			$error = $res;
			$go = 'index.php?go=add_album&error='.$error.'&album_name='.$_POST['album_name'];
		}
	break;

	case 'edit_album':
		$res = $page->edit_album( $_POST['album_name'], $_POST['scopebox'], $_FILES['album_im'], $_POST['num_album']);

		if($res == 1)
			$go = 'index.php?go=albums&alb_num='.$_POST['num_album']."&id_user=".$_SESSION['id_user'];
		else {
			$error = $res;
			$go = 'index.php?go=edit_album&error='.$error.'&album_name'.$_POST['album_name'].'&num_album='.$_POST['num_album'];
		}
	break;

	case 'ed_photo':
		$id_album = $_POST['id_album'];		
		$res = $page->ed_photo($_POST['id_photo'], $_POST['photoname'], $_POST['tag_select'], $_POST['tag'], 
							   $_POST['descr'], $_POST['scopebox'], $_POST['scopeboxo'], 
							   $id_album, $_POST['alb_sel'], $_POST['new_alb'], $_POST['groupbox'], $_POST['cmt_rgt'][0]);
		
		if($res == 1) {
			$ph_page = isset($_SESSION['ph_page']) ? $_SESSION['ph_page'] : 1;
			$go = 'index.php?go=albums&left=albums&alb_num='.$id_album.'&ph_page='.$ph_page.'&id_user='.$_SESSION['id_user'];
		} else {
			// alb_num - where to go after the job is done
			$error = $res;			
			$go = "index.php?go=ed_photo&ph_num=".$_POST['id_photo']."&error=".$error;
		}
	break;
	
	case 'add_photo':
		$id_album = $_POST['id_album'];
		if( isset($_POST['Exif_ignore']) )
			$exifignore = true;
		else 
			$exifignore = false;
			
		$res = $page->add_photo($_POST['photoname'], $_POST['tag_select'], $_POST['tag'], $_POST['descr'], $_POST['scopebox'], $_POST['scopeboxo'], $_POST['groupbox'], 
								$_FILES['photofile'], $id_album, $_POST['alb_sel'], $_POST['new_alb'], $exifignore, $_POST['cmt_rgt'][0]);
								
		if($res == 1) {			
			unset($_SESSION['descr']);
			unset($_SESSION['scopebox']);
			unset($_SESSION['tag']);
			unset($_SESSION['groupbox']);
			$go = 'index.php?go=albums&left=albums&alb_num='.$id_album.'&id_user='.$_SESSION['id_user'];
		}
		else {
			// alb_num - where to go after the job is done
			// 
			$error = $res;			
			$go = "index.php?go=add_photo&alb_num=0&error=".$error."&tag_select=".$_POST['tag_select'].
				  "&alb_num=".$_POST['id_album']."&alb_sel=".$_POST['alb_sel'].
				  "&new_alb=".$_POST['new_alb']."&phtnm=".$_POST['photoname']."&scopeboxo=".$_POST['scopeboxo'];
			$_SESSION['descr'] = $_POST['descr'];
			$_SESSION['scopebox'] = $_POST['scopebox'];
			$_SESSION['tag'] = $_POST['tag'];
			$_SESSION['groupbox'] = $_POST['groupbox'];
		}
	break;
	
	case 'add_friend':
		if(is_array($_REQUEST['friend_select']))
			$friends = $_REQUEST['friend_select'];
		else 
			$friends[] = $_REQUEST['friend_select'];
			
		$page->add_friend($_REQUEST['id_user'], $friends);
	break;
	
	case 'alw_comment':
		$go = $_SERVER['HTTP_REFERER'];
		$page->allow_comment($_GET['id_user']);
	break;	

	case 'frbd_comment':
		$go = $_SERVER['HTTP_REFERER'];
		$page->forbid_comment($_GET['id_user']);
	break;	
	
	
	case 'change_scope':
		$res = $page->change_scope($_POST['id_photo'], $_POST['scopebox']);
		
		if( $res == 1) {
			unset($_SESSION['scopebox']);
			$go = $_SESSION['backok'];
			unset($_SESSION['backok']);
		} else {
			$_SESSION['scopebox'] = $_POST['scopebox'];	
			$go = "index.php?go=scope&id_photo=".$_POST['id_photo']."&error=".$res;		
		}
	break;
	
	/*
	case 'change_descr':
		$res = $page->change_descr($_POST['id_photo'], $_POST['descr']);
		$go = $_SESSION['back'];
	break;
	*/
	
	case 'find':
		$qres = $page->find($_POST['search'], $_POST['select']);
		$_SESSION['search_res'] = $qres;
		$go = 'index.php?go=search_res&mode='.$_POST['select'].'&search='.urlencode($_POST['search']);
	break;

	//------------------------------ administration ------------------------------------	
	case 'mn_page':
		$go = $_SERVER['HTTP_REFERER'];
		if(isset($_GET['id_photo'])) {
			$table = 'photo';
			$id = $_GET['id_photo'];
		} elseif (isset($_GET['id_group'])) {
			$table = 'group';
			$id = $_GET['id_group'];
		}
		$page->main_page_remove_add($id, $table, $_GET['rem_add']);		
		
	break;
	
	case 'pb_ban':
		$go = $_SERVER['HTTP_REFERER'];
		if(isset($_GET['id_user'])) {
			$page->publish_ban_unban($_GET['id_user'], 'ban');
		}
	break;

	case 'pb_unban':
		$go = $_SERVER['HTTP_REFERER'];
		if(isset($_GET['id_user'])) {
			$page->publish_ban_unban($_GET['id_user'], 'unban');
		}
	break;
	
	case 'tag_rem':
		$go = $_SERVER['HTTP_REFERER'];
		if(isset($_GET['id_tag'])) {
			$page->publishtag_rem_add($_GET['id_tag'], 'rem');
		}
	break;

	case 'tag_add':
		$go = $_SERVER['HTTP_REFERER'];
		if(isset($_GET['id_tag'])) {
			$page->publishtag_rem_add($_GET['id_tag'], 'add');
		}
	break;	
	
	
	//------------------------------- groups -----------------------------------------
	case 'add_group':
		$res = $page->add_group( $_POST['title'], $_POST['type'], $_POST['descr'], $_FILES['group_im'] );
		
		if($res == 1) {
			$go = 'index.php?go=groups';
			unset($_SESSION['descr']);
		} else {
			$error = $res;
			$go = 'index.php?go=add_group&error='.$error.'&title='.$_POST['title'].'&type='.$_POST['type'];
			$_SESSION['descr'] = $_POST['descr'];
		}
	break;
	
	case 'ed_group':
		$res = $page->edit_group( $_POST['id_group'], $_POST['title'], $_POST['descr'], $_FILES['group_im']);
		
		if($res == 1) {
			$go = 'index.php?go=group&id_group='.$_POST['id_group'];
		} else {
			$go = 'index.php?go=edit_group&id_group='.$_POST['id_group']."&error=".$res;
		}
	break;
	
	case 'add_discuss':
		$res = $page->add_discuss($_POST['posttitle'], $_POST['imradio'], $_POST['posttext'], $_POST['id_group'], $_POST['id_discuss']);
		if($res == 1) {
			unset($_SESSION['posttext']);
			//do smth
			if( isset($_POST['id_discuss']) ) 
				$go = 'index.php?go=discuss&id_discuss='.$_POST['id_discuss'];	
			else  
				$go = 'index.php?go=group&id_group='.$_POST['id_group'];
		} else {
			if( !isset($_POST['id_discuss']) ) {
				$go = 'index.php?go=add_discuss&id_group='.$_POST['id_group']."&error=".$res."&posttitle=".$_POST['posttitle'].
					  "&imradio=".$_POST['imradio'];
				$_SESSION['posttext'] = $_POST['posttext'];
			} else {
				$go = 'index.php?go=add_post&id_discuss='.$_POST['id_discuss'].'&id_group='.$_POST['id_group']."&error=".$res."&imradio=".$_POST['imradio'];
				$_SESSION['posttext'] = $_POST['posttext'];
			}
		}
		
	break;
		
	case 'del_discuss':
		$res = $page->del_discuss($_POST['disc']);
		$go = 'index.php?go=group&id_group='.$_POST['id_group'];
		if( $res != 1 )
			$go .= '&error='.$res;		
	break;
	
	case 'del_post':
		$id_discuss = $page->del_post($_GET['id_post']);		
		$go = 'index.php?go=discuss&id_discuss='.$id_discuss;

	break;
	
	case 'del_comment':
		if( isset($_GET['id_dis_comment']) ) {
			$id_discuss = $page->del_dis_comment($_GET['id_dis_comment']);
			$go = 'index.php?go=discuss&id_discuss='.$id_discuss;
		} elseif ( isset($_GET['id_comment']) ) {
			$page->del_comment($_GET['id_comment']);
			$go = $_SERVER['HTTP_REFERER'];
		}
		
	break;
	
	case 'ban_user':
		$page->ban_unban_user($_GET['id_user'], $_GET['id_group'], 'ban');
		$go = 'index.php?go=members&id_group='.$_GET['id_group'];
	break;
	
	case 'unban_user':
		$page->ban_unban_user($_GET['id_user'], $_GET['id_group'], 'unban');
		$go = 'index.php?go=members&id_group='.$_GET['id_group'];		
	break;
	
	case 'grnt_md_rght':
		$page->grant_moderator($_GET['id_user'], $_GET['id_group'], 'grant');
		$go = $_SERVER['HTTP_REFERER'];
	break;
	
	case 'ungrnt_md_rght':
		$page->grant_moderator($_GET['id_user'], $_GET['id_group'], 'ungrant');
		$go = $_SERVER['HTTP_REFERER'];
	break;	
	//----------------------------------------
}

header('location:'.$go);
    
   //\\						                                     //\\												
  //||\\            End of the form hanlder 		      //||\\	
 //=||=\\--------------------------------------------//=||=\\
//>>><<<\\..........................................//>>><<<\\
?>
