<?php
$templates = [];

$pages_path = view::getThemePath().'/pages/';
if(file_exists($pages_path)) {
  $pages = scandir($pages_path);
  foreach ($pages as $page) if($page[0]!='.'){
    $templates[] = [
      'title'=>$page, 'url'=>$pages_path.$page
      //description: 'Some desc 1',
      //file_get_contents($pages_path.$page);
      ];
  }
}
?>

<div id="code-tab"  class="gs-12" style="height:350px">
    <textarea name="<?=$textarea?>"><?=isset($p->page)?$p->page:$p->post?></textarea>
</div>

<script src="lib/tinymce/tinymce.min.js"></script>

<script>

function get_text_to_save() {
    return g('[name=p_post]').all[0].innerHTML;
}

tinymce.init({
  selector: '[name=<?=$textarea?>]',
  mode: 'none',
  height: 300,
  theme: 'modern',
  plugins: [
    'lists link image charmap hr anchor pagebreak',
    'searchreplace wordcount visualchars code',
    'insertdatetime media nonbreaking table contextmenu ',
    'template paste textcolor colorpicker textpattern codesample'
],
  toolbar1: 'styleselect | forecolor backcolor bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link media image codesample',
  templates: <?php echo json_encode((isset($templates)?$templates:[])); ?>,
  document_base_url : "<?=gila::config('base')?>",
  content_css: <?php echo json_encode(view::$stylesheet); //isset($content_css)?$content_css:[] ?>,
  file_picker_callback: function(cb, value, meta) {
    input_filename = cb;
    open_gallery_post();
  },
  /*setup: function(editor) {
    editor.addMenuItem('myitem', {
      text: 'My menu item',
      context: 'table',
      onclick: function() {
        editor.insertContent('&nbsp;<table><tr><th>Ena<th>Dio</tr><tr><td><td>000</table>&nbsp;');
      }
    });
  },*/
 });

if(typeof requiredRes=='undefined') requiredRes = {}

g.require('lib/jquery/jquery-2.2.4.min.js',function(){
    g.loadCSS('lib/select2/select2.min.css');
    g.require(['lib/select2/select2.min.js'],function(){
        $('.select2').select2();
    })
})

g.dialog.buttons.select_path = {
    title:'Select',fn:function(){
        let v = g('#selected-path').attr('value')
        if(v!=null) g('[name=p_img]').attr('value',v)
        g('#gila-darkscreen').remove();
    }
}
g.dialog.buttons.select_path_post = {
    title:'Select',fn:function(){
        //if(!quill.getSelection()) return
        //index=quill.getSelection().index
        let v = g('#selected-path').attr('value')
        if(v!=null) input_filename(v);
        //if(v!=null) quill.insertEmbed(index, 'image', v);
        g('#gila-darkscreen').remove();
    }
}
function open_gallery() {
    g.post("admin/media","g_response=content&path=assets",function(gal){ //
        g.dialog({title:"Gila gallery",body:gal,buttons:'select_path'})
    })
}
function open_gallery_post() {
    g.post("admin/media","g_response=content&path=assets",function(gal){ //
        g.dialog({title:"Gila gallery",body:gal,buttons:'select_path_post'})
    })
}

</script>