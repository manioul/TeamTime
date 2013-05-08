<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 15:18:39
         compiled from "/var/www/TeamTime/themes/anis/templates/debugMessages.tpl" */ ?>
<?php /*%%SmartyHeaderCode:17202394935188ff2fecaad8-77678666%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8f7bdf5feeec321cd311b4e78fb0f4792cd5af30' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/debugMessages.tpl',
      1 => 1367932717,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17202394935188ff2fecaad8-77678666',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'debugMessages' => 0,
    'message' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188ff2ff21b76_78193982',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188ff2ff21b76_78193982')) {function content_5188ff2ff21b76_78193982($_smarty_tpl) {?>
<div id="dbgMsg">
<ul>
<?php if ($_smarty_tpl->tpl_vars['debugMessages']->value!==false){?>
<?php  $_smarty_tpl->tpl_vars['message'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['message']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['debugMessages']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['message']->key => $_smarty_tpl->tpl_vars['message']->value){
$_smarty_tpl->tpl_vars['message']->_loop = true;
?>
<li><pre><?php echo $_smarty_tpl->tpl_vars['message']->value;?>
</pre></li>
<?php } ?>
<?php }?>
</ul>
</div>
<?php }} ?>