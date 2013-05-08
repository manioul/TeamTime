<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 15:18:23
         compiled from "/var/www/TeamTime/themes/anis/templates/lastErrorMessage.tpl" */ ?>
<?php /*%%SmartyHeaderCode:15348769955188ff1f33f693-68603485%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3af7bc2e62ff1698e2323fb10bd49fa5dfad46a4' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/lastErrorMessage.tpl',
      1 => 1367932698,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '15348769955188ff1f33f693-68603485',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'lastErrorMessage' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188ff1f3444b0_06501643',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188ff1f3444b0_06501643')) {function content_5188ff1f3444b0_06501643($_smarty_tpl) {?>
<div id="lastErrorMsg">Dernier message d'erreur : <span class="errorCode"><?php echo $_smarty_tpl->tpl_vars['lastErrorMessage']->value;?>
</span></div>
<?php }} ?>