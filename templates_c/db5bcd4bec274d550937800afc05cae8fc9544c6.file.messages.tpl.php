<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 14:27:02
         compiled from "/var/www/TeamTime/themes/anis/templates/messages.tpl" */ ?>
<?php /*%%SmartyHeaderCode:16299060145188f3162735b5-91810976%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'db5bcd4bec274d550937800afc05cae8fc9544c6' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/messages.tpl',
      1 => 1366968407,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16299060145188f3162735b5-91810976',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'messages' => 0,
    'index' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188f3162bf109_97033227',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188f3162bf109_97033227')) {function content_5188f3162bf109_97033227($_smarty_tpl) {?>
<div id="dMessages">
<?php if (isset($_smarty_tpl->tpl_vars['smarty']->value['section']['i'])) unset($_smarty_tpl->tpl_vars['smarty']->value['section']['i']);
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['name'] = 'i';
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop'] = is_array($_loop=$_smarty_tpl->tpl_vars['messages']->value) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show'] = true;
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['max'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'] = 1;
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['start'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'] > 0 ? 0 : $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop']-1;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show']) {
    $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop'];
    if ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'] == 0)
        $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show'] = false;
} else
    $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'] = 0;
if ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['show']):

            for ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['start'], $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] = 1;
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] <= $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total'];
                 $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] += $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'], $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration']++):
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['rownum'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index_prev'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] - $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index_next'] = $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['index'] + $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['step'];
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['first']      = ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] == 1);
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['last']       = ($_smarty_tpl->tpl_vars['smarty']->value['section']['i']['iteration'] == $_smarty_tpl->tpl_vars['smarty']->value['section']['i']['total']);
?>
<?php if ($_smarty_tpl->getVariable('smarty')->value['section']['i']['index']==0){?><ul id="uMessages"><?php }?>
<li<?php if ($_smarty_tpl->tpl_vars['messages']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['classe']!=''){?> class="<?php echo $_smarty_tpl->tpl_vars['messages']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['classe'];?>
"<?php }?>><?php if ($_smarty_tpl->tpl_vars['messages']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['lien']!=''){?><a href="<?php echo $_smarty_tpl->tpl_vars['messages']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['lien'];?>
"><?php }?><?php echo $_smarty_tpl->tpl_vars['messages']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['message'];?>
<?php if ($_smarty_tpl->tpl_vars['messages']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']]['lien']!=''){?></a><?php }?></li>
<?php if ($_smarty_tpl->tpl_vars['index']->value==$_smarty_tpl->getVariable('smarty')->value['section']['i']['last']){?></ul><?php }?>
<?php endfor; endif; ?>
</div>
<?php }} ?>