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
    'csv'=> ['id','title','slug','updated','publish','page'],
    'fields'=> [
        'id'=> ['title'=>'ID', 'edit'=>false],
        'widget'=> ['title'=>'Widget', 'options'=>$widgets,  'create'=>true], //'edit'=>false,
        'title'=> ['title'=>'Title'],
        'area'=> ['title'=>'Widget Area', 'options'=>$widget_areas],
        'pos'=> ['title'=>'Position'],
        'active'=> [
          'title'=>'Active',
          'type'=>'checkbox','edit'=>true,'create'=>false
        ],
        'data'=> [
          'title'=>'Data', 'show'=>false, 'edit'=>false,
          'qcolumn'=>"REPLACE(data,'\"','\\\"')", 'type'=>'text'
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
      //(isset($default_data)?$default_data:'{}');
    }
];
