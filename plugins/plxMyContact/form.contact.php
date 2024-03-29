<?php
if(!defined('PLX_ROOT')) {
	exit;
}

# récupération d'une instance de plxShow
$plxShow = plxShow::getInstance();
$plxShow->plxMotor->plxCapcha = new plxCapcha();
$plxPlugin = $plxShow->plxMotor->plxPlugins->getInstance('plxMyContact');

# Si le fichier de langue n'existe pas
$lang = $plxShow->plxMotor->aConf['default_lang'];
$filename = PLX_PLUGINS . 'plxMyContact/lang/' . $lang . '.php';
if(!file_exists($filename)) {
?>
	<p><?php printf($plxPlugin->getLang('L_LANG_UNAVAILABLE'), $filename); ?></p>
<?php
	return;
}

$error = false;
$success = false;

$captcha = $plxPlugin->getParam('captcha');
if($captcha == '') {
	$captcha = '1';
}

if(!empty($_POST)) {

	$name=plxUtils::unSlash($_POST['name']);
	$mail=plxUtils::unSlash($_POST['mail']);
	$subject = '';
	if($plxPlugin->getParam('append_subject')) {
		$subject = plxUtils::unSlash($_POST['subject']).' ';
	}
	$content=plxUtils::unSlash($_POST['content']);

	# pour compatibilité avec le plugin plxMyCapchaImage
	if(strlen($_SESSION['capcha']) <= 10) {
		$_SESSION['capcha']=sha1($_SESSION['capcha']);
	}

	if(empty(trim($name))) {
		$error = $plxPlugin->getLang('L_ERR_NAME');
	} elseif(!plxUtils::checkMail($mail)) {
		$error = $plxPlugin->getLang('L_ERR_EMAIL');
	} elseif(empty(trim($content))) {
		$error = $plxPlugin->getLang('L_ERR_CONTENT');
	} elseif($captcha != 0 AND $_SESSION['capcha'] != sha1($_POST['rep'])) {
		$error = $plxPlugin->getLang('L_ERR_ANTISPAM');
	}

	if(!$error) {
		if(plxUtils::sendMail($name,$mail,$plxPlugin->getParam('email'), plxUtils::unSlash($plxPlugin->getParam('subject')) . $subject, $content, 'text', $plxPlugin->getParam('email_cc'), $plxPlugin->getParam('email_bcc')))
			$success = $plxPlugin->getParam('thankyou_' . $plxPlugin->default_lang);
		else
			$error = $plxPlugin->getLang('L_ERR_SENDMAIL');
	}
} else {
	$name='';
	$mail='';
	$subject = '';
	$content='';
}

?>

<div id="form_contact">
<?php
if($error) {
?>
	<p class="contact_error"><?= $error ?></p>
<?php
}

if($success) {
?>
	<p class="contact_success"><?= plxUtils::strCheck($success) ?></p>
<?php
} else {

	if($plxPlugin->getParam('mnuText_'.$plxPlugin->default_lang)) {
?>
	<p class="text_contact">
	<?= $plxPlugin->getParam('mnuText_'.$plxPlugin->default_lang) ?>
	</p>
<?php
	}

	$withLabel = !empty($plxPlugin->getParam('label'));
	$withPlaceholder = !empty($plxPlugin->getParam('placeholder'));
?>
	<form action="#form" method="post">
		<fieldset>
		<p>
<?php
	if($withLabel) {
?>
			<label for="name"><?php $plxPlugin->lang('L_FORM_NAME'); ?>&nbsp;:</label>
<?php
	}

	$placeholder = $withPlaceholder ? 'placeholder="' . plxUtils::strCheck($plxPlugin->getLang('L_FORM_NAME')) . '" ' : '';
?>
			<input <?= $placeholder ?>id="name" name="name" type="text" size="30" value="<?= plxUtils::strCheck($name) ?>" maxlength="30" />
		</p>
		<p>
<?php
	if($withLabel) {
?>
			<label for="mail"><?php $plxPlugin->lang('L_FORM_MAIL'); ?>&nbsp;:</label>
<?php
	}

	$placeholder = $withPlaceholder ? 'placeholder="' . plxUtils::strCheck($plxPlugin->getLang('L_FORM_MAIL')) . '" ' : '';
?>
			<input <?= $placeholder ?>id="mail" name="mail" type="text" size="30" value="<?= plxUtils::strCheck($mail) ?>" />
		</p>
<?php
		if($plxPlugin->getParam('append_subject')) {
?>
		<p>
<?php
			if($withLabel) {
?>
			<label for="subject"><?php $plxPlugin->lang('L_FORM_SUBJECT') ?>&nbsp;:</label>
<?php
			}

			$placeholder = $withPlaceholder ? 'placeholder="' . $plxPlugin->getLang('L_FORM_SUBJECT') . '" ' : '';
?>
			<input <?= $placeholder ?>id="subject" name="subject" type="text" size="30" value="<?= plxUtils::strCheck($subject) ?>" maxlength="30" />
		</p>
<?php
		}
?>
		<p>
<?php
		if($withLabel) {
?>
			<label for="message"><?php $plxPlugin->lang('L_FORM_CONTENT') ?>&nbsp;:</label>
<?php
		}

		$placeholder = $withPlaceholder ? 'placeholder="' . plxUtils::strCheck($plxPlugin->getLang('L_FORM_CONTENT')) . '" ' : '';
?>
			<textarea <?= $placeholder ?>id="message" name="content" cols="60" rows="12"><?= plxUtils::strCheck($content) ?></textarea>
		</p>
<?php
		if($captcha) {
 ?>
		<p>
		<label for="id_rep"><strong><?php $plxPlugin->lang('L_FORM_ANTISPAM') ?></strong></label>
		<?php $plxShow->capchaQ(); ?>
		<input id="id_rep" name="rep" type="text" size="2" maxlength="1" style="width: auto; display: inline;" autocomplete="off" />
		</p>
<?php
		}
?>
		<p>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_FORM_BTN_SEND') ?>" />
			<input type="reset" name="reset" value="<?php $plxPlugin->lang('L_FORM_BTN_RESET') ?>" />
		</p>
		</fieldset>
	</form>
<?php
	}
?>
</div>
