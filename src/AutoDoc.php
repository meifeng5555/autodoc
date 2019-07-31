<?php
/**
 * PHP 文档自动生成
 *
 * @date    2019-07-31
 * @author  meijinfeng
 */

error_reporting(E_ALL^E_NOTICE);

class AutoDoc
{
    protected static $dstFile = "doc.md";

    protected static $exclude = [];

    /**
     * 临时存储
     * @var array
     */
    protected static $temp = [];

    protected static $md = [
        'file' => "### {%file%} {%type%} {%name%}：\n",
        'desc' => "{%desc%}：<br>",
        'func' => "`function {%funcname%}({%param%}) : {%return%}` <br> \n"
    ];

    public static function run()
    {
        $data = self::parseArgs();
        $dir  = $data['dir'];
        $exclude = $data['exclude'];
        $exclude['file'] = array_merge(['.', '..'], $exclude['file']);
        self::$exclude = $exclude;

        self::recurDir($dir);
        self::saveMarkDown();
    }

    protected static function parseArgs() : array
    {
        $data = [
            'dir' => '',
            'exclude' => [
                'file' => [],
                'func' => []
            ]
        ];

        $opt = array_merge(
            [
                'd' => '',
                'dir' => '',
                'ex-file' => '',
                'ex-func' => '',
                'dst' => self::$dstFile
            ],
            getopt("d:", ['dir:', 'ex-file:', 'ex-func:', 'dst:'])
        );

        $data['dir'] = !empty($opt['d']) ? $opt['d'] : $opt['dir'];

        foreach (explode(",", $opt['ex-file']) as $file) {
            $data['exclude']['file'][] = $file;
        }

        foreach (explode(",", $opt['ex-func']) as $func) {
            $data['exclude']['func'][] = $func;
        }

        !empty($data['dir']) ?: self::trace("required -d or --dir", true);

        empty($data['dst']) ?: self::$dstFile = $data['dst'];

        return $data;
    }

    protected static function recurDir(string $dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $dirMap = scandir($dir);
        foreach ($dirMap as $name) {
            if (in_array($name, self::$exclude['file'])) {
                continue;
            }

            if (is_dir("{$dir}/{$name}")) {

                self::recurDir("{$dir}/{$name}");
            } elseif (is_file("{$dir}/{$name}")) {

                self::parseFile("{$dir}/{$name}", $name);
            }
        }
    }

    protected static function parseFile(string $file, string $name)
    {
        $match = [];
        self::$temp[$name] = [
            'type' => '',
            'name' => '',
            'func' => []
        ];
        $content = file_get_contents($file);
        preg_match_all('/\/\*(\s|.)*?\*\//', $content, $match);

        foreach ((array)$match[0] as $index => $segment) {

            $segment = trim(str_replace(["/", "*"], "", $segment));
            $lineMap = explode("\n", $segment);

            foreach ($lineMap as $linen => $line) {
                if (empty($line)) {
                    continue;
                }

                if (strstr($line, "@Class") !== false) {

                    self::$temp[$name]['type'] = "Class";
                    self::$temp[$name]['name'] = trim(str_replace("@Class", "", $line));
                } elseif (strstr($line, "@Trait") !== false) {

                    self::$temp[$name]['type'] = "Trait";
                    self::$temp[$name]['name'] = trim(str_replace("@Trait", "", $line));
                } elseif (strstr($line, "@name") !== false) {

                    self::$temp[$name]['func'][$index]['name'] = trim(str_replace("@name", "", $line));
                } elseif (strstr($line, "@param") !== false) {

                    // 去掉多余的空格
                    $line = preg_replace('/\s(?=\s)/', "\\1", $line);
                    $line = trim(str_replace("@param", "", $line));

                    // explode(" ", $line)
                    // 0 类型
                    // 1 参数名
                    // 2 参数描述
                    $param = explode(chr(32), $line);
                    $paramName = $param[1];
                    self::$temp[$name]['func'][$index]['param'][$paramName]['type'] = $param[0];
                    self::$temp[$name]['func'][$index]['param'][$paramName]['name'] = $param[1];
                    self::$temp[$name]['func'][$index]['param'][$paramName]['desc'] = $param[2];
                } elseif (strstr($line, "@return") !== false) {

                    self::$temp[$name]['func'][$index]['return'] = trim(str_replace("@return", "", $line));
                } elseif (strstr($line, "@throws") !== false) {

                    self::$temp[$name]['func'][$index]['throws'] = trim(str_replace("@throws", "", $line));
                } elseif (strstr($line, "@desc") !== false) {

                    self::$temp[$name]['func'][$index]['desc'] = trim(str_replace("@desc", "", $line));
                } else {

                    // 首行注释,且没有其他标识
                    // 则判定为方法的描述
                    if ($linen == 0) {
                        self::$temp[$name]['func'][$index]['desc'] = trim($line);
                    }
                }
            }
        }
    }

    protected static function saveMarkDown()
    {
        $markDown = "";

        foreach (self::$temp as $file => $source) {

            $mdFile = str_replace([
                "{%file%}",
                "{%type%}",
                "{%name%}",
            ], [
                $file,
                $source['type'],
                $source['name']
            ], self::$md['file']);

            if (!isset($source['func'])) {
                continue;
            }

            $markDown .= "{$mdFile} \n";

            foreach ((array)$source['func'] as $funcSource) {

                $mdDesc = str_replace([
                    "{%desc%}",
                ], [
                    $funcSource['desc']
                ], self::$md['desc']);

                $mdFunc = str_replace([
                    "{%funcname%}",
                    "{%return%}",
                ], [
                    $funcSource['name'],
                    $funcSource['return']
                ], self::$md['func']);

                if (isset($funcSource['param'])) {

                    $param = "";
                    foreach ((array)$funcSource['param'] as $paramResource) {

                        !empty($param) ?
                            $param .= ", {$paramResource['type']} {$paramResource['name']}" :
                            $param = "{$paramResource['type']} {$paramResource['name']}";
                    }

                    $mdFunc = str_replace([
                        "{%param%}"
                    ], [
                        $param
                    ], $mdFunc);
                } else {

                    $mdFunc = str_replace([
                        "{%param%}"
                    ], [
                        ""
                    ], $mdFunc);
                }

                $markDown .= "{$mdDesc} {$mdFunc}";
            }
        }

        file_put_contents(
            self::$dstFile,
            $markDown
        );
    }

    protected static function saveArray()
    {
        file_put_contents(
            self::$dstFile,
            "```php\n".print_r(self::$temp, true)."\n```"
        );
    }

    protected static function trace($info, bool $finish = false)
    {
        if (is_array($info)
            || is_object($info)) {

            print_r($info);
        } else {

            echo "{$info}".PHP_EOL;
        }

        !$finish ?: exit(0);
    }
}

AutoDoc::run();
