<?php

namespace App\Services\Imports;

class ExcelCellHandler
{

    protected $pathClass = __DIR__ . "/FromExcelRow.php";
    protected $methodsName = [];

    public function __construct(protected array $cells)
    {
        $this->cells = $cells;
        $this->generateMethods();
    }

    public function generateMethods()
    {
        foreach ($this->cells as $key => $value) {
            if (is_string($value) && !empty($value)) {
                $methodName = $this->generateMethodName($value, $key);
                $this->addMethod($methodName, $key, $value);
            }
        }
    }

    protected function generateMethodName($cellName, $key)
    {
        $cellName = str($cellName)->after(".");
        $methodName = str($cellName)->slug()->camel()->toString();

        if ($this->hasMethod($methodName)) {
            $methodName = $methodName . $key;
        }
        return ucfirst($methodName);
    }

    protected function addMethod($methodName, $key, $comments)
    {
        if (!$this->hasMethod($methodName)) {
            $this->methodsName[$methodName] = [
                'key' => $key,
                "comments" => $comments
            ];
        }
    }

    private function hasMethod($methodName)
    {
        return array_key_exists($methodName, $this->methodsName);
    }

    public static function createClass($cells)
    {
        $static = new static($cells);
        $content = "";
        foreach ($static->methodsName as $name => $value) {
            $key = $value['key'];
            $comments = $value['comments'];
            $newLine = "\n";
            $content .= $newLine . '
                 /**
                 * ' . $comments . '
                 * @return string|null
                 */
                public function get' . $name . '(){
                    return $this->row[' . $key . '];
                }';
        }

        $content =  $static->classContent($content);

        file_put_contents($static->pathClass, $content);
    }

    private function classContent($methods)
    {
        return '<?php

                namespace App\Services\Imports;

                class FromExcelRow
                {
                    public function __construct(protected array $row)
                    {
                    }

                    ' . $methods . '

                }';
    }
}
