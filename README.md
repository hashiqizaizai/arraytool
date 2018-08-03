#数组增强

数组增强组件主要是对数组等数据进行处理，如无限级分类操作、数组合并等操作。
参考yii2的助手函数以及后盾的数组增强插件，进行融合

####安装组件
使用 composer 命令进行安装或下载源代码使用。

```
composer require hashiqizaizai/arraytool
```
####使用组件

#测试数组
```
 $array = ['foo' => ['bar' => new User(),'bibao' =>  '测试',]];
```


#数组获取值 getValue
```
第二个参数描述 ：
 1.数组键名或者欲从中取值的对象的属性名称；
 2.以点号分割的数组键名或者对象属性名称组成的字符串，上例中使用的参数类型就是该类型；
 3.返回一个值的回调函数。

取值方法 ：
取值1 ：  第三个参数非必须 ，该值不存在时候默认返回null，存在则返回改设置值
         ArrayTool::getValue($array, 'foo.get','不存在该键值');
取值2 ：  匿名函数获取 ，对返回值进行处理
        $fullName = ArrayTool::getValue($array, function ($info, $defaultValue) { 
            return $info['foo']['bibao'].'=='.$info['foo']['bar']->test;
        });

```

#数组设置值 setValue
```
 $array1 = ['key' => ['in' => ['k' => 'value']]];
参数说明 ：
setValue(&$array, $path, $value)  ，对应 （数组，取值路径，设置的值）
设置方法 ：
设置1 ：修改对应的键值对
       ArrayTool::setValue($array1, 'foo.bibao', ['name' => 'val']);
设置2 ： 在 `$array` 中写入值的路径可以被指定为一个数组    
       ArrayTool::setValue($array1, ['key', 'in'], ['name' => 'val']); 
设置3 ： 如果路径包含一个不存在的键，它将被创建  
       ArrayTool::setValue($array1, 'key.in.arr0.arr1', 'val');
```

#数组移除单个 remove

```
$array2 = ['type' => 'A', 'options' => [1, 2],'name'=>2323];
用途描述 ： 如果你想获得一个值，然后立即从数组中删除它 ，与 getValue 方法不同，remove 仅支持简单的键名称
参数描述 ： (&$array, $key, $default = null) ，对应 （原先数组，要删除的键，默认返回）
           该值不存在时候默认返回null，存在则返回改设置值
获取1 ： 简单的删除值
        $type = ArrayTool::remove($array2, 'type','不存在该取消的值');
返回解释 ：
        $type 为删除的type值 ，$array2 为删除之后的值 
```

#数组移除多个 removeMulti
```
$array2 = ['type' => 'A', 'options' => [1, 2],'name'=>2323];
用途描述 ： 同时删除一个或者多个数组中的值
参数描述 ： ($data,$extName,$default = null) ，对应 （原先数组，删除数组键组成的数组，默认返回）
           该值不存在时候默认返回null，存在则返回改设置值
获取1 ： 简单的删除值
        $type =  ArrayTool::removeMulti($array2,['type','name']);
返回解释 ：
        $type 为删除之后的数组
```

#过滤下标 filterKeys
```
用途描述 ： 相当于数组移除操作
$d = [ 'id' => 1, 'url' => 'houdunwang.com','title'=>'后盾网' ];
参数描述 ： (array $data, $keys, $type = 1)  对应 （数组，要过滤的键集合，1 过滤 | 0 保留）
过滤 1 ： $res = ArrayTool::filterKeys($d,['id','url'],1);
         [ 'title'=>'后盾网' ];
过滤 2 ： $res = ArrayTool::filterKeys($d,['id','url'],0);
         [ 'id' => 1, 'url' => 'houdunwang.com' ]
```

#检查键名 array_key_exists
```
$data1 = ['userName' => 'Alex',];
用途描述 ： 检查对应的键是否存在数组中
参数说明 ： keyExists($key, $array, $caseSensitive = true) 对应 （键值，数组，大小写敏感）
           第三个参数默认true，区分大小写， false 为不区分大小写 
返回说明 ： 范返回bool值 ，true/false           
```

#检索列 getColumn
```
$data = [['id' => '123', 'data' => 'abc'],['id' => '345', 'data' => 'def']];
用途描述 ： 通过检索数组，对指定数组中的键值进行重组
检索1 ：
$ids = ArrayTool::getColumn($data, 'id'); 
正确返回1 ： ['123', '345']
错误返回1 ： 无键值存在时候     [null, null]  ,  !$ids[0] 或者 !$ids[1] 是否为空判断成功  

检索2 ： 如果需要额外的转换或者取值的方法比较复杂， 第二参数可以指定一个匿名函数，对其返回键值做处理
		 $result = ArrayTool::getColumn($data, function ($element) {
		            return $element['data'].$element['id'];exit;
		        });
```

#重建数组索引 index
```
$data = [['id' => '123', 'data' => 'abc'],['id' => '345', 'data' => 'def']];
用途描述 ： /按一个指定的键名重新索引一个数组 ，输入的数组应该是多维数组或者是一个对象数组
参数说明 ：($array, $key, $groups = [])  对应 （索引数组，索引键值，）
		 $array = [
		            ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
		            ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
		            ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
		        ];
索引1 : 无第三个参数
       $result = ArrayTool::index($array, 'id');	
返回1 ： ['123'=>[数组] ,'345'=>[数组]] ，键值相同后面的覆盖前面

索引2 : 无第三个参数 , 第三个参数 $groups 属性是一个键数组 ，根据指定的键将输入数组分组为一个或多个子数组
       $result = ArrayTool::index($array, 'id',['id','data']);
返回2 ：   ['123'=>['abc'=>['123'=>[数组]],'345'=>['def'=>['345'=>[数组]],'hgi'=>['345'=>[数组]]]] 
返回说明 ： 将是一个多维数组，由第一级的 id 分组，第二级的 device 和第三级的 data 索引： 

索引3 : key 为空  传递 id 作为第三个参数将 id 分配给 $ array
        $result2 = ArrayHelper::index($array, null, 'id');     
返回3：  区别于 返回1 ，将不会被覆盖，重新组成三维数组 

索引4 ： 匿名函数 ，用 'id'作为键 与 返回1 相同
		$result1 = ArrayTool::index($array, function ($element) {
		            return $element['id'];
		        }); 
索引4 ： 匿名函数 多维数组 与 返回2 相同
		$result = ArrayTool::index($array, 'data', [function ($element) {
		            return $element['id'];
		        }, 'device']); 		        
```

#建立哈希表 map
```
用途描述 ： 为了从一个多维数组或者一个对象数组中建立一个映射表（键值对）
		 $array = [
		            ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
		            ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
		            ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
		        ];
建立1 ：   $result = ArrayTool::map($array, 'id', 'name');
返回1 ：  返回id键 和 name的值\

建立2 ：  $result = ArrayTool::map($array, 'id', 'name','class');	
返回2 ： 按照class键 进行分类	        
```

#多维排序 multisort
```
用途描述 ： 用来对嵌套数组或者对象数组进行排序
	         $data = [
	            ['age' => 30, 'name' => 'Alexander'],
	            ['age' => 30, 'name' => 'Brian'],
	            ['age' => 19, 'name' => 'Barney'],
	        ];
返回描述 ：  返回原来的数组 $data ,原来数组被改变

排序1 ：  ArrayTool::multisort($data, ['age', 'name'], [SORT_ASC,SORT_DESC]);

排序2 ： 使用匿名函数  是单键名的话可以是字符串，如果是多键名则是一个数组
		 $result = ArrayTool::multisort($data, function($item) {
		            return isset($item['age2']) ? ['age', 'name'] : $item['age2'];
		 });

返回2 ： 按照class键 进行分类	        
```

#检测数组类型 isIndexed  isAssociative
```
函数描述 ： isIndexed 是否为索引数组（数字键数组） ， isAssociative 是否为 联合数组
用途描述 ： 想知道一个数组是索引数组还是联合数组很方便
           $indexed = ['b'=>'Qiang%232@232<span>232</span> <b>bold</b>', 'a'=>'Paul']; 
返回描述 ： 返回 true/false
           $result =  ArrayTool::isIndexed($indexed);   全为数字索引返回true
           $result =  ArrayTool::isAssociative($indexed);   全为键名都是字符串  正确返回true 
```

#数组编码解码 htmlEncode htmlDecode
```
用途描述 ： 对数组的键值进行解码，编码
参数描述 ： 编码参数 ($data, $valuesOnly = true, $charset = null) 对应 （数组，是否对值做编码，默认utf8）
            $valuesOnly = true 默认。第二个参数传 false，对键名也进行编码
           解码参数 ($data, $valuesOnly = true)  对应 （数组，是否对值做编码） 
  $encoded = ArrayTool::htmlEncode($indexed,false);   数组编码
  $encoded = ArrayTool::htmlDecode($indexed,false);   数组解码
```

#数组合并 merge
```
用途描述 ： 将两个或多个数组合并成一个递归的数组，每个数组都有一个具有相同字符串键值的元素，则后者将覆盖前者
           对于整数键的元素，来自后一个数组的元素将被附加到前一个数组。        
合并 ： 合并两个数组
		 $array1 = ['id'=>1,'test'=>111,'data'=>'abc','ids'=>['a'=>1,1],'info'=>['name'=>1,'info'=>'111'],'replace'=>['a'=>'b']];
		 $array2 = ['id'=>2,'test'=>111,'data1'=>'abc','ids'=>['a'=>2,2],'info'=>['name'=>ArrayTool::getUnsetArray()],'replace'=>ArrayTool::getReplaceArray(['s'=>232]) ];
取消前一个数组的值 ：
取消1 ： 'name'=>ArrayTool::getUnsetArray()  取消前一个数组的name 
取消2 ： 'name'=>new \hashiqizaizai\arraytool\UnsetArrayValue()  取消前一个数组的name 

替换前一个数组的值
替换1 ： 'replace'=>ArrayTool::getReplaceArray(['s'=>232]) 
替换2 ： 'replace'=>new \hashiqizaizai\arraytool\ReplaceArrayValue(['s'=>232])
```

#对象转化为数组 toArray
```
用途描述 ： 一个对象或者对象的数组转换成一个数组，或者yii2 AR 模型转化为数组
转化1 :  案例1 yii2  $posts = Post::find()->limit(10)->all()->toArray();
转化2 ：  $posts = new \StdClass();  $posts->test = '23232'; $posts->toArray();
转化3 :  $posts = new  \common\models\UnsetArrayValue();  $result = ArrayHelper::toArray($posts);
转化4 ：
        /*一个要包含的照原样的字段名（和类中属性的名称一致）；
        一个由你可随意取名的键名和你想从中取值的模型列名组成的键值对；
        一个由你可随意取名的键名和有返回值的回调函数组成的键值对；*/

        $posts = new  \common\models\User();
        $data = ArrayHelper::toArray($posts, [
            'app\models\Post' => [
                'id',
                'title',
                // created_at  为 模型User的属性
                'createTime' => 'created_at',
                // 方法处理
                'length' => function ($post) {
                    return strlen($post->content);
                },
            ],
        ]);
```

#检测列阵 isIn  isSubset
```
用途描述  ： 查一个元素是否在数组中，或者一组元素是另一个元素的子集
返回 ： 返回bool ，true/false
检测1 ： $result = ArrayTool::isIn('a', ['a']);
检测2 ： 数组转化为数组对象  ArrayObject的常用函数
			 $array =array('1'=>'one', '2'=>'two', '232'=>'three');
	        $obj = new \ArrayObject($array) ;
	        $posts->test = [1,2];
	        $result =ArrayTool::isIn('two', $obj );
检测3 ： 两个对象之间的检测
        $result = ArrayTool::isSubset(new \ArrayObject(['a', 'c']), new \ArrayObject(['a', 'b', 'c']));

附带学习 : ArrayObject  php 原始函数的用法
        $obj = new \ArrayObject($array) ;
        $iterator= $obj->getIterator();
        $iterator->next();
        $iterator->next(); 

参数学习 ：
        $obj = new \ArrayObject($array) ; 用法
        ArrayIterator::current( void ) //返回当前数组元素
        ArrayIterator::key(void) //返回当前数组key
        ArrayIterator::next (void)//指向下个数组元素
        ArrayIterator::rewind(void )//重置数组指针到头
        ArrayIterator::seek()//查找数组中某一位置
        ArrayIterator::valid()//检查数组是否还包含其他元素
        ArrayObject::append()//添加新元素
        ArrayObject::__construct()//构造一个新的数组对象
        ArrayObject::count()//返回迭代器中元素个数
        ArrayObject::getIterator()//从一个数组对象构造一个新迭代器
        ArrayObject::offsetExists(mixed index )//判断提交的值是否存在
        ArrayObject::offsetGet()//指定 name 获取值
        ArrayObject::offsetSet()//修改指定 name 的值
        ArrayObject::offsetUnset()//删除数据             	        
```

#树状栏目操作
```
       $data = [
            ['cid' => 1, 'pid' => 0, 'title' => '新闻'],
            ['cid' => 2, 'pid' => 1, 'title' => '国内新闻'],
            ['cid' => 3, 'pid' => 2, 'title' => '国内新闻2'],
            ['cid' => 4, 'pid' => 2, 'title' => '国内新闻3'],
        ];
树状1 ：   获取树状结构  参数  （数组，字段名称，主键，父集id）  
          $d  = ArrayTool::tree($data, 'title', 'cid', 'pid');
树状2 ：   获取目录列表  获取对应pid的列表
          $d = ArrayTool::channelList($data, $pid = 2, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid');       
树状3 ：   获取多级目录列表  返回多维数组
          $d = ArrayTool::channelLevel($data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid');
树状4 ：   获取所有的父集栏目  第二个参数为子栏目的cid   ，获取包括其在上的所有父集栏目
          $d = ArrayTool::parentChannel($data, 2, $fieldPri = 'cid', $fieldPid = 'pid');    
树状5 ：  是否为子栏目 第二个参数  子栏目id  ，第三个参数 父栏目id
          $d = ArrayTool::isChild($data,3, 1, $fieldPri = 'cid', $fieldPid = 'pid');  
树状6 ：  是否有子栏目  第二个参数为 判断的子栏目id
             $d = ArrayTool::hasChild($data,2, $fieldPid = 'pid');             
```
