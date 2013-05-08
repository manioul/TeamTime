<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 14:27:01
         compiled from "/var/www/TeamTime/themes/anis/templates/menu.tpl" */ ?>
<?php /*%%SmartyHeaderCode:5745223685188f315f294e4-69969570%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5891ca1e2bc2ff3f8e23cebfe1024d3be1be9021' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/menu.tpl',
      1 => 1366968361,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '5745223685188f315f294e4-69969570',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'menu' => 0,
    'class' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188f3161ecc74_19512857',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188f3161ecc74_19512857')) {function content_5188f3161ecc74_19512857($_smarty_tpl) {?>

<div id="<?php echo $_smarty_tpl->tpl_vars['menu']->value->titre();?>
">
<?php echo $_smarty_tpl->getSubTemplate ('menuHor.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('arbre'=>$_smarty_tpl->tpl_vars['menu']->value->arbre(),'class'=>$_smarty_tpl->tpl_vars['class']->value), 0);?>

</div>
<?php }} ?>