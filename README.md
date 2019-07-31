# autodoc
通过注释生成文档

## 安装
下载源码即可

## 使用
```shell
$ php AutoDoc.php --dir "/data/project/src"
```

## 参数说明
```shell
 --dir|-d    required   项目目录
 --ex-file   optional   不包含的文件，多个文件用","分开（例如 a.php，就会过滤项目目录下所有a.php的文件）
 --ex-func   optional   不包含的方法，多个方法用","分开 (例如 toArray，就会过滤项目文件下所有toArray()方法)
 --dst       optional   指定生成文件路径及文件名，默认生成在当前运行目录
```

## 注释格式说明
以php为例
```php

    /**
     * 方法1
     * @param string $a
     * @return array
     */
    public function fn(string $a) : array
    {

    }

    /**
     * 方法2
     * @param string $a
     * @param string $b
     * @return array
     */
    public function fn2(string $a, string $b) : array
    {

    }
```
生成实例

### test.php Class TestFor-AutoDoc：
 
方法1：<br> `function (string $a) : array` <br> 
方法2：<br> `function (string $a, string $b) : array` <br> 

