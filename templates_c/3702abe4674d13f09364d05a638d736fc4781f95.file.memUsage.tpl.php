<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 15:16:01
         compiled from "/var/www/TeamTime/themes/anis/templates/memUsage.tpl" */ ?>
<?php /*%%SmartyHeaderCode:4631046775188fe9126e628-23109997%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3702abe4674d13f09364d05a638d736fc4781f95' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/memUsage.tpl',
      1 => 1367932556,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4631046775188fe9126e628-23109997',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'memUsage' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188fe9129f7c8_15375232',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188fe9129f7c8_15375232')) {function content_5188fe9129f7c8_15375232($_smarty_tpl) {?>
<div id="memUsage">
Le script utilise <?php echo $_smarty_tpl->tpl_vars['memUsage']->value;?>
.
</div>
<?php }} ?>