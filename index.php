<?
$cam_width=600;
$cam_height=450;

/*	Na-na-na-na-nah-na-nah-naaaa StabPad!
 *	
 *	yo@joeltron.com     -     StabPad.com
 *
 */

// put into print mode, which creates a pdf
$skin=null;
if(isset($_GET['print'])) {
	$skin='print';
	ob_start();
}

include_once('functions.inc.php');
include_once('config.inc.php');
include_once('validation.inc.php');

ini_set('memory_limit', '96M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

if(!isset($redirect_url))
	$redirect_url=$_SERVER['REDIRECT_URL'];


?><html lang="en">
  <head>
<?if(isset($base_dir)) {?>
  <base href="<?=$base_dir?>" />
<?}?>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="niceforms.css" />
<link rel="stylesheet" media="all and (orientation:portrait)" href="css/portrait.css">
<link rel="stylesheet" media="all and (orientation:landscape)" href="css/landscape.css">

<link rel="shortcut icon" href="img/favicon.gif" type="image/x-icon" />
<link rel="icon" href="img/favicon.ico">
<link rel="apple-touch-icon" href="img/favicon_big.png" />

<meta name="format-detection" content="telephone=no" />
<meta name="viewport" content="width=700, minimum-scale=1.0, maximum-scale=1.5, user-scalable=0" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="HandheldFriendly" content="true"/>
<meta name="MobileOptimized" content="width" />

<?
	// load skin settings
	if($skin==null) $skin='default';
	if($skin!='print' && !$submit && $settings['skin'] && file_exists('css/'.$settings['skin'].'.css'))
		$skin=$settings['skin'];

	if($settings['skin']=='custom') {
		?><style type="text/css"><?=$settings['skin_custom']?></style><?
	}

?>
<link rel="stylesheet" type="text/css" href="css/<?=$skin?>.css" />

    <title><?=$settings['name']?> Release Form</title>
  </head>
  <body>
<a name="top"></a>
  <div id="redirect_url" style="display: none;"><?=$redirect_url?></div>
<?if(!$submit){?>
	<!-- Signature javascript -->
	<script type="text/javascript" src="js/signature.js"></script>
<?
if(!isset($settings['disable_nice_forms']) && $skin!="print") {
	?><script language="javascript" type="text/javascript" src="niceforms.js"></script><?
}?>
<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<?}?>

<?
if($settings['play_sound'] && $settings['play_sound'] !== 'none') {
?>
<audio id="email_sent_notification">
	<source src="audio/<?=$settings['play_sound']?>.mp3" type="audio/mpeg" />
	<source src="audio/<?=$settings['play_sound']?>.wav" type="audio/wav" />
</audio>
<?
}
?>
<form name="MyForm" id="MyForm" action="" method="post" class="niceform">
<?

	if($settings['title_image'] && $skin != "print") {
		?><img class="title_image" src="<?=$settings['title_image']?>" alt="<?=$settings['name']?> Release Form" /><?
	} else {
		?>
		<fieldset class="action">
			<h1><?=$settings['name']?> Release Form</h1>
		</fieldset>
		<?
	}
	
// artist
if(is_array($settings['artists']) && count($settings['artists'])>0) {
	$artist_input='<div class="label" id="input_artist">Artist:<span class="required">*</span></div>';
	if($submit) {
		if($artist['extra']) $artist['extra']='&nbsp;-&nbsp;'.$artist['extra'];
		$artist_input.='<div class="value">'.$_REQUEST['artist'].$artist['extra'].'</div>';
	} else {
		if($skin=="print") {
			$artist_input.='<div class="value"><div class="fake_input"></div></div>';
		} else {
			$artist_input.='<select onchange="artist_select(this)" size="1" name="artist"><option value="-1"> -- Select -- </option>';
			foreach($settings['artists'] as $artist) {
				$name=$artist;
				if(is_array($name)) $name=$name['title'];
				if($artist['extra']) $artist['extra']='&nbsp;-&nbsp;'.$artist['extra'];
				$artist_input.='<option value="'.$name.'"'.($_REQUEST['artist']==$name?' selected':'').'>'.$name.($artist['extra']).'</option>';
			}
			if($settings['allow_other_artist'])
				$artist_input.='<option value="!other">Other</option>';

			$artist_input.='</select>';
		}
	}
	$artist_input.='</div><div class="break"></div>';
}

// top header
?><fieldset id="top">
	<div class="legend">Let us do this part:</div>
<?

//date
$date=date("D M j Y h:i:s");
?><div class="label"><?=display_label('Today\'s Date')?></div>
<?if($submit) {
	if($_REQUEST['date_data'])
		$date=$_REQUEST['date'];
	echo($date);
} else {
	if($skin=="print") {
		?><span class="print_yn">&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/</span><?
	} else {?>
		<input type="hidden" name="date" id="date_data" />
		<div class="value"><span id="todays_date"><?=$date?></span></div>
<?
	}
}
?><div class="break"></div><?

echo $artist_input;

// fields
if(count($settings['fields'])>0) {

	$count=0;
	if(is_array($settings['fields']))
	foreach($settings['fields'] as $field) {
		if(is_array($field)) {
			$array=$field;
			$field=$array['title'];
			$required=$array['required'];
		}
?>
		<div id="input_field_<?=$count?>" class="label"><?=$field?>:<?=$required?'<span class="required">*</span>':''?></div>
		<div class="value">
			<?if($submit || $skin=="print") {?>
				<div class="fake_input"><?=$_POST['fields'][$count]?></div>
			<?} else {
				if($skin=="print") {
					?><div class="fake_input"></div><?
				} else {?>
				<input type="text" name="fields[<?=$count?>]" value="" />
				<?}
			}?>
		</div>

		<div class="break"></div>
	<?
	$count++;
	}
}

if($settings['artist_signature']) {
	if(!$submit){
		if($skin=="print")
			echo '<div class="fake_signature"></div>';
		else {

		?>
		<div id="input_artist_signature" class="label"><?=display_label('artist signature')?><span class="required">*</span></div>
		<br />
		<span class="clear_signature_container"><input class="clear_signature" type="button" value="Clear" onclick="clearCanvas('signature_artist');" /></span>
		<input type="hidden" name="signature_artist_data" id="signature_artist_data" />
		<input type="hidden" name="signature_artist_status" id="signature_artist_status" value="" />
		<canvas class="signature" id="signature_artist" name="signature_artist"></canvas>
		<?
		}
	} else {
		?><img src="<?=$_POST['signature_artist_data']?>"><?
	}
}

if($settings['artist_lock']) {
	?><div class="lock"><input type="button" onclick="lock_down('top');" value="Lock this section" /></div><?
}

?>
</fieldset>
<?

// header
if(strlen($settings['header'])) {
	?><fieldset class="action">
	<?=$settings['header'];?>
	</fieldset><?
}


if(count($settings['provisions'])>0 || strlen($settings['provisions_foot'])>0) {
	
?>
	<fieldset>
		<div class="legend">Please read &amp; answer:</div>
<?	
$count=0;
if(is_array($settings['provisions']))
foreach($settings['provisions'] as $provision) {
	?><div id="input_provision_<?=$count?>"></div><?
	switch($provision['type']) {
		case 'yn_details':
		case 'yn':
			if($skin=="print") {
				?><span class="print_yn">Y&nbsp;/&nbsp;N</span><?
			} else
			if($submit) {
				?>
				<img src="img/yn_y_<?=$_POST['provisions'][$count][0]=='y'?'on':'off'?>.png" />
				&nbsp;
				<img src="img/yn_n_<?=$_POST['provisions'][$count][0]=='n'?'on':'off'?>.png" />
				<?
			} else {
				?>
				<img id="yn_y_<?=$count?>" src="img/yn_y_off.png" onclick="yn_select(<?=$count?>, 'y');" />
				<img id="yn_n_<?=$count?>" src="img/yn_n_off.png" onclick="yn_select(<?=$count?>, 'n');" />
				<input type="hidden" id="provisions[<?=$count?>][0]" name="provisions[<?=$count?>][0]" value="" />
				<?
			}
			break;
		case 'checklist':
		case 'dropdown':
		case 'text':
			?><div style="width:50px; float: left;">&nbsp;</div><?
			break;
		case 'checkbox':
		default :
			if($skin=="print") {
				?><div class="fake_checkbox"></div><?
			} else
			if($submit) {
				if(isset($_REQUEST['provisions'][$count]))
					echo('<img src="img/checked_box.png" />');	
				else
					echo('<img src="img/unchecked_box.png" />');	
			} else {
				?><span class="checkbox_container"><input type="checkbox" class="bigbox" name="provisions[<?=$count?>]" /></span><?
			}
			break;
		case 'note':
		case 'dropdown':
			break;
	}

			?>
			<span class="provision_title"><?=$provision['title']?><?if($provision['required']) echo('<span class="required">*</span>');?></span>
<div class="clear_line"></div><?

if($skin=="print" && $provision['type'] == 'dropdown')
	$provision['type']='text';


switch($provision['type']) {
	case 'checklist':
		$cl_count=0;
		foreach(explode("\n",$provision['text']) as $prov) {
			?><span class="checklist"><?
				if($skin=="print")
					echo '<div class="fake_checkbox"></div>';
				else 

				if(!$submit) {
					?><input type="checkbox" name="provisions[<?=$count?>][<?=$cl_count?>]" /><?
				} else {
					if(isset($_POST['provisions'][$count][$cl_count])) {
						echo('<img src="img/checked_box.png" style="margin-right: 20px;"/>');	
					} else {
						echo('<img src="img/unchecked_box.png" style="margin-right: 20px;" />');	
					}
				}
				?><span><?=$prov?></span><?
			?></span><?
			$cl_count++;
		}
		?><div class="clear_line"></div><?
		break;
	case 'dropdown':
		$options = explode("\n",$provision['text']);

		if($submit) {
			echo '<div class="fake_input">'.$options[$_POST['provisions'][$count]].'</div>';
		} else {
			?><select name="provisions[<?=$count?>]">
			<option value="-1">-- Select --</option><?
			$ob_count=0;
			foreach($options as $prov) {
				echo '<option value="'.$ob_count.'">'.$prov.'</option>';
				$ob_count++;
			}
			?></select><br /><?
		}
		break;
	case 'yn_details':
		?><div class="provision_text"><?
		?><div class="label">Details:</div><?
		if($submit)
			echo '<div class="fake_input">'.$_POST['provisions'][$count][1].'</div>';
		else {
			?><input name="provisions[<?=$count?>][1]" type="text" />&nbsp;<?
		}
		?></div><?
		break;
	case 'text':	
		echo nl2br($provision['text']);
		?><div class="provision_text"><?
		if($submit || $skin=="print") {
			echo '<div class="fake_input">'.$_POST['provisions'][$count].'</div>';
		} else {
			?><input type="text" name="provisions[<?=$count?>]" value="" /><?
		}
		?>&nbsp;</div><?
		break;
	case 'note':
		echo '<div class="note">'.nl2br($provision['text']).'</div>';
		break;
	default:
		echo nl2br($provision['text']);
		break;
}
?>
<hr class="provision_break" />
<?php
	$count++;
}
if(strlen($settings['provisions_foot'])>0)
	echo(nl2br($settings['provisions_foot']).'<hr />');
?>
If any provision, section, subsection, clause or phrase of this release is found to be unenforceable or invalid, that portion shall be severed from this contract. The remainder of this contract will then be construed as though the unenforceable portion had never been contained in this document.
	</fieldset>

<?}?>

	<fieldset>
		<!--<div class="sub_legend"><a href="javascript: force_guardian();">display guardian</a></div>-->
		<div class="legend">Personal Info</div>
I hereby declare that I am of legal age (with valid proof of age) and am competent to sign this Agreement or, if not, that my parent or legal guardian shall sign on my behalf, and that my parent or legal guardian is in complete understanding and concurrence with this agreement.
		<div class="break"></div>
		<div id="input_name" class="label"><?=display_label('name')?></div>
		<div class="value"><?=display_input('name',$skin)?></div>
		<div class="break"></div>
			
		<div id="input_address" class="label"><?=display_label('address')?></div>
		<div class="value"><?=display_input('address',$skin)?></div>
		<div class="break"></div>

		<div id="input_dob" class="label"><?=display_label('dob','Date of birth')?></div>
		
			<?if($skin=="print") {
				?><div class="value"><span class="print_yn">&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/</span><?
			} else {?>
				<div class="value"><?=display_dob()?>
			<?
			}
			if($settings['age_limit'] > 0)
				echo '<div id="age_note" class="under_age no_label">If you are under <b>'.$settings['age_limit'].'</b> your parent/guardian will also need to fill out the guardian section.</div>';
			else if ($settings['age_limit'] < 0)
				echo '<div id="age_note" class="under_age no_label">You must be '.-$settings['age_limit'].' or older to have this procedure done.';
?>
		</div>
		<div class="break"></div>

		<div id="input_phone" class="label"><?=display_label('phone','Phone #')?></div>
		<div class="value"><?=display_input('phone',$skin)?></div>
		<div class="break"></div>

		<div id="input_email" class="label"><?=display_label('email')?></div>
		<div class="value"><?=display_input('email',$skin)?></div>

			<?
				if($settings['enable_newsletter'] && $skin !='print') {
				?><div class="no_label" id="input_newsletter" style="clear: both;"><?
				if(!$submit){?>
					<input class="newsletter_check" type="checkbox" name="newsletter"<?=($settings['newsletter_checked']?' checked="checked"':'')?> />
				<?} else {
					if(isset($_REQUEST['newsletter']) && $_REQUEST['newsletter']) {
						echo('<img src="img/checked_box.png" />');	
					} else {
						echo('<img src="img/unchecked_box.png" />');	
					}
				}?>
				<span style="position: relative; bottom: 8px;">Sign up for our newsletter.</span>
				</div>
			<?}?>
			
			<div class="break"></div>

			<div id="input_signature" class="label"><?=display_label('signature')?><span class="required">*</span></div>
			<br />
			<?if(!$submit){?>
			<span class="clear_signature_container"><input class="clear_signature" type="button" value="Clear" onclick="clearCanvas('signature_client')" /></span>
		<?}?>
				<?if(!$submit){
					if($skin=="print")
						echo '<div class="fake_signature"></div>';
					else {

?>
					<input type="hidden" name="signature_client_data" id="signature_client_data" />
					<input type="hidden" name="signature_client_status" id="signature_client_status" value="" />
					<canvas class="signature" id="signature_client" name="signature_client"></canvas>
					<?}
				} else {
					?><img src="<?=$_POST['signature_client_data']?>"><?
				}?>
	</fieldset>

	<input type="hidden" name="require_guardian" id="require_guardian" value="0" />

<?if((!$submit && $settings['age_limit']) || ($skin=="print") ||  dob_to_age()<$settings['age_limit']){?>
	<fieldset <?=!$submit?' style="display: none;"':''?> id="parent">
		<div class="legend">Parent/Legal Guardian</div>
By the parental/guardian signature they, on my behalf, release all claims that both they and I have.<br />
		<?=$settings['parent_header']?>
		<hr />	
		<div id="input_parent_name" class="label"><?=display_label('parent_name')?><span class="required">*</span></div>
		<div class="value"><?=display_input('parent_name',$skin)?></div>
		<div class="break"></div>
					
		<div id="input_parent_signature" class="label"><?=display_label('signature')?><span class="required">*</span></div>
					<br />
					<?if(!$submit){?>
					<span class="clear_signature_container"><input class="clear_signature" type="button" value="Clear" onclick="clearCanvas('signature_parent')" /></span>
					<?}?>
				<?if(!$submit){
					if($skin=="print")
						echo '<div class="fake_signature"></div>';
					else {
	

?>
					<input type="hidden" name="signature_parent_data" id="signature_parent_data" />
					<input type="hidden" name="signature_parent_status" id="signature_parent_status" value="" />
					<canvas class="signature" id="signature_parent" name="signature_parent"></canvas>
				<?
					}
				} else {
					?><img class="signature" src="<?=$_POST['signature_parent_data']?>"><?
				}?>
	</fieldset>
<?}?>

<?if($settings['enable_camera'] && $skin != "print") {?>
	<fieldset id="camera_container">
		<div id="input_photo" class="legend">Photo ID(s)<?=$settings['photo_required']?'<span class="required">*</span>':''?></div>

		<?if($submit) {
			if($_POST['photo_data']) {
				// here we go! Crazy ass url->blob->img->resize->blob->url time!
				$img = new Imagick();
				$url_data=$_POST['photo_data'];
				$pre=substr($url_data,0,strpos($url_data,',')+1);
				$url_data=substr($url_data,strpos($url_data,',')+1);
				$decoded=$url_data;
				$decoded=base64_decode($url_data);
				$img->readimageblob($decoded);
				$img->resizeImage($cam_width,$cam_height,Imagick::FILTER_LANCZOS,1);
				$blob = $img->getImageBlob();
				$url_data=$pre.base64_encode($blob);
				
				?><img class="photo" src="<?=$url_data?>" /><?
			}
		} else {

			// check browser/os support. Fuck you apple.
			if(is_ios())
				$camera = 'os';
			else
				$camera = 'live';
	
			//$camera = 'os';
			if(is_ios() && ios_ver()<6) {
				$camera_status = true;	// force it to go
				echo'This feature is only available for iOS 6+. <a href="http://support.apple.com/kb/HT4623">Consider upgrading</a>';
			}

			switch($settings['camera_mode']) {
				case 'live':
				case 'photo':
				case 'os':
					$camera = $settings['camera_mode'];
			}
			// keep 'legacy' option here, but if hidden if live view is selected
			?>
			<input type="hidden" name="photo_data" id="photo_data" />
			<input type="hidden" name="photo_status" id="photo_status" value="<?=$camera_status?>" />

			<div id="uploadcontainer"<?=$camera!='os'?' style="display: none;"':''?>>
				<div class="please_take_photo">
				<b>Please take a picture of your government issued photo ID</b>
				<br />
				<input onchange="if(os_camera_upload()) canvas_status('photo',1) ;" onfocus="this.onclick();" type="file" accept="image/*;capture=camera" id="os_photo" name="os_photo">
				<a id="os_photo_msg" href="#" onclick="document.getElementById('os_photo').click();return false;"><img src="img/take_photo.png" /></a>
				</div>
			</div>
				
			<?
			// enable live view camera
			if($camera=='live' || $camera=='photo') {
				?>
				<div id="cameracontainer">
					<b style="margin-bottom: 10px; display: block;">Please take a picture of your government issued photo ID</b>
					<div class="snapbutton"><input type=button value="CLICK TO TAKE PHOTO" onclick="snapshot()" id="snapbutton"></div>
					<video class="photo" id="monitor" autoplay></video>
					<canvas class="photo" name="photo" id="photo"></canvas>

					<div id="take_photo" class="take_photo" onclick="snapshot()"></div>		
				</div>
				<script type="text/javascript" src="js/camera.js"></script>
				<script language="javascript">
					// see if we can use the camera or return error.
					camera_init('<?=$camera?>');
				</script>
				<?
			}
		}
	?></fieldset><?
}

	if(!$submit) {?>
		<fieldset class="action" id="submit">
			<span class="passcode" id="enter_passcode">
				<?if($settings['enable_passcode']) {?>
					Enter passcode to submit:
					<?for($a=0;$a<5;$a++) {
					?><span><input onClick="if(enterpasscode(this.value)) sendpdf();" type="button" value="<?=$a?>"></span><?	
					}
				}?>
			</span>	

			<input type="button" name="complete" id="complete" value="I have completed this form" onClick="hide(); setTimeout('if(submit_form()){<?=$settings['enable_passcode']?'display_passcode();':' sendpdf();'?>}',10);" />
		</fieldset>
	<?}?>
</form>

<div class="container" id="container"></div>
<!--<div class="containerClose" id="containerClose">x</div>-->

<div id="ResponseDiv"><!-- onclick="popup();">-->
	<div id="close_link" onclick="popup();"><img id="close_button" class="close" src="img/close.png" /></div>
	<div id="Response"></div>
</div>

<?if(!$submit){?>
	<script language="javascript">draw_init();</script>
<?}?>

<script language="javascript">

var inputs = document.getElementsByTagName('input');
for(a=0; a<inputs.length; a++)
{
/*	inputs[a].addEventListener('touchstart',	function (event) { event.preventDefault(); }, false);
	inputs[a].addEventListener('touchmove',		function (event) { event.preventDefault(); }, false);
inputs[a].addEventListener('touchend',		function (event) { event.preventDefault(); }, false);*/
}
</script>

  </body>
</html>

<?
if($submit && !isset($_GET['debug'])) {
	// grab entire html
	$html=ob_get_contents();
	ob_end_clean();

	$client = array();

	// post to client info	
	$client['email'] 	= $_POST['email'];
	$client['name'] 	= $_POST['name'];
	$client['address'] 	= $_POST['address'];
	$client['phone'] 	= $_POST['phone'];
	$client['dob']		= $_POST['dobY'].'-'.$_POST['dobM'].'-'.$_POST['dobD'];
	$client['artist'] 	= $_POST['artist'];
	$client['release'] 	= $settings['name'];

	// news letter
	if($settings['enable_newsletter'])
		if($_POST['newsletter'])
			$client['newsletter']=true;

	// custom fields
	if(is_array($settings['fields'])) {
		for($a=0; $a<count($settings['fields']); $a++) {
			$client['custom_fields'][$a] = array(
				'key'=>$settings['fields'][$a]['title'],
				'value'=>$_POST['fields'][$a],
			);
		}
	}
//
	if(send_pdf($html,$_POST['email'])) {
//echo "force fail";
//		echo '<img src="'.$url_data.'" />';
		echo "sent";
	}
}

if($skin=="print") {
	$html=ob_get_contents();
	ob_end_clean();

if(isset($_GET['debug1'])) {
	echo $html; die;
}

	// use DOMPDF
	require_once("dompdf/dompdf_config.inc.php");
		
	// create pdf
/*	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$html = $doc->saveHTML();*/

	// render html2pdf
	$file_name=$settings['name']." Release Form";
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();
	$dompdf->stream($file_name.".pdf");

}
?>
