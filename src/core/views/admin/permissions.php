<?php

// load all permissions
$permissions = [];
$packages = array_merge(gila::config('packages'),["core"]);
foreach($packages as $package) {
    $pjson = 'src/'.$package.'/package.json';
    if(file_exists($pjson)) {
        $parray = json_decode(file_get_contents($pjson),true);
        if(isset($parray['permissions']))
            $permissions = array_merge($permissions, $parray['permissions']);
    }
}

// load all user groups
global $db;
$roles = $db->get("SELECT id,userrole FROM userrole;");

// update permissions.json if form submited
if(isset($_POST['submit']) && isset($_POST['role'])) {
    $checked = [];
    foreach($_POST['role'] as $role=>$list) {
        if(is_array($list)) {
            $checked[$role] = array_keys($list);
        }
    }
    file_put_contents('log/permissions.json',json_encode($checked,JSON_PRETTY_PRINT));
    view::alert('success',__('_changes_updated'));
}

// read the permissions.json
$checked = [];
if(file_exists('log/permissions.json')) {
    $checked = json_decode(file_get_contents('log/permissions.json'),true);
}

view::alerts();
?>
<br>
<form action="<?=gila::url('admin/users?tab=2')?>" method="POST">
<button class="g-btn" name="submit" value="true">
    <i class="fa fa-save"></i> <?=__('Submit')?>
</button>
<br>

<table id="tbl-permissions" class="g-table">
    <tr>
        <th><?php foreach($roles as $role) {
            echo '<th style="text-align:center">'.$role[1];
        } ?>
    </tr>
    <?php foreach($permissions as $index=>$permission) { ?>
    <tr>
        <td>
            <strong><?=$index?></strong><br><?=$permission?>
        <?php foreach($roles as $role) {
            echo '<td style="text-align:center">';
            echo '<input type="checkbox" name="role['.$role[0].']['.$index.']"';
            if(isset($checked[$role[0]]) && in_array($index,$checked[$role[0]])) echo ' checked';
            echo '>';
        } ?>
    </tr>
    <?php } ?>
</table>
</form>
