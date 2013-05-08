<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 11:53:24
         compiled from "/var/www/TeamTime/themes/anis/templates/indexTtm.tpl" */ ?>
<?php /*%%SmartyHeaderCode:16243676675188cf14900b09-45844549%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '575ede3032f95d9035e684ef15dd6bade35c2426' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/indexTtm.tpl',
      1 => 1366901720,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16243676675188cf14900b09-45844549',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'nav' => 0,
    'content1' => 0,
    'content2' => 0,
    'content3' => 0,
    'content4' => 0,
    'contenu' => 0,
    'v' => 0,
    'VERSION' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188cf14a057d3_76526993',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188cf14a057d3_76526993')) {function content_5188cf14a057d3_76526993($_smarty_tpl) {?>
<div id='container'>
	<div id="horlogeFond"></div>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[1]!=''){?><div id="nav1"></div><?php }?>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[2]!=''){?><div id="nav2"></div><?php }?>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[3]!=''){?><div id="nav3"></div><?php }?>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[4]!=''){?><div id="nav4"></div><?php }?>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[1]!=''){?>
	<div id="nav1-text">
		<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['nav']->value[1]).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('content'=>$_smarty_tpl->tpl_vars['content1']->value), 0);?>

	</div>
	<?php }?>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[2]!=''){?>
	<div id="nav2-text">
		<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['nav']->value[2]).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('content'=>$_smarty_tpl->tpl_vars['content2']->value), 0);?>

	</div>
	<?php }?>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[3]!=''){?>
	<div id="nav3-text">
		<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['nav']->value[3]).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('content'=>$_smarty_tpl->tpl_vars['content3']->value), 0);?>

	</div>
	<?php }?>
	<?php if ($_smarty_tpl->tpl_vars['nav']->value[4]!=''){?>
	<div id="nav4-text">
		<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['nav']->value[4]).".tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('content'=>$_smarty_tpl->tpl_vars['content4']->value), 0);?>

	</div>
	<?php }?>
	<?php if (is_array($_smarty_tpl->tpl_vars['contenu']->value)){?>
	<div id="content" class="boite">
		<?php  $_smarty_tpl->tpl_vars['v'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['v']->_loop = false;
 $_smarty_tpl->tpl_vars['id'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['contenu']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['v']->key => $_smarty_tpl->tpl_vars['v']->value){
$_smarty_tpl->tpl_vars['v']->_loop = true;
 $_smarty_tpl->tpl_vars['id']->value = $_smarty_tpl->tpl_vars['v']->key;
?>
		<div id="$id">
			<h2><?php echo $_smarty_tpl->tpl_vars['v']->value['titre'];?>
</h2>
			<p><?php echo $_smarty_tpl->tpl_vars['v']->value['texte'];?>
</p>
		</div>
		<?php } ?>
	</div>
	<?php }?>
	<div id="version">v<?php echo $_smarty_tpl->tpl_vars['VERSION']->value;?>
</div>
</div>
<?php }} ?>