<?php
namespace Test\Hal\Metric\Helper;

use Hal\Metric\Helper\RoleOfMethodDetector;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\ParserFactory;

/**
 * @group method
 * @group helper
 * @group parsing
 */
class RoleOfMethodDetectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideExamples
     */
    public function testICanDetectRoleOfMethod($expected, $code)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $stmt = $parser->parse($code);

        $helper = new RoleOfMethodDetector();

        foreach ($stmt as $node) {
            if ($node instanceof Class_) {
                foreach ($node->stmts as $sub) {
                    if ($sub instanceof ClassMethod) {
                        $type = $helper->detects($sub);
                        $this->assertEquals($expected, $type);
                    }
                }
            }
        }
    }

    public function provideExamples()
    {
        $examples = [
            ['getter', '<?php class A { function getName(){ return $this->name; } }  ?>'],
            ['getter', '<?php class A { function getName(){ return (string) $this->name; } }  ?>'],
            ['getter', '<?php class A { function getName(){ return (int) $this->name; } }  ?>'],
            ['setter', '<?php class A { function setName($string){ $this->name = $name; } } ?>'],
            ['setter', '<?php class A { function setName($string){ $this->name = (string) $name; } } ?>'],
            ['setter', '<?php class A { function setName($string){ $this->name = (string) $name; return $this; } } ?>'],
            [null, '<?php class A { function foo($string){ $this->name = (string) $name * 3; } } ?>'],
        ];
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $examples[] = ['getter', '<?php class A { function getName(): string { return $this->name; } }'];
            $examples[] = ['setter', '<?php class A { function setName(string $name): void { $this->name = $name; } }'];
            $examples[] = ['getter', '<?php class A { function getName(): Name { return $this->name; } }'];
            $examples[] = ['setter', '<?php class A { function setName(Name $name): void { $this->name = $name; } }'];
        }
        return $examples;
    }
}
