<?php
namespace hashiqizaizai\arraytool;
use hashiqizaizai\arraytool\StringTool;
use hashiqizaizai\arraytool\Exception;
use hashiqizaizai\arraytool\UnsetArrayValue;
use hashiqizaizai\arraytool\ReplaceArrayValue;

class ArrayTool
{    
    /*Closure 类  介绍
    用于代表 匿名函数 的类. 匿名函数（在 PHP 5.3 中被引入）会产生这个类型的对象
    提到闭包就不得不想起匿名函数，也叫闭包函数（closures）
    使用instanceof运算符，可以判断当前实例是否可以有这样的一个形态
    instanceof 用于确定一个 PHP 变量是否属于某一类 class 的实例，在此之前用 is_a(),但是后来 is_a() 被废弃
    instanceof也可用来确定一个变量是不是继承自某一父类的子类的实例
    class a{}
    class b{}
    $a = new a;
    var_dump($a instanceof \Closure);  true
    var_dump($a instanceof \Closure);  false*/


    
    //获取空类  用户合并数组时候 递归执行
    public static function  getUnsetArray(){
        return new UnsetArrayValue();
    }
    
    //替换数组   用户合并数组时候 进行替换执行
    public static function  getReplaceArray($value){
        return new ReplaceArrayValue($value);
    }

    //测试阵列（Testing against Arrays）
    //通常你需要检查一个元素是否在数组中，或者一组元素是另一个元素的子集。 虽然PHP提供 in_array()，这不支持子集或 \Traversable 对象。
    //Traversable用于检测一个类是否可以使用 foreach 进行遍历，

    public static function isIn($needle, $haystack, $strict = false)
    {   
        if ($haystack instanceof \Traversable) {
            foreach ($haystack as $value) {
                if ($needle == $value && (!$strict || $needle === $value)) {
                    return true;
                }
            }
        } elseif (is_array($haystack)) {
            return in_array($needle, $haystack, $strict);
        } else {
            throw new \Exception('Argument $haystack must be an array or implement Traversable');
        }

        return false;
    }

    public static function isSubset($needles, $haystack, $strict = false)
    {
        if (is_array($needles) || $needles instanceof \Traversable) {
            foreach ($needles as $needle) {
                if (!static::isIn($needle, $haystack, $strict)) {
                    return false;
                }
            }

            return true;
        }

        throw new \Exception('Argument $needles must be an array or implement Traversable');
    }



    public static function getValue($array, $key, $default = null)
    {   
        //是否是一个闭包  否则直接输出来
       
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessible beforehand
            return $array->$key;
        } elseif (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        }

        return $default;
    }

    //设置值
    public static function setValue(&$array, $path, $value)
    {
        if ($path === null) {
            $array = $value;
            return;
        }

        $keys = is_array($path) ? $path : explode('.', $path);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }
    
    //移除某些值
    public static function remove(&$array, $key, $default = null)
    {
        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            $value = $array[$key];
            unset($array[$key]);

            return $value;
        }

        return $default;
    }

    //移除数组的一些元素
    public static function removeMulti($data,$extName,$default = null)
    {   
        $extData = [];
        if(is_array($data) && is_array($extName)){
            foreach ((array)$data as $k => $v) {
                if ( ! in_array($k, $extName)) {
                    $extData[$k] = $v;
                }
            }
            return $extData;
        }
        

        return $default;
    }

    //数组进行整数映射转换
    public static function intToString($arr,array $map = ['status' => ['0' => '禁止', '1' => '启用']]) {
        foreach ($map as $name => $m) {
            if (isset($arr[$name]) && array_key_exists($arr[$name], $m)) {
                $arr['_'.$name] = $m[$arr[$name]];
            }
        }

        return $arr;
    }
    
    //数组进行字符串映射整数转换
    public static function stringToInt($data)
    {
        $tmp = $data;
        foreach ((array)$tmp as $k => $v) {
            $tmp[$k] = is_array($v) ? $this->stringToInt($v)
                : (is_numeric($v) ? intval($v) : $v);
        }

        return $tmp;
    }

    //过滤小标
    public static function filterKeys(array $data, $keys, $type = 1)
    {
        $tmp = $data;
        foreach ($data as $k => $v) {
            if ($type == 1) {
                //存在时过滤
                if (in_array($k, $keys)) {
                    unset($tmp[$k]);
                }
            } else {
                //不在时过滤
                if ( ! in_array($k, $keys)) {
                    unset($tmp[$k]);
                }
            }
        }

        return $tmp;
    }
 
    //检查键值是否存在
    public static function keyExists($key, $array, $caseSensitive = true)
    {
        if ($caseSensitive) {
            // Function `isset` checks key faster but skips `null`, `array_key_exists` handles this case
            // http://php.net/manual/en/function.array-key-exists.php#107786
            return isset($array[$key]) || array_key_exists($key, $array);
        }

        foreach (array_keys($array) as $k) {
            if (strcasecmp($key, $k) === 0) {
                return true;
            }
        }

        return false;
    }
    
    //检索多行数据或者多行对象的列
    public static function getColumn($array, $name, $keepKeys = true)
    {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $k => $element) {
                $result[$k] = static::getValue($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::getValue($element, $name);
            }
        }

        return $result;
    }

    //重建数组索引（Re-indexing Arrays）
    public static function index($array, $key, $groups = [])
    {
        $result = [];
        $groups = (array) $groups;


        foreach ($array as $element) {
            $lastArray = &$result;
            foreach ($groups as $group) {
                $value = static::getValue($element, $group);
                if (!array_key_exists($value, $lastArray)) {
                    $lastArray[$value] = [];
                }
                $lastArray = &$lastArray[$value];
            }

            if ($key === null) {
                if (!empty($groups)) {
                    $lastArray[] = $element;
                }
            } else {
                $value = static::getValue($element, $key);
                if ($value !== null) {
                    if (is_float($value)) {
                        $value = StringHelper::floatToString($value);
                    }
                    $lastArray[$value] = $element;
                }
            }
            unset($lastArray);
        }

        return $result;
    }

    //建立哈希表
    public static function map($array, $from, $to, $group = null)
    {
        $result = [];
        foreach ($array as $element) {
            $key = static::getValue($element, $from);
            $value = static::getValue($element, $to);
            if ($group !== null) {
                $result[static::getValue($element, $group)][$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    //多维数组排序
    public static function multisort(&$array, $key, $direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        $keys = is_array($key) ? $key : [$key];
        if (empty($keys) || empty($array)) {
            return;
        }
        $n = count($keys);
        if (is_scalar($direction)) {
            $direction = array_fill(0, $n, $direction);
        } elseif (count($direction) !== $n) {
            throw new \Exception('The length of $direction parameter must be the same as that of $keys.');
        }
        if (is_scalar($sortFlag)) {
            $sortFlag = array_fill(0, $n, $sortFlag);
        } elseif (count($sortFlag) !== $n) {
            throw new \Exception('The length of $sortFlag parameter must be the same as that of $keys.');
        }
        $args = [];
        foreach ($keys as $i => $key) {
            $flag = $sortFlag[$i];
            $args[] = static::getColumn($array, $key);
            $args[] = $direction[$i];
            $args[] = $flag;
        }

        // This fix is used for cases when main sorting specified by columns has equal values
        // Without it it will lead to Fatal Error: Nesting level too deep - recursive dependency?
        $args[] = range(1, count($array));
        $args[] = SORT_ASC;
        $args[] = SORT_NUMERIC;

        $args[] = &$array;
        call_user_func_array('array_multisort', $args);
    }

    //检测数组类型（Detecting Array Types）  组是索引数组还是联合数组
    //索引数组还是联合数组,用字符串表示键的数组就是下面要介绍的关联数组
    public static function isIndexed($array, $consecutive = false)
    {
        if (!is_array($array)) {
            return false;
        }

        if (empty($array)) {
            return true;
        }

        if ($consecutive) {
            return array_keys($array) === range(0, count($array) - 1);
        }

        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
    }

    public static function isAssociative($array, $allStrings = true)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        if ($allStrings) {
            foreach ($array as $key => $value) {
                if (!is_string($key)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($array as $key => $value) {
            if (is_string($key)) {
                return true;
            }
        }

        return false;
    }

    //数组编码
    public static function htmlEncode($data, $valuesOnly = true, $charset = null)
    {
        if ($charset === null) {
            $charset = 'UTF-8';
        }
        $d = [];
        foreach ($data as $key => $value) {
            if (!$valuesOnly && is_string($key)) {
                $key = htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            }
            if (is_string($value)) {
                $d[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            } elseif (is_array($value)) {
                $d[$key] = static::htmlEncode($value, $valuesOnly, $charset);
            } else {
                $d[$key] = $value;
            }
        }

        return $d;
    }
    
    //数组解码
    public static function htmlDecode($data, $valuesOnly = true)
    {
        $d = [];
        foreach ($data as $key => $value) {
            if (!$valuesOnly && is_string($key)) {
                $key = htmlspecialchars_decode($key, ENT_QUOTES);
            }
            if (is_string($value)) {
                $d[$key] = htmlspecialchars_decode($value, ENT_QUOTES);
            } elseif (is_array($value)) {
                $d[$key] = static::htmlDecode($value);
            } else {
                $d[$key] = $value;
            }
        }

        return $d;
    }

    //数组合并
    public static function merge($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            foreach (array_shift($args) as $k => $v) {
                if ($v instanceof UnsetArrayValue ) {
                    unset($res[$k]);
                } elseif ($v instanceof ReplaceArrayValue) {
                    $res[$k] = $v->value;
                } elseif (is_int($k)) {
                    if (array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }



    //转化为数组 对象转换为数组（Converting Objects to Arrays）
    public static function toArray($object, $properties = [], $recursive = true)
    {
        if (is_array($object)) {
            if ($recursive) {
                foreach ($object as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $object[$key] = static::toArray($value, $properties, true);
                    }
                }
            }

            return $object;
        } elseif (is_object($object)) {
            if (!empty($properties)) {
                $className = get_class($object);
                if (!empty($properties[$className])) {
                    $result = [];
                    foreach ($properties[$className] as $key => $name) {
                        if (is_int($key)) {
                            $result[$name] = $object->$name;
                        } else {
                            $result[$key] = static::getValue($object, $name);
                        }
                    }

                    return $recursive ? static::toArray($result, $properties) : $result;
                }
            }
            if ($object instanceof Arrayable) {
                $result = $object->toArray([], [], $recursive);
            } else {
                $result = [];
                foreach ($object as $key => $value) {
                    $result[$key] = $value;
                }
            }

            return $recursive ? static::toArray($result, $properties) : $result;
        }

        return [$object];
    }

    //============================   操作树状结构  ============================= 

    //获取树状结构
    public static function tree($data, $title, $fieldPri = 'cid', $fieldPid = 'pid')
    {
        if ( ! is_array($data) || empty($data)) {
            return [];
        }
        $arr = self::channelList($data, 0, '', $fieldPri, $fieldPid);
        foreach ($arr as $k => $v) {
            $str = "";
            if ($v['_level'] > 2) {
                for ($i = 1; $i < $v['_level'] - 1; $i++) {
                    $str .= "│&nbsp;&nbsp;&nbsp;&nbsp;";
                }
            }
            if ($v['_level'] != 1) {
                $t = $title ? $v[$title] : '';
                if (isset($arr[$k + 1])
                    && $arr[$k + 1]['_level'] >= $arr[$k]['_level']
                ) {
                    $arr[$k]['_'.$title] = $str."├─ ".$v['_html'].$t;
                } else {
                    $arr[$k]['_'.$title] = $str."└─ ".$v['_html'].$t;
                }
            } else {
                $arr[$k]['_'.$title] = $v[$title];
            }
        }
        //设置主键为$fieldPri
        $data = [];
        foreach ($arr as $d) {
            //            $data[$d[$fieldPri]] = $d;
            $data[] = $d;
        }

        return $data;
    }
    
    //获取目录列表
     public static function channelList($arr,$pid = 0,$html = "&nbsp;",$fieldPri = 'cid',$fieldPid = 'pid',$level = 1) {
        $pid  = is_array($pid) ? $pid : [$pid];
        $data = [];
        foreach ($pid as $id) {
            $res =self::_channelList(
                $arr,
                $id,
                $html,
                $fieldPri,
                $fieldPid,
                $level
            );
            foreach ($res as $k => $v) {
                $data[$k] = $v;
            }
        }
        if (empty($data)) {
            return $data;
        }
        foreach ($data as $n => $m) {
            if ($m['_level'] == 1) {
                continue;
            }
            $data[$n]['_first'] = false;
            $data[$n]['_end']   = false;
            if ( ! isset($data[$n - 1])
                 || $data[$n - 1]['_level'] != $m['_level']
            ) {
                $data[$n]['_first'] = true;
            }
            if (isset($data[$n + 1])
                && $data[$n]['_level'] > $data[$n + 1]['_level']
            ) {
                $data[$n]['_end'] = true;
            }
        }
        //更新key为栏目主键
        $category = [];
        foreach ($data as $d) {
            $category[$d[$fieldPri]] = $d;
        }

        return $category;
    }

    //获取多级目录列表
    public static function channelLevel(
        $data,
        $pid = 0,
        $html = "&nbsp;",
        $fieldPri = 'cid',
        $fieldPid = 'pid',
        $level = 1
    ) {
        if (empty($data)) {
            return [];
        }
        $arr = [];
        foreach ($data as $v) {
            if ($v[$fieldPid] == $pid) {
                $arr[$v[$fieldPri]]           = $v;
                $arr[$v[$fieldPri]]['_level'] = $level;
                $arr[$v[$fieldPri]]['_html']  = str_repeat($html, $level - 1);
                $arr[$v[$fieldPri]]["_data"]  = self::channelLevel(
                    $data,
                    $v[$fieldPri],
                    $html,
                    $fieldPri,
                    $fieldPid,
                    $level + 1
                );
            }
        }

        return $arr;
    }

    /**
     * 获得所有父级栏目
     *
     * @param        $data     栏目数据
     * @param        $sid      子栏目
     * @param string $fieldPri 唯一键名，如果是表则是表的主键
     * @param string $fieldPid 父ID键名
     *
     * @return array
     */
    public static function parentChannel(
        $data,
        $sid,
        $fieldPri = 'cid',
        $fieldPid = 'pid'
    ) {
        if (empty($data)) {
            return $data;
        } else {
            $arr = [];
            foreach ($data as $v) {
                if ($v[$fieldPri] == $sid) {
                    $arr[] = $v;
                    $_n    = self::parentChannel(
                        $data,
                        $v[$fieldPid],
                        $fieldPri,
                        $fieldPid
                    );
                    if ( ! empty($_n)) {
                        $arr = array_merge($arr, $_n);
                    }
                }
            }

            return $arr;
        }
    }

    /**
     * 判断$s_cid是否是$d_cid的子栏目
     *
     * @param        $data     栏目数据
     * @param        $sid      子栏目id
     * @param        $pid      父栏目id
     * @param string $fieldPri 主键
     * @param string $fieldPid 父id字段
     *
     * @return bool
     */
    public static function isChild(
        $data,
        $sid,
        $pid,
        $fieldPri = 'cid',
        $fieldPid = 'pid'
    ) {
        $_data = self::channelList($data, $pid, '', $fieldPri, $fieldPid);
        foreach ($_data as $c) {
            //目标栏目为源栏目的子栏目
            if ($c[$fieldPri] == $sid) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * 检测是不否有子栏目
     *
     * @param        $data     栏目数据
     * @param        $cid      要判断的栏目cid
     * @param string $fieldPid 父id表字段名
     *
     * @return bool
     */
     public static function hasChild($data, $cid, $fieldPid = 'pid')
    {
        foreach ($data as $d) {
            if ($d[$fieldPid] == $cid) {
                return true;
            }
        }

        return false;
    }

    //只供channelList方法使用
    private static function _channelList(
        $data,
        $pid = 0,
        $html = "&nbsp;",
        $fieldPri = 'cid',
        $fieldPid = 'pid',
        $level = 1
    ) {
        if (empty($data)) {
            return [];
        }
        $arr = [];
        foreach ($data as $v) {
            $id = $v[$fieldPri];
            if ($v[$fieldPid] == $pid) {
                $v['_level'] = $level;
                $v['_html']  = str_repeat($html, $level - 1);
                array_push($arr, $v);
                $tmp = self::_channelList(
                    $data,
                    $id,
                    $html,
                    $fieldPri,
                    $fieldPid,
                    $level + 1
                );
                $arr = array_merge($arr, $tmp);
            }
        }

        return $arr;
    }


}