
<style>
#menu {
  font-family: Arial, sans-serif;
  color: #444;
}
#menu .item {
  padding:0;
  margin:0.4em 0;
  border:1px solid #b6b6b6;
}
#menu .item>div {
  background: #e8e8e8;
  /*border-bottom: 1px solid #999;*/
  padding:3px;
  height:48px;
}
#menu .bold {
  font-weight: bold;
  border-radius:6px;
  padding:0.8em;
}
#menu ul {
  padding-left: 1.8em;
  padding-top: 1em;
  padding-bottom: 1em;
  line-height: 1.5em;
  list-style-type: none;
  padding-right: 0.4em;
}
#menu input {
  width:auto;
}
#menu .i-btn {
  opacity:0.2;
  /* cursor: pointer; */
}
#menu .i-btn:hover {
  opacity:1;
}
#menu .add, #menu .condition {
  display: inline-block;
  border-radius: 8px;
  border: 1px solid green;
  color: green;
  padding: 0 4px;
  font-size:0.8em;
}
#menu .condition {
  border: 1px solid red;
  color: red;
}
</style>

<?php
use core\models\page as page;

global $db;


$pages = page::genPublished();
$pageOptions = "";
foreach ($pages as $p) {
    $pageOptions .= "<option value=\"{$p['id']}\">{$p['title']}</option>";
}

$ql = "SELECT id,title FROM postcategory;";
$cats = $db->get($ql);
$postcategoryOptions = "";
foreach ($cats as $p) {
    $postcategoryOptions .= "<option value=\"{$p[0]}\">{$p[1]}</option>";
}

$itemTypes = [
    "link"=>[
        "data"=>[
            "type"=>"link",
            "name"=>"New Link",
            "url"=>"#"
        ],
        "template"=>"<input v-model=\"model.name\" class=\"g-input\" placeholder=\"Name\"><i class=\"fa fa-chevron-right\"></i> <input v-model=\"model.url\" class=\"g-input\" placeholder=\"URI\">"
    ],
    "page"=>[
        "data"=>[
            "type"=>"page",
            "id"=>1
        ],
        "template"=>"<select class=\"g-input\" v-model=\"model.id\">$pageOptions</select>"
    ],
    "postcategory"=>[
        "data"=>[
            "type"=>"postcategory",
            "id"=>1
        ],
        "template"=>"<select class=\"g-input\" v-model=\"model.id\">$postcategoryOptions</select>"
    ],
    "dir"=>[
        "data"=>[
            "type"=>"dir",
            "name"=>"New Directory",
            "children"=>[]
        ],
        "template"=>"<input v-model=\"model.name\" class=\"g-input\" placeholder=\"Name\">",
        //"parent"=>"menu"
    ]
];

?>

<script src="lib/vue/vue.min.js"></script>

<!-- item template -->
<script type="text/x-template" id="item-template">
  <li :id="uid" data-index="index">
      <div @dragstart="dragStart()" @dragend="$emit('remove')" draggable="true">
          <span v-show="model.type!='menu'" >::: </span>
          <i v-if="isFolder&&open" class="fa fa-folder-open-o" @click="toggle"></i>
          <i v-if="isFolder&&open==false"  class="fa fa-folder-o" @click="toggle"></i>
          <?php
          foreach($itemTypes as $type=>$item) {
              echo "<span v-if=\"model.type=='$type'\" draggable=\"false\">";
              echo $item['template'];
              echo "</span>";
          }
          ?>

          <a v-if="model.type=='menu'" @click="saveMenu" class="g-btn success" style="float:right"><?=__("Save")?></a>
          <i v-if="model.type!='menu'" @click="$emit('remove')" class="fa fa-trash i-btn" style="float:right"></i>
          <span v-show="(isFolder&&open)||model.type=='menu'">
              <?php
              foreach($itemTypes as $type=>$item) {
                  if(isset($item['parent'])) $vif = "v-if=\"model.type=='{$item['parent']}'\""; else $vif="";
                  echo "<span $vif class=\"add\" @click=\"addChild('$type')\">+$type</span>";
              }
              ?>
          </span>
      </div>
    <ul v-show="(isFolder&&open)||model.type=='menu'" @drop="drop()" @dragover="allowDrop()" data-drop="insert">
      <item
        class="item" v-for="(model, index) in model.children"
        :key="index" :model="model"
        v-on:remove="removeItem(index)">
      </item>
    </ul>
  </li>
</script>



<h1><?=__("Main Menu")?></h1><hr>
<div id="menu">
<ul>
  <item
    class="item" :model="treeData">
  </item>
</ul>
</div>

<script>

<?php
$data="{type:\"menu\",children:[]}";
if(file_exists('log/menus/mainmenu.json')) {
    $data = file_get_contents('log/menus/mainmenu.json');
}
echo "var data = ".$data."\n";

$itemTypesJSON = [];
foreach($itemTypes as $type=>$item) {
    $itemTypesJSON[$type] = $item['data'];
}
echo "var itemTypes = ".json_encode($itemTypesJSON)."\n";
?>

item_drag = false

// define the item component
Vue.component('item', {
  template: '#item-template',
  props: {
    model: Object,
    key: 0
  },
  data: function () {
    return {
      open: false
    }
  },
  computed: {
    isFolder: function () {
        if(this.model.type=='dir') return true
        return false
        if(this.model.type=='menu') return false
        return this.model.children
    },
    isItem: function () {
        if(this.model.type=='menu') return false
        return this.model.type
    },
    uid: function () {
        return uuidv4()
    },
  },
  methods: {
      saveMenu: function() {
          let fm=new FormData()
          fm.append('menu', JSON.stringify(this.model));
          g.ajax({url:"admin/menu",method:'POST',data:fm, fn: function (response){
              alert(JSON.parse(response).msg);
          }})
      },
    moveUp: function() {
      var node = event.target.parentNode.parentNode
      var node2 = node.previousSibling
      if(typeof node2.tagName!='undefined') node.parentNode.insertBefore(node,node2)
    },
    moveDown: function() {
        var node = event.target.parentNode.parentNode
        var node2 = node.nextSibling
        if(typeof node2.tagName!='undefined') node.parentNode.insertBefore(node2,node)
    },
    toggle: function () {
      this.open = !this.open
    },
    removeItem: function (index) {
        if(item_drag == false) this.model.children.splice(index,1)
    },
    alertdata: function() {
        alert(JSON.stringify(this.model))
    },
    addChild: function (type) {
      if(typeof this.model.children==='undefined') Vue.set(this.model, 'children', [])
      this.model.children.push({type:type})
    },
    addCondition: function (_c) {
     this.model.children.push({type:_c,children:[]})
    },
    dragStart: function (index) {
        item_drag = true;
        if(this.model.type=='menu') return false;
        event.dataTransfer.setData("Text", event.target.parentNode.id);
        event.dataTransfer.setData("Item", JSON.stringify(this.model));
    },
    allowDrop: function () {
        event.preventDefault();
    },
    nodrop: function () {
        event.preventDefault();
    },
    drop: function () {
        if(item_drag == false) return;

        if(event.target.tagName=='UL'){
            ul=event.target
        } else return;

        var dd = event.target.getAttribute('data-drop');
        var data = event.dataTransfer.getData("Text");
        let item = JSON.parse(event.dataTransfer.getData("Item"));
        var el = document.getElementById(data);
        event.preventDefault();

        if(dd=='insert') {

            y = event.pageY - ul.offsetTop
            pageY = event.pageY
            _model = this.model

            if(item!='') setTimeout(function(){
                chn = ul.childElementCount
                if(chn>0) {
                    chh = ul.offsetHeight/chn
                    ind = Math.floor(y/chh)
                    if(ind>chn) ind=chn-1
                    if(ind<0) ind=0
                    _model.children.splice(ind,0,item);
                    _model.children[ind].open=false
                } else {
                    _model.children.push(item);
                }
            }, 30)

            event.dataTransfer.setData("Item",'')
            item_drag = false
        }

        event.dataTransfer.clearData();
    }
  }
})


var demo = new Vue({
  el: '#menu',
  data: {
    treeData: data
  }
})


function uuidv4() {
  return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  )
}

</script>