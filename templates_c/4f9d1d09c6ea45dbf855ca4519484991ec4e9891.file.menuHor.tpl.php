<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 14:27:02
         compiled from "/var/www/TeamTime/themes/anis/templates/menuHor.tpl" */ ?>
<?php /*%%SmartyHeaderCode:20973313155188f3161f5082-94723805%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4f9d1d09c6ea45dbf855ca4519484991ec4e9891' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/menuHor.tpl',
      1 => 1366968378,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '20973313155188f3161f5082-94723805',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'class' => 0,
    'arbre' => 0,
    'element' => 0,
    'menu' => 0,
    'k' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188f31622c918_22393729',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188f31622c918_22393729')) {function content_5188f31622c918_22393729($_smarty_tpl) {?>
<ul class="<?php echo $_smarty_tpl->tpl_vars['class']->value;?>
">
<?php  $_smarty_tpl->tpl_vars['element'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['element']->_loop = false;
 $_smarty_tpl->tpl_vars['k'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['arbre']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['element']->key => $_smarty_tpl->tpl_vars['element']->value){
$_smarty_tpl->tpl_vars['element']->_loop = true;
 $_smarty_tpl->tpl_vars['k']->value = $_smarty_tpl->tpl_vars['element']->key;
?>
<?php echo $_smarty_tpl->getSubTemplate ('elem_menu.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('elem'=>$_smarty_tpl->tpl_vars['element']->value,'id'=>$_smarty_tpl->tpl_vars['menu']->value->titreAsId(),'key'=>$_smarty_tpl->tpl_vars['k']->value), 0);?>

<?php } ?>
</ul>
<?php }} ?>