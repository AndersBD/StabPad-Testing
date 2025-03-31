<?php

function debug_server()
{

$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

?><pre><?
echo 'Max Upload: '.$max_upload;
?><br /><?
echo 'Max Post: '.$max_post;
?><br /><?
echo 'Mem Limit: '.$memory_limit;
?><br /><?
echo 'Upload MB: '.$upload_mb;
?></pre><?

}

function resizeImage($filename,$toWidth,$toHeight) {
	$image = new Imagick($filename);
	$image->resizeImage($toWidth,$toHeight,Imagick::FILTER_LANCZOS,1);
	$image->writeImage($filename);
	return true;
}

function resizeImage_old($originalImage,$toWidth,$toHeight){ 
    // Get the original geometry and calculate scales 
    list($width, $height) = getimagesize($originalImage); 
    $xscale=$width/$toWidth; 
    $yscale=$height/$toHeight; 

    if($width<=$toWidth && $height<=$toHeight)
	    return true;

    // Recalculate new size with default ratio 
    if ($yscale>$xscale){ 
        $new_width = round($width * (1/$yscale)); 
        $new_height = round($height * (1/$yscale)); 
    } 
    else { 
        $new_width = round($width * (1/$xscale)); 
        $new_height = round($height * (1/$xscale)); 
    } 
    $imageResized = imagecreatetruecolor($new_width, $new_height); 
    $imageTmp = imagecreatefromjpeg ($originalImage);

//    if(!$imageTmp)  return false;

    imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    $result = imagejpeg($imageResized, $originalImage, 75);
    return true;
} 

function file2base64($file, $toWidth=null, $toHeight=null) {
	$imgtype = array('jpg', 'gif', 'png');

	$filename = $file['tmp_name'];

	$filetype = $file['type'];
	$filetype = str_replace('jpeg', 'jpg', $filetype);
	$filetype = split('/', $filetype);
	$filetype = $filetype[count($filetype)-1];

	if($toWidth || $toHeight)
		resizeImage($filename,$toWidth,$toHeight);

	if (in_array($filetype, $imgtype)){
		$imgbinary = fread(fopen($filename, "r"), filesize($filename));
		//echo '<img src="data:image/' . $filetype . ';base64,' . base64_encode($imgbinary).'" />';
		return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
	}
}



function is_ios()
{

	if(
		strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||
		strstr($_SERVER['HTTP_USER_AGENT'],'iPod') ||
		strstr($_SERVER['HTTP_USER_AGENT'],'iPad')
	) {
		return true;
	}

	return false;
}

function ios_ver()
{
	preg_match('/OS (.+?) like/', $_SERVER['HTTP_USER_AGENT'], $ret);
	$ver=$ret['1'];
	$ver=str_replace('_','.',$ver);
	$ver=intval($ver);
	return $ver;
}

function dob_to_age($birthDate=null)
{
	if(!$birthDate) {
		// age
		$birthDate=array(
			0=>$_REQUEST['dobM'],
			1=>$_REQUEST['dobD'],
			2=>$_REQUEST['dobY'],
		);
	}

	// if passing a string, not array
	if(is_string($birthDate)) {
		$birthDate = split('-',$birthDate);
		if($birthDate[0]>12) { //must be backwards!
			$birthDate=array_reverse($birthDate);
		}
	}


        $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md") ? ((date("Y")-$birthDate[2])-1):(date("Y")-$birthDate[2]));
	
	return $age;
}

function send_pdf($html, $email) {
	global $settings;


	/* use attach class 
	include('attach_mailer_class.php');
 
	$subject=$settings['email_subject']." [".$_REQUEST['name']."]";
	$body = $settings['email_text'];
	$body = nl2br($body);
	$from = 'noreply@stabpad.com';
	$reply_to = $settings['email_from'];
	$name = explode('@', $settings['email_from']);
	$name = $name[1];
	$name = "joel Tron";

	// create pdf
	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$html = $doc->saveHTML();

	// render html2pdf
	require_once("dompdf/dompdf_config.inc.php");
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();

	// generate chunks and name
	$f_name="release_form_";
	$f_name.=preg_replace("/[^A-Za-z0-9 ]/", '_', $_REQUEST['name']);
	$f_name.=".pdf";

	$f_contents = chunk_split(base64_encode($dompdf->output())); 

	$my_mail = new attach_mailer($name, $from, $email, $reply_to, $cc = "", $bcc = "", $subject, $body); 
	$my_mail->attach_attachment_part($f_contents, $f_name);

//	echo 'force fail';

	if(!$my_mail->process_mail()) {
		echo "email didn't send....";
		return false;
	}
	return true;
*/

	// normal sending
	// clean up html
	/*$config = array('indent' => TRUE,
                'output-xhtml' => TRUE,
                'wrap' => 200);

	$tidy = tidy_parse_string($html, $config, 'UTF8');
	$tidy->cleanRepair();
	$html=$tidy;*/

	// use DOMPDF
	require_once("dompdf/dompdf_config.inc.php");
		
	// create pdf
	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$html = $doc->saveHTML();

	// render html2pdf
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();

	// email pdf!
	$f_contents = chunk_split(base64_encode($dompdf->output())); 
        $file_type = 'application/pdf';
	$f_name='release_form.pdf';
	$subject=$settings['email_subject']." [".$_REQUEST['name']."]";



	$to = $settings['email_to'];
	$subject=$settings['email_subject']." [".$_REQUEST['name']."]";
	$hash = md5(date('r', time()));
	$headers = "From: ".$settings['email_from']."\r\nReply-To: ".$settings['email_from'];
	$headers .= "\r\n".'Cc: '.$email;
	$headers .= "\r\nContent-Type: multipart/mixed; boundary=".$hash;

	$attachment = chunk_split(base64_encode($dompdf->output())); 

	$output = "
--".$hash."
Content-Type: multipart/alternative; boundary=".$hash."_alt

--".$hash."_alt
Content-Type: text/plain; charset=ISO-8859-1

".$settings['email_text']."

--".$hash."_alt
Content-Type: text/html; charset=ISO-8859-1

".nl2br($settings['email_text'])."

--".$hash."_alt--
--".$hash."
Content-Type: application/pdf; name=\"".$f_name."\"
Content-Disposition: attachment; filename=\"".$f_name."\"
Content-Transfer-Encoding: base64

".$attachment."
--".$hash."--";
	return  @mail($to, $subject, $output, $headers);
}

function display_label($input, $title=null) {
	global $settings;

	if($input=='dob')
		$settings['require_'.$input]=true;

	if(!$title)
		$title=ucfirst(str_replace('_',' ',$input));

	?><?=$title?>:<?=(isset($settings['require_'.$input])&&$settings['require_'.$input])?'<span class="required">*</span>':''?><?
}

function display_input($input,$skin=null) {
	global $submit, $settings;
	if($submit || $skin=="print") {
		echo '<div class="fake_input">'.$_REQUEST[$input].'</div>';
	} else {
		$type='text';
		// for tablet auto keyobardy stuff
		switch($input) {
			case 'phone':
				$type='tel';
				break;
			case 'email':
				$type=$input;
				break;
		}
		echo'<input id="'.$input.'" name="'.$input.'" type="'.$type.'" autocomplete="off"'.($input=='name' && $settings['autocomplete']?' onkeyup="this.onchange();" onchange="autocomplete_client(this.value);"':'').'/>';
	}
}

function display_date_year($val=null, $name='dobY', $onchange=null)
{
?>
<select class="date_year" size="1" name="<?=$name?>" <?=$onchange?'onchange="'.$onchange.'"':''?>  id="<?=$name?>">
	<option value="-1">-Year-</option>
<?php
$year_start=date('Y')-110;
$year_end=date('Y');//-1;
echo($year_start);
for($i=$year_end; $i>$year_start; $i--)
	echo '<option value="'.$i.'"'.($val==$i?' selected':'').'>'.$i.'</option>';
?>
</select>
<?
}
	
function display_date_day($val=null, $name='dobD', $onchange=null)
{
?>
<select class="date_day" size="1" name="<?=$name?>" <?=$onchange?'onchange="'.$onchange.'"':''?>  id="<?=$name?>">
	<option value="-1">-Day-</option>
<?php
for($i=1; $i<=31; $i++)
	echo '<option value="'.$i.'"'.($val==$i?' selected':'').'>'.$i.'</option>';
?>
</select>
<?
}

function display_date_month($val=null,$name='dobM', $onchange=null)
{
	?><select class="date_month" size="1" name="<?=$name?>" <?=$onchange?'onchange="'.$onchange.'"':''?>  id="<?=$name?>">
	<option value="-1">-Month-</option>
	<?php
	$months=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$count=1;
	foreach($months as $month) {
		echo '<option value="'.$count.'"'.($val==$count?' selected':'').'>'.$month.'</option>';
		$count++;
	}
	?>
	</select>
<?
}

function display_dob()
{
	global $submit, $settings;

	if($submit) {
		echo('<div class="fake_input">');
		echo(date('jS F Y',strtotime($_REQUEST['dobY'].'-'.$_REQUEST['dobM'].'-'.$_REQUEST['dobD'])));
		echo(' ('.$_REQUEST['dobY'].'/'.$_REQUEST['dobM'].'/'.$_REQUEST['dobD'].')');
		echo(' - Age:'.$_REQUEST['dob_age']);
		echo('</div>');
	} else {

		?>
		<input type="hidden" value="" name="dob_age" id="dob_age" />
		<?
		display_date_month($_REQUEST['dobM'],'dobM','age('.$settings['age_limit'].')');
		display_date_day($_REQUEST['dobD'],'dobD','age('.$settings['age_limit'].')');
		display_date_year($_REQUEST['dobY'],'dobY','age('.$settings['age_limit'].')');	
		?><div class="dob_age" id="dob_age_display"></div><?
	}
}

?>
