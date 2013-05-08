<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 14:27:02
         compiled from "/var/www/TeamTime/themes/anis/templates/elem_menu.tpl" */ ?>
<?php /*%%SmartyHeaderCode:12039096325188f31622faa2-93973099%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0671eacb0e48c43d331f8364bdc885b8db195800' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/elem_menu.tpl',
      1 => 1366968394,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12039096325188f31622faa2-93973099',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'id' => 0,
    'key' => 0,
    'elem' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188f31625f431_09038040',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188f31625f431_09038040')) {function content_5188f31625f431_09038040($_smarty_tpl) {?>

	<li id="<?php echo $_smarty_tpl->tpl_vars['id']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" class="">
		<a href="<?php echo $_smarty_tpl->tpl_vars['elem']->value->lien();?>
"><?php echo $_smarty_tpl->tpl_vars['elem']->value->titre();?>
</a>
		<?php if (!is_null($_smarty_tpl->tpl_vars['elem']->value->sousmenu())){?><?php echo $_smarty_tpl->getSubTemplate ('menuHor.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('arbre'=>$_smarty_tpl->tpl_vars['elem']->value->submenu()->arbre(),'menu'=>$_smarty_tpl->tpl_vars['elem']->value->submenu(),'class'=>''), 0);?>
<?php }?>
	</li>
<?php }} ?>