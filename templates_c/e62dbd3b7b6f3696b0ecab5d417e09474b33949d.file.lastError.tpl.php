<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 15:18:23
         compiled from "/var/www/TeamTime/themes/anis/templates/lastError.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8768607225188ff1f305e95-51838850%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e62dbd3b7b6f3696b0ecab5d417e09474b33949d' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/lastError.tpl',
      1 => 1367932698,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8768607225188ff1f305e95-51838850',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'lastError' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188ff1f33c200_13177964',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188ff1f33c200_13177964')) {function content_5188ff1f33c200_13177964($_smarty_tpl) {?>
<div id="lastError">Dernier code d'erreur : <span class="errorCode"><?php echo $_smarty_tpl->tpl_vars['lastError']->value;?>
</span></div>
<?php }} ?>