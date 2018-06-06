<?php
use core\models\page as page;
global $db;

$p_list = ['title', 'page', 'publish' ];
if ($_SERVER['REQUEST_METHOD'] === 'POST') if (router::post('submit-btn')=='submited'){
    $title = $_POST['p_title'];
    $text = $_POST['p_page'];
    $publish = isset($_POST['p_publish'])?1:0;
    $slug = $_POST['p_slug'];
    if($slug == '') {
        $slugify = new Cocur\Slugify\Slugify();
        $slug = $slugify->slugify($title);
    }
    $args = [$title,$text,$publish,$slug];
    if ($id == "new") {
		$db->query("INSERT INTO page(title,page,publish,slug) VALUES(?,?,?,?)",$args);
        $id = $db->insert_id;
    }
    else {
        $db->query("UPDATE page SET title=?,page=?,publish=?,slug=? WHERE id=$id",$args);
    }

    echo "<div class='alert success'>Changes have been saved successfully!</div>";
}

if ($id == 'new') {
    $p = (object)['slug'=>'','title'=>'','page'=>'','publish'=>1,'id'=>(isset($_POST['p_id'])?$_POST['p_id']:0)];
}
else {
    if (! $p = page::getById($id)) die("The page '$id' could not be found in db!");
    $p = (object)$p;
}

view::script('src/core/assets/admin/media.js');
?>

<div class="row ">
<form id="postForm" method="post" class="g-form" action="admin/pages/<?=($p->id?:'new')?>">
    <input value="<?=($p->id?:'new')?>" name="p_id" type="hidden"/>

    <div class="row ">
        <label class="gm-2">Title</label>
        <input class="gm-10" value="<?=$p->title?>" name="p_title"/>
    </div>

    <div class="row ">
        <label class="gm-2">Slug</label>
        <input class="gm-10" value="<?=$p->slug?>" name="p_slug" placeholder="Generate from title" />
    </div>

    <div class="row ">
        <label class="gm-2" for="p_publish">Public</label>
        <label class="g-switch"  for="p_publish">
            <input type="checkbox" id="p_publish" name="p_publish" <?=($p->publish==1?' checked':'')?>>
            <div class="g-slider"></div>
        </label>
        <!--label class="col-md-3">Public<input type="checkbox" name="p_publish" value="1"></label-->
    </div>

    <input type="hidden" name="submit-btn" id="submit-btn">
    <?php $textarea='p_page'; include __DIR__.'/edit_mce_editor.php'; ?>

    <br>
    <div class="btn-group">
        <button onclick="g.el('submit-btn').value='submited'; submit()" class="btn btn-primary primary" >Submit</button>
    </div>

</form>

</div>