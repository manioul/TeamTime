<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 11:53:24
         compiled from "/var/www/TeamTime/themes/anis/templates/support.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8784219535188cf14a333b6-92603190%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e1578550f99806c4e58dd1424bc183bf4028bc45' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/support.tpl',
      1 => 1366968018,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8784219535188cf14a333b6-92603190',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content' => 0,
    'v' => 0,
    'k' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188cf14a51800_20190356',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188cf14a51800_20190356')) {function content_5188cf14a51800_20190356($_smarty_tpl) {?>
		<ul class="lien"><li><h1>Support</h1>
			<div id="dSupport" class="desc boite">
				<ul class="sousmenu">
				<?php  $_smarty_tpl->tpl_vars['v'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['v']->_loop = false;
 $_smarty_tpl->tpl_vars['k'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['content']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['v']->key => $_smarty_tpl->tpl_vars['v']->value){
$_smarty_tpl->tpl_vars['v']->_loop = true;
 $_smarty_tpl->tpl_vars['k']->value = $_smarty_tpl->tpl_vars['v']->key;
?>
					<li><a href="<?php echo $_smarty_tpl->tpl_vars['v']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</a></li>
				<?php } ?>
				</ul>
			</div>
			</li>
		</ul>
<?php }} ?>