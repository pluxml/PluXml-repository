<?php

/**
 * Plugin plxMyContact
 * @author	Stephane F, Jean-Pierre Pourrez @bazooka07
 **/
class plxMyContact extends plxPlugin {
	const CODE_BEGIN = '<?php /* plugin ' . __CLASS__ . '*/' . PHP_EOL;
	const CODE_END = PHP_EOL . '?>';
	const HOOKS_BACK = array(
		'AdminTopEndHead',
		'AdminTopBottom',
	);
	const HOOKS_FRONT = array(
		'plxMotorPreChauffageBegin',
		'plxMotorDemarrageBegin',
		'plxShowConstruct',
		'plxShowStaticListEnd',
		'plxShowPageTitle',
		'SitemapStatics',
	);

	private $url = ''; # parametre de l'url pour accèder à la page de contact
	public $lang = '';

	/**
	 * Constructeur de la classe
	 *
	 * @param	default_lang	langue par défaut
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# gestion du multilingue plxMyMultiLingue
		if(preg_match('#([a-z]{2})/(.*)#i', plxUtils::getGets(), $capture)) {
				$this->lang = $capture[1] . '/';
		}

		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		$this->url = $this->getParam('url');
		if(empty($this->url)) {
			$this->url = 'contact';
		}

		# droits pour accèder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);

		# déclaration des hooks
		if(defined('PLX_ADMIN')) {
			foreach(self::HOOKS_BACK as $hook) {
				$this->addHook($hook, $hook);
			}
		}

		# Si le fichier de langue existe on peut mettre en place la partie visiteur
		if(file_exists(PLX_PLUGINS . __CLASS__ . '/lang/' . $default_lang . '.php')) {
			if(plxUtils::checkMail($this->getParam('email'))) {
				foreach(self::HOOKS_FRONT as $hook) {
					$this->addHook($hook, $hook);
				}
			}
		}
	}

	/**
	 * Méthode qui charge le code css nécessaire à la gestion de onglet dans l'écran de configuration du plugin
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopEndHead() {
		echo self::CODE_BEGIN;
?>
if(isset($plxPlugin) and $plugin == '<?= __CLASS__ ?>') {
	echo '<link href="<?= PLX_PLUGINS . __CLASS__ ?>/tabs/style.css" rel="stylesheet" type="text/css" />';
}
<?php
		echo self::CODE_END;
	}

	/**
	 * Méthode qui affiche un message si l'adresse email du contact n'est pas renseignée
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopBottom() {

		echo self::CODE_BEGIN;
?>
if($plxAdmin->plxPlugins->aPlugins["plxMyContact"]->getParam("email") == "") {
	echo '<p class="warning">Plugin MyContact<br /><?= $this->getLang('L_ERR_EMAIL') ?></p>';
	plxMsg::Display();
}

$lang = $plxAdmin->aConf['default_lang'];
$file = PLX_PLUGINS . '<?= __CLASS__ ?>/lang/' . $lang . '.php';
if(!file_exists($file)) {
	echo '<p class="warning">Plugin MyContact<br />' . sprintf('<?= $this->getLang('L_LANG_UNAVAILABLE') ?>' , $lang) .'</p>';
	plxMsg::Display();
}
<?php
		echo self::CODE_END;

	}

	/**
	 * Méthode de traitement du hook plxShowConstruct
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowConstruct() {

		# infos sur la page statique
		echo self::CODE_BEGIN;
?>
if($this->plxMotor->mode == '<?= $this->url ?>') {
	$this->plxMotor->aStats[$this->plxMotor->cible] = array(
		'name'		=> addslashes('<?= $this->getParam('mnuName_' . $this->default_lang) ?>'),
		'menu'		=> '',
		'url'		=> 'contact',
		'readable'	=> 1,
		'active'	=> 1,
		'group'		=> '',
	);
}
<?php
		echo self::CODE_END;
	}

	/**
	 * Méthode de traitement du hook plxMotorPreChauffageBegin
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxMotorPreChauffageBegin() {

		$template = $this->getParam('template');
		echo self::CODE_BEGIN;
?>
if($this->get && preg_match('#^<?= $this->url ?>/?#', $this->get)) {
	$this->mode = '<?= $this->url ?>';
	$prefix = str_repeat('../', substr_count(trim(PLX_ROOT . $this->aConf['racine_statiques'], '/'), '/'));
	$this->cible = $prefix . 'plugins/plxMyContact/form';
	$this->template = '<?= !empty($template) ? $template : 'static.php' ?>';
	return true;
}
<?php
		echo self::CODE_END;
	}

	public function plxMotorDemarrageBegin() {
		echo self::CODE_BEGIN;
?>
return ($this->mode == '<?= $this->url ?>');
<?php
		echo self::CODE_END;
	}

	/**
	 * Méthode de traitement du hook plxShowStaticListEnd
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowStaticListEnd() {

		if($this->getParam('mnuDisplay')) {
		# ajout du menu pour accèder à la page de contact
			$url = $this->lang . $this->url;
			$title = $this->getParam('mnuName_' . $this->default_lang);
			$caption = addslashes($this->getParam('mnuName_' . $this->default_lang));
			echo self::CODE_BEGIN;
?>
$menu = strtr($format, [
	'#static_id'	=> 'static-contact',
	'#static_status'=> ($this->plxMotor->mode == '<?= $this->url ?>') ? 'active' : 'noactive',
	'#static_url'	=> $this->plxMotor->urlRewrite('?<?= $url ?>'),
	'#static_name'	=> '<?= $caption ?>',
	'#static_class'	=> 'static contact',
]);
array_splice(
	$menus,
	<?= intval($this->getParam('mnuPos') - 1) ?>,
	0,
	array($menu)
);
<?php
			echo self::CODE_END;
		}
	}

	/**
	 * Méthode qui rensigne le titre de la page dans la balise html <title>
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowPageTitle() {

		echo self::CODE_BEGIN;
		$title = $this->getLang('L_PAGE_TITLE');
?>
if($this->plxMotor->mode == '<?= $this->url ?>') {
	echo plxUtils::strCheck('<?= $title ?>');
	return true;
}
<?php
		echo self::CODE_END;
	}

	/**
	 * Méthode qui référence la page de contact dans le sitemap
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function SitemapStatics() {
		$loc = $this->lang . $this->url;
?>
	<url><!-- plugin <?= __CLASS__ ?> -->
		<loc><?php echo '<?= '; ?>$plxMotor->urlRewrite('?<?= $loc ?>')<?php echo ' ?>' ?></loc>
		<changefreq>monthly</changefreq>
		<priority>0.8</priority>
	</url>
<?php
	}

}
