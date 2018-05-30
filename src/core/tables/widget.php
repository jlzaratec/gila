<?php

$widget_areas = ['x'=>'(None)'];
foreach (gila::$widget_area as $value) {
    $widget_areas[$value] = $value;
}
$widgets = [];
foreach (gila::$widget as $k=>$value) {
    $widgets[$k] = $k;
}

$table = [
    'name'=> 'widget',
    'title'=> 'Widgets',
    'id'=>'id',
    'tools'=>['add'],
    'commands'=>['edit_widget','delete'],
    'list'=> ['id','title','widget','area','pos','active'],
    'csv'=> ['id','title','widget','area','pos','active'],
    'permissions'=>[
        'create'=>['admin'],
        'update'=>['admin'],
        'delete'=>['admin']
    ],
    'search-boxes'=> ['area','widget'],
    'fields'=> [
        'id'=> ['title'=>'ID', 'edit'=>false],
        'widget'=> ['title'=>'Type', 'options'=>$widgets,  'create'=>true], //'edit'=>false,
        'title'=> ['title'=>'Title'],
        'area'=> ['title'=>'Widget Area', 'options'=>$widget_areas],
        'pos'=> ['title'=>'Position'],
        'active'=> [
          'title'=>'Active',
          'type'=>'checkbox','edit'=>true
        ],
        'data'=> [
          'title'=>'Data', 'list'=>false, 'edit'=>false,
          //'qcolumn'=>"REPLACE(data,'\"','\\\"')",
          'type'=>'text'
        ],
    ],
    'oncreate'=>function(&$row){
      include 'src/'.gila::$widget[$row['widget']].'/widget.php';
      $default_data=[];
      foreach($options as $key=>$op) {
        if(isset($op['default'])) $def=$op['default']; else $def='';
        $default_data[$key]=$def;
      }

      $row['data']=json_encode($default_data);
    }
];
