<?php
if (IN_serendipity !== true) {
  die ("Don't hack!");
}

@serendipity_plugin_api::load_language(dirname(__FILE__));

$serendipity['smarty']->assign(array('currpage'  => "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
                                     'currpage2' => $_SERVER['REQUEST_URI']));

if (class_exists('serendipity_event_spamblock')) {
    $required_fieldlist = serendipity_db_query("SELECT value FROM {$serendipity['dbPrefix']}config WHERE name LIKE '%spamblock%required_fields'", true, 'assoc');
} elseif (class_exists('serendipity_event_commentspice')) {
    $required_fieldlist = serendipity_db_query("SELECT value FROM {$serendipity['dbPrefix']}config WHERE name LIKE '%commentspice%required_fields'", true, 'assoc');
}

$serendipity['smarty']->assign('archiveURL', serendipity_rewriteURL(PATH_ARCHIVE));

$serendipity['smarty']->assign(array(
	'BOOTSTRAP_PATH'  => serendipity_getTemplateFile('bootstrap-5.3.2-dist', 'serendipityHTTPPath', true),
	'currpage'  => "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
	'currpage2' => $_SERVER['REQUEST_URI']
));

$serendipity['smarty']->assign('is_templatechooser', $_SESSION['serendipityUseTemplate'] ?? null);

$template_config = array(
    array(
        'var'           => 'date_format',
        'name'          => GENERAL_PLUGIN_DATEFORMAT . " (http://php.net/strftime)",
        'type'          => 'select',
        'default'       => DATE_FORMAT_ENTRY,
        'select_values' => array(DATE_FORMAT_ENTRY => DATE_FORMAT_ENTRY,
                                 '%a, %e. %B %Y' => '%a, %e. %B %Y',
                                 '%d-%m-%y' => '%d-%m-%y',
                                 '%m-%d-%y' => '%m-%d-%y',
                                 '%a %d-%m-%y' => '%a %d-%m-%y',
                                 '%a %m-%d-%y' => '%a %m-%d-%y',
                                 '%b %d' => '%b %d',
                                 "%b %d '%y" => "%b %d '%y")
    ),
    array(
        'var' => 'use_corenav',
        'name' => BROWNPAPER_USE_CORENAV,
        'type' => 'boolean',
        'default' => true
    ),
    array(
        'var' => 'webfonts',
        'name' => BROWNPAPER_WEBFONTS,
        'type' => 'select',
        'default' => 'none',
    ),
);

// Collapse template options into groups.
$template_global_config = array('navigation' => true);
$template_loaded_config = serendipity_loadThemeOptions($template_config,
	$serendipity['smarty_vars']['template_option'] ?? '',
	true);
serendipity_loadGlobalThemeOptions($template_config, $template_loaded_config, $template_global_config);

function serendipity_plugin_api_pre_event_hook_js($event, &$bag, &$eventData, &$addData) {
    // always add newlines to the end of last element, in case of other plugins using this hook and
    // always start at line Col 1, to populate the (virtual) serendipity.js file
    echo "
jQuery(function() {
    jQuery('input[type=\"url\"]').change(function() {
        if (this.value != '' && ! (this.value.substr(0,7) == 'http://' || this.value.substr(0,8) == 'https://')) {
            this.value = 'http://' + this.value;
        }
    });
})\n\n";
}

function serendipity_plugin_api_pre_event_hook_css($event, &$bag, &$eventData, &$addData) {
    global $serendipity;
    if (isset($serendipity['smarty']) &&
        isset($serendipity['smarty']->tpl_vars['template_option']) &&
        isset($serendipity['smarty']->tpl_vars['template_option']->value['webfonts'])) {
	}
}

if ($_SESSION['serendipityUseTemplate'] ?? false) {
    $template_loaded_config['use_corenav'] = false;
}
