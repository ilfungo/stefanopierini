<?php
/**
 * Il file base di configurazione di WordPress.
 *
 * Questo file definisce le seguenti configurazioni: impostazioni MySQL,
 * Prefisso Tabella, Chiavi Segrete, Lingua di WordPress e ABSPATH.
 * E' possibile trovare ultetriori informazioni visitando la pagina: del
 * Codex {@link http://codex.wordpress.org/Editing_wp-config.php
 * Editing wp-config.php}. E' possibile ottenere le impostazioni per
 * MySQL dal proprio fornitore di hosting.
 *
 * Questo file viene utilizzato, durante l'installazione, dallo script
 * di creazione di wp-config.php. Non � necessario utilizzarlo solo via
 * web,� anche possibile copiare questo file in "wp-config.php" e
 * rimepire i valori corretti.
 *
 * @package WordPress
 */

// ** Impostazioni MySQL - E? possibile ottenere questoe informazioni
// ** dal proprio fornitore di hosting ** //
/** Il nome del database di WordPress */
define('DB_NAME', 'stefanop80680');

/** Nome utente del database MySQL */
define('DB_USER', 'stefanop80680');

/** Password del database MySQL */
define('DB_PASSWORD', 'stef56320');

/** Hostname MySQL  */
define('DB_HOST', 'sql.stefanopierini.com');

/** Charset del Database da utilizare nella creazione delle tabelle. */
define('DB_CHARSET', 'utf8');

/** Il tipo di Collazione del Database. Da non modificare se non si ha
idea di cosa sia. */
define('DB_COLLATE', '');

/**#@+
 * Chiavi Univoche di Autenticazione e di Salatura.
 *
 * Modificarle con frasi univoche differenti!
 * E' possibile generare tali chiavi utilizzando {@link https://api.wordpress.org/secret-key/1.1/salt/ servizio di chiavi-segrete di WordPress.org}
 * E' possibile cambiare queste chiavi in qualsiasi momento, per invalidare tuttii cookie esistenti. Ci� forzer� tutti gli utenti ad effettuare nuovamente il login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';T}6KvNMLxe_`GT#|vI0*k>w(YO]iW}[#@Vx5>(_{1sMor2^enm$}o@t_/x*1E~4');
define('SECURE_AUTH_KEY',  '|[ A}#Z}Tf<26pV@v=Ulf3lX6JM1/0RrNC 83jcxn_hoh6Q(Rd~G>kT+<%JOWUm+');
define('LOGGED_IN_KEY',    '+DA_tf8)B,o}aXUw@y9O%f&.g<C9ZzLG3,PJo*ta;W2mv:j D[.IWB%X6^+ez+9J');
define('NONCE_KEY',        'l[I!PIIe2SR,O|f90_Pc&BCB+_ysbxOI*ZPW3JiV-1%.T*IAa%RKyVQ|E`.CNp7.');
define('AUTH_SALT',        '}x(NOOhr3(Z@77C}7u O04$*-i96 APA2GyJx=s+S5Zu`J?#~+&eHg55$#HT(aRD');
define('SECURE_AUTH_SALT', '8,W-b[ikI;J=&mj]O|3%+Wm;k=J)eE&t+%Cxc(?8DDxE#XO<)?dC.W?5wxF7pX4@');
define('LOGGED_IN_SALT',   'n I+9qMYQ>c[g*2JkK+=bx1p{*5V@L6<yDr#Of-5&Cg=EWgULlbKg!tA,4|G#Ud7');
define('NONCE_SALT',       'fSqJsLM$=@tCulRG`C*~=Ay PO7$+~mjQ,{_EWw&{[M-RPeB>&(wA&A~_oYL!fXR');

/**#@-*/

/**
 * Prefisso Tabella del Database WordPress .
 *
 * E' possibile avere installazioni multiple su di un unico database if you give each a unique
 * fornendo a ciascuna installazione un prefisso univoco.
 * Solo numeri, lettere e sottolineatura!
 */
$table_prefix  = 'wp_';

/**
 * Lingua di Localizzazione di WordPress, di base Inglese.
 *
 * Modificare questa voce per localizzare WordPress. Occorre che nella cartella
 * wp-content/languages sia installato un file MO corrispondente alla lingua
 * selezionata. Ad esempio, installare de_DE.mo in to wp-content/languages ed
 * impostare WPLANG a 'de_DE' per abilitare il supporto alla lingua tedesca.
 *
 * Tale valore � gi� impostato per la lingua italiana
 */
define('WPLANG', 'it_IT');

/**
 * Per gli sviluppatori: modalit� di debug di WordPress.
 *
 * Modificare questa voce a TRUE per abilitare la visualizzazione degli avvisi
 * durante lo sviluppo.
 * E' fortemente raccomandato agli svilupaptori di temi e plugin di utilizare
 * WP_DEBUG all'interno dei loro ambienti di sviluppo.
 */
define('WP_DEBUG', false);

/* Finito, interrompere le modifiche! Buon blogging. */

/** Path assoluto alla directory di WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Imposta lle variabili di WordPress ed include i file. */
require_once(ABSPATH . 'wp-settings.php');
