<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 11:53:24
         compiled from "/var/www/TeamTime/themes/anis/templates/header.tpl" */ ?>
<?php /*%%SmartyHeaderCode:13134803675188cf14898fe4-52244228%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '04836243e50630c3579a29c7d511ee52de66d484' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/header.tpl',
      1 => 1366901599,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '13134803675188cf14898fe4-52244228',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'titrePage' => 0,
    'language' => 0,
    'stylesheet' => 0,
    'theme' => 0,
    'sheet' => 0,
    'javascript' => 0,
    'js' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188cf148faac6_65555548',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188cf148faac6_65555548')) {function content_5188cf148faac6_65555548($_smarty_tpl) {?>
<?php echo '<?xml';?> version="1.0" encoding="utf-8"<?php echo '?>';?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head profile="http://www.w3.org/2005/10/profile">
	<title><?php if ($_smarty_tpl->tpl_vars['titrePage']->value){?><?php echo $_smarty_tpl->tpl_vars['titrePage']->value;?>
<?php }else{ ?>Grille<?php }?></title>
	<meta http-equiv="Content-Language"    content="<?php echo $_smarty_tpl->tpl_vars['language']->value;?>
" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type"  content="text/css" />
	<meta http-equiv="Content-Type"        content="application/xhtml+xml; charset=utf-8" />
	<link rel="icon" type="image/ico" href="favicon.ico" />
<?php  $_smarty_tpl->tpl_vars['sheet'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['sheet']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['stylesheet']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['sheet']->key => $_smarty_tpl->tpl_vars['sheet']->value){
$_smarty_tpl->tpl_vars['sheet']->_loop = true;
?>
	<link rel="stylesheet" type="text/css" href="themes/<?php echo $_smarty_tpl->tpl_vars['theme']->value;?>
/style/<?php echo strtr($_smarty_tpl->tpl_vars['sheet']->value['href'], array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", "\n" => "\\n", "</" => "<\/" ));?>
" media="<?php echo $_smarty_tpl->tpl_vars['sheet']->value['media'];?>
" />
<?php } ?>
<?php  $_smarty_tpl->tpl_vars['js'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['js']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['javascript']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['js']->key => $_smarty_tpl->tpl_vars['js']->value){
$_smarty_tpl->tpl_vars['js']->_loop = true;
?>
	<script type="text/javascript" src="js/<?php echo strtr($_smarty_tpl->tpl_vars['js']->value, array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", "\n" => "\\n", "</" => "<\/" ));?>
"></script>
<?php } ?>
	<link rel="icon" type="image/png" href="favicon.png" />
</head>
<body>
<div id="contenu">
<?php }} ?>