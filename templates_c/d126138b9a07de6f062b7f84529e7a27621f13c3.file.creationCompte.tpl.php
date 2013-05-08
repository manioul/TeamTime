<?php /* Smarty version Smarty-3.1-DEV, created on 2013-05-07 15:12:50
         compiled from "/var/www/TeamTime/themes/anis/templates/creationCompte.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8053484635188fdd2554806-91017308%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd126138b9a07de6f062b7f84529e7a27621f13c3' => 
    array (
      0 => '/var/www/TeamTime/themes/anis/templates/creationCompte.tpl',
      1 => 1366969009,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8053484635188fdd2554806-91017308',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'infos' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1-DEV',
  'unifunc' => 'content_5188fdd2594914_71825126',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5188fdd2594914_71825126')) {function content_5188fdd2594914_71825126($_smarty_tpl) {?>
<div>
<form name="fCc" method="post" action="" onsubmit="subCc()">
<table class="genElem">
<tr>
<td><label for="sCcnom">nom</label></td>
<td><select name="nom" id='sCcnom' onchange="updDispFormCc()">
<?php if (isset($_smarty_tpl->tpl_vars['smarty']->value['section']['i'])) unset($_smarty_tpl->tpl_vars['smarty']->value['section']['i']);
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['name'] = 'i';
$_smarty_tpl->tpl_vars['smarty']->value['section']['i']['loop'] = is_array($_loop=$_smarty_tpl->tpl_vars['infos']->value) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<option value="<?php echo $_smarty_tpl->tpl_vars['infos']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']];?>
"><?php echo $_smarty_tpl->tpl_vars['infos']->value[$_smarty_tpl->getVariable('smarty')->value['section']['i']['index']];?>
</option>
<?php endfor; endif; ?>
</select>
</td>
</tr><tr>
<td><label for="iCclogin">login</label></td><td><input type="text" name="login" id="iCclogin" /></td>
</tr><tr>
<td><label for="iCcpassword">mot de passe</label></td><td><input type="text" name="password" id="iCcpassword" /></td>
</tr><tr>
<td><label for="iCcemail">adresse mail</label></td><td><input type="text" name="email" id="iCcemail" /></td>
</tr><tr>
<td><label for="sendmail">Envoyer un mail</label><input type="checkbox" checked="checked" name="sendmail" id="sendmail" value="svp" /></td>
<td><input type="button" value="Envoyer" onclick="subCc()" /></td>
</tr>
</table>
</form>
</div>
<?php }} ?>