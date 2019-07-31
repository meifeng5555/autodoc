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
