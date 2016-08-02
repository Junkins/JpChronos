<?php

namespace JpChronos\Test\TestCase;

use Cake\TestSuite\TestCase;
use JpChronos\JpChronos;

/**
 * JpChronos Test
 *
 */
class JpChronosTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

   /**
    * @dataProvider seirekiProvider
    */
    public function testFormat($sample, $format, $expected)
    {
        $now = new JpChronos($sample);
        $wareki = $now->format($format);
        $this->assertEquals($expected, $wareki);
    }

    public function seirekiProvider()
    {
        return [
            ['1868-01-24', '{短元号}{年}', ''],      // 明治以前
            ['1868-01-25', '{短元号}{年}', 'M元'],   // 明治元年
            ['1869-01-01', '{短元号}{年}', 'M2'],    // 明治2年
            ['1912-07-29', '{短元号}{年}', 'M45'],   // 明治4年
            ['1912-07-30', '{短元号}{年}', 'T元'],   // 大正元年
            ['1912-07-30', '{元号}{年}年m月d日', '大正元年07月30日'],   // 大正元年
            ['1913-01-01', '{短元号}{年}', 'T2'],    // 大正2年
            ['1926-12-24', '{短元号}{年}', 'T15'],   // 大正15年
            ['1926-12-25', '{短元号}{年}', 'S元'],   // 昭和元年
            ['昭和元年12月25日', '{短元号}{年}', 'S元'],   // 昭和64年
            ['1927-01-01', '{短元号}{年}', 'S2'],    // 昭和2年
            ['1989-01-07', '{短元号}{年}', 'S64'],   // 昭和64年
            ['1989-01-08', '{短元号}{年}', 'H元'],   // 平成元年
            ['1990-01-01', '{短元号}{年}', 'H2'],    // 平成2年
            ['2016-08-01', '{短元号}{年}', 'H28'],   // 平成28年
        ];
    }
}