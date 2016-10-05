<?php

namespace JpChronos;

use Cake\Chronos\Chronos;

class JpChronos extends Chronos
{

    protected static $toStringFormat = '{短元号}{年}(Y)/m/d';

    const START_DATE_MEIJI      = 18680125; // 明治は1868年1月25日 〜
    const START_DATE_TAISHO     = 19120730; // 大正は1912年7月30日 〜
    const START_DATE_SHOWA      = 19261225; // 昭和は1926年12月25日 〜
    const START_DATE_HEISEI     = 19890108; // 平成は1989年1月8日 〜

    const START_YEAR_MEIJI      = 1868;     // 明治は1868年1月25日 〜
    const START_YEAR_TAISHO     = 1912;     // 大正は1912年7月30日 〜
    const START_YEAR_SHOWA      = 1926;     // 昭和は1926年12月25日 〜
    const START_YEAR_HEISEI     = 1989;     // 平成は1989年1月8日 〜

    const ERA_MEIJI             = '明治';
    const ERA_TAISHO            = '大正';
    const ERA_SHOWA             = '昭和';
    const ERA_HEISEI            = '平成';

    const ERA_INITIAL_MEIJI     = 'M';
    const ERA_INITIAL_TAISHO    = 'T';
    const ERA_INITIAL_SHOWA     = 'S';
    const ERA_INITIAL_HEISEI    = 'H';

    const JP_DATA_PATTERN       = '^(明治|大正|昭和|平成)([0-9]{1,2}|元)年([0-9]{1,2})月([0-9]{1,2})日$';
    const GT_DATA_PATTERN       = '^([0-9]{4})([0-9]{2})([0-9]{2})$';

    const FORMAT_ERA            = 'era';
    const FORMAT_INITIAL        = 'initial';

    /**
     * ERA_TO_START_YEAR_OPTIONS
     * 元号 => 和暦の開始年.
     *
     * @author ito
     */
    private static $ERA_TO_START_YEAR_OPTIONS = [
        self::ERA_MEIJI         => self::START_YEAR_MEIJI,
        self::ERA_TAISHO        => self::START_YEAR_TAISHO,
        self::ERA_SHOWA         => self::START_YEAR_SHOWA,
        self::ERA_HEISEI        => self::START_YEAR_HEISEI
    ];

    /**
     * ERA_TO_ERA_INITIAL_OPTIONS
     * 元号 => 元号のイニシャルへの変更.
     *
     * @author ito
     */
    private static $ERA_TO_ERA_INITIAL_OPTIONS = [
        self::ERA_MEIJI         => self::ERA_INITIAL_MEIJI,
        self::ERA_TAISHO        => self::ERA_INITIAL_TAISHO,
        self::ERA_SHOWA         => self::ERA_INITIAL_SHOWA,
        self::ERA_HEISEI        => self::ERA_INITIAL_HEISEI
    ];

    /**
     * getEraInitialList.
     *
     * @author ito
     */
    public static function getEraInitialList()
    {
        return self::$ERA_TO_ERA_INITIAL_OPTIONS;
    }

    /**
    * __construct
    *
    * @author ito
    */
    public function __construct($time = 'now', $tz = null)
    {
        if ($time == '') {
            return '';
        }

        // タイムゾーンの設定
        if ($tz === null) {
            $tz = 'Asia/Tokyo';
        }

        if (is_object($time)) {
            $time = $this->convert2String($time);
        }

        // 和暦の場合は西暦に変換
        if ($this->isWareki($time)) {
            $time = $this->convertWarekiToPlaneSeireki($time);
        }

        parent::__construct($time, $tz);
    }

    /**
     * convert2String.
     *
     * @author ito
     */
    private function convert2String($time)
    {
        if ($time instanceof Chronos) {
            return Chronos::parse($time);
        }

        return $time;
    }

    /**
     * format.
     *
     * @author ito
     */
    public function format($format)
    {
        $formated = parent::format($format);

        return $this->warekiFormat($formated);
    }

    /**
     * isWareki.
     *
     * @author ito.
     */
    public function isWareki($time)
    {
        return preg_match('/' . self::JP_DATA_PATTERN . '/', $time);
    }

    /**
     * getWarekiParam.
     *
     * @author ito
     */
    public function getWarekiParam($time)
    {
        $match = [];
        preg_match('/' . self::JP_DATA_PATTERN . '/', $time, $match);

        return $match;
    }

    /**
     * wareki.
     *
     * @author ito
     */
    public function wareki()
    {
        $time = parent::format('Ymd');

        return $this->getWareki($time);
    }

    /**
     * warekiInitial.
     *
     * @author ito
     */
    public function warekiInitial()
    {
        $time = parent::format('Ymd');

        return $this->getWareki($time, self::FORMAT_INITIAL);
    }

    /**
     * warekiYear.
     *
     * @author ito
     */
    public function warekiYear()
    {
        $year = parent::format('Y');
        $wareki = $this->wareki();

        if ( $wareki === false ) {
            return '';
        }

        $sub = self::$ERA_TO_START_YEAR_OPTIONS[$wareki];
        $warekiYear = $year - $sub + 1;
        if ($warekiYear == 1) {
            $warekiYear = '元';
        }

        return $warekiYear;
    }

    /**
     * warekiZeroYear.
     *
     * @author ito
     */
    public function warekiZeroYear()
    {
        $warekiYear = $this->warekiYear();
        //元年以外は2桁で0埋めする{}
        if (is_numeric($warekiYear)) {
            $warekiYear = sprintf('%02d', $warekiYear);
        }

        return $warekiYear;
    }

    /**
     * warekiFormat.
     *
     * @author ito
     */
    public function warekiFormat($jPformat)
    {
        // 明治・大正・昭和・平成変換
        $jPformat = preg_replace('/{元号}/', $this->wareki(), $jPformat);

        // M・T・S・H変換
        $jPformat = preg_replace('/{短元号}/', $this->warekiInitial(), $jPformat);

        // 昭和63の「63」の変換
        $jPformat = preg_replace('/{年}/', $this->warekiYear(), $jPformat);

        // 昭和63の「63」の変換(桁ぞろえ)
        $jPformat = preg_replace('/{0年}/', $this->warekiZeroYear(), $jPformat);

        return $jPformat;
    }

    /**
     * convertWarekiToPlaneSeireki.
     *
     * @author ito
     */
    public function convertWarekiToPlaneSeireki($time)
    {
        $params = $this->getWarekiParam($time);

        // 元年を1年に修正
        if ( $params[2] == '元' ) {
            $params[2] = 1;
        }

        $year   = $params[2] + self::$ERA_TO_START_YEAR_OPTIONS[$params[1]] - 1;
        $month  = sprintf('%02d', $params[3]);
        $day    = sprintf('%02d', $params[4]);

        return (int) $year . $month . $day;
    }

    /**
     * getWareki.
     *
     * @author ito
     */
    private function getWareki($time, $format = self::FORMAT_ERA)
    {
        if (!in_array($format, [self::FORMAT_ERA, self::FORMAT_INITIAL])) {
            return false;
        }

        if ($time === null) {
            $time = $this->format('Ymd');
        }

        // 明治以前
        if ($time < self::START_DATE_MEIJI) {
            return false;
        }

        $era = '';
        // 明治
        if (
            (self::START_DATE_MEIJI <= $time) &&
            ($time < self::START_DATE_TAISHO)
        ) {
            $era = self::ERA_MEIJI;
        // 大正
        } elseif (
            (self::START_DATE_TAISHO <= $time) &&
            ($time < self::START_DATE_SHOWA)
        ) {
            $era = self::ERA_TAISHO;
        // 昭和
        } elseif (
            (self::START_DATE_SHOWA <= $time) &&
            ($time < self::START_DATE_HEISEI)
        ) {
            $era = self::ERA_SHOWA;
        // 平成
        } elseif (self::START_DATE_HEISEI <= $time) {
            $era = self::ERA_HEISEI;
        }

        switch ($format) {
            case self::FORMAT_ERA:
                return $era;
                break;
            case self::FORMAT_INITIAL:
                return self::$ERA_TO_ERA_INITIAL_OPTIONS[$era];
                break;
            default:
                break;
        }

        return false;
    }
}
