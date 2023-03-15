<?php
declare(strict_types=1);

namespace SkillUp\PageBuilderExport\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use SkillUp\PageBuilderExport\Helper\Data;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var DataTest
     */
    protected $object;

    /**
     * @var string
     */
    protected $expectedName;

    protected $dir_path;

    private MockObject $context;

    private MockObject $reader;

    private MockObject $directoryList;

    public function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
        ->disableOriginalConstructor()
//        ->onlyMethods('[]')
        ->getMock();
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
//        ->onlyMethods('[]')
            ->getMock();
        $this->directoryList = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
//        ->onlyMethods('[]')
            ->getMock();
        $this->object = new Data(
            $this->context,
        $this->reader,
        $this->directoryList);
    }

    /**
     * Test to return camelcase
     *
     * @return void
     */
        public function testFunctionalize()
    {
        $this->expectedName = 'TestNameStyle';
        $name = 'test_name/style';
        $this->assertEquals($this->expectedName, $this->object->functionalize($name));
    }

    /**
     * Test to return const"UPGRADE_SCRIPT_SETUP_DIR" value
     *
     * @return void
     */
    public function testGetSetupDirConfigValue()
    {
        $this->dir_path = Data::UPGRADE_SCRIPT_SETUP_DIR;
        $this->assertEquals($this->object::UPGRADE_SCRIPT_SETUP_DIR, $this->object->getSetupDirConfigValue());
        var_dump($this->object::UPGRADE_SCRIPT_SETUP_DIR);
    }
}