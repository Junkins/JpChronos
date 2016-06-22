<?php

namespace JpChronos;

use Cake\Chronos\Chronos;

class JpChronos extends Chronos
{

    protected static $toStringFormat = '{短元号}{年}(Y)/m/d';

    const START_DATE_MEIJI      = 18680125; // 明治は1868年1月25日 〜
    const START_DATE_TAISHO     = 19120730; // 大正は1912年7月30日 〜
    const START_DATE_SHOWA      = 19261225; // 昭和は1989年12月25日 〜
    const START_DATE_HEISEI     = 19890108; // 平成は1989年1月8日 〜

    const START_YEAR_MEIJI      = 1868;     // 明治は1868年1月25日 〜
    const START_YEAR_TAISHO     = 1911;     // 大正は1912年7月30日 〜
    const START_YEAR_SHOWA      = 1925;     // 昭和は1989年12月25日 〜
    const START_YEAR_HEISEI     = 1988;     // 平成は1989年1月8日 〜

    const ERA_MEIJI             = '明治';
    const ERA_TAISHO            = '大正';
    const ERA_SHOWA             = '昭和';
    const ERA_HEISEI            = '平成';

    const ERA_INITIAL_MEIJI     = 'M';
    const ERA_INITIAL_TAISHO    = 'T';
    const ERA_INITIAL_SHOWA     = 'S';
    const ERA_INITIAL_HEISEI    = 'H';

    const JP_DATA_PATTERN       = '^(明治|大正|昭和|平成)([0-9]{1,2})年([0-9]{1,2})月([0-9]{1,2})日$';
    const GT_DATA_PATTERN       = '^([0-9]{4})([0-9]{2})([0-9]{2})$';

    /**
    * ERA_TO_START_YEAR_OPTIONS
    * 元号 => 和暦の開始年
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
    * 元号 => 元号のイニシャルへの変更
    * @author ito
    */
    private static $ERA_TO_ERA_INITIAL_OPTIONS = [
        self::ERA_MEIJI         => self::ERA_INITIAL_MEIJI,
        self::ERA_TAISHO        => self::ERA_INITIAL_TAISHO,
        self::ERA_SHOWA         => self::ERA_INITIAL_SHOWA,
        self::ERA_HEISEI        => self::ERA_INITIAL_HEISEI
    ];

    /**
    * __construct
    *
    * @author ito
    */
    public function __construct($time = 'now', $tz = null)
    {
        // タイムゾーンの設定
        if ($tz === null) {
            $tz = 'Asia/Tokyo';
        }

        // 和暦の場合は西暦に変換
        if ($this->isWareki($time)) {
            $time = $this->convertWarekiToPlaneSeireki($time);
        }

        parent::__construct($time, $tz);
    }

    /**
    * format
    * @author ito
    */
    public function format($format)
    {
        $formated = parent::format($format);
        return $this->warekiFormat($formated);
    }

    /**
    * isWareki
    *
    */
    public function isWareki($time)
    {
        return preg_match('/' . self::JP_DATA_PATTERN . '/', $time);
    }

    /**
    * getWarekiParam
    *
    */
    public function getWarekiParam($time)
    {
        $match = [];
        preg_match('/' . self::JP_DATA_PATTERN . '/', $time, $match);
        return $match;
    }

    /**
    * wareki
    *
    */
    public function wareki()
    {
        $time = parent::format('Ymd');
        return $this->getWareki($time);
    }

    /**
    * warekiInitial
    *
    */
    public function warekiInitial()
    {
        $time = parent::format('Ymd');
        return $this->getWareki($time, 'initial');
    }

    /**
    * warekiYear
    *
    */
    public function warekiYear()
    {
        $year = parent::format('Y');
        $sub = self::$ERA_TO_START_YEAR_OPTIONS[$this->wareki()];
        $warekiYear = $year - $sub;
        if ($warekiYear == 1) {
            $warekiYear = '元';
        }
        return $warekiYear;
    }

    /**
    * warekiFormat
    *
    */
    public function warekiFormat($jPformat)
    {
        // 明治・大正・昭和・平成変換
        $jPformat = preg_replace('/{元号}/', $this->wareki(), $jPformat);

        // M・T・S・H変換
        $jPformat = preg_replace('/{短元号}/', $this->warekiInitial(), $jPformat);

        // 昭和63の「63」の変換
        $jPformat = preg_replace('/{年}/', $this->warekiYear(), $jPformat);

        return $jPformat;
    }

    /**
    * convertWarekiToPlaneSeireki
    *
    */
    public function convertWarekiToPlaneSeireki($time)
    {
        $params = $this->getWarekiParam($time);
        $year   = $params[2] + self::$ERA_TO_START_YEAR_OPTIONS[$params[1]];
        $month  = sprintf('%02d', $params[3]);
        $day    = sprintf('%02d', $params[4]);
        return (int) $year . $month . $day;
    }

    /**
    * getWareki
    *
    */
    private function getWareki($time, $format = 'era')
    {
        if (!in_array($format, ['era', 'initial'])) {
            return false;
        }

        if ($time === null) {
            $time = $this->format('Ymd');
        }

        // 明治以前
        if ($time < self::START_DATE_MEIJI) {
            return false;
        }

        // 明治
        if (
            (self::START_DATE_MEIJI <= $time) &&
            ($time < self::START_DATE_TAISHO)
        ) {
            switch ($format) {
                case 'era':
                    return self::ERA_MEIJI;
                    break;
                case 'initial':
                    return self::ERA_INITIAL_MEIJI;
                    break;
                default:
                    break;
            }
        }

        // 大正
        if (
            (self::START_DATE_TAISHO <= $time) &&
            ($time < self::START_DATE_SHOWA)
        ) {
            switch ($format) {
                case 'era':
                    return self::ERA_TAISHO;
                    break;
                case 'initial':
                    return self::ERA_INITIAL_TAISHO;
                    break;
                default:
                    break;
            }
        }

        // 昭和
        if (
            (self::START_DATE_SHOWA <= $time) &&
            ($time < self::START_DATE_HEISEI)
        ) {
            switch ($format) {
                case 'era':
                    return self::ERA_SHOWA;
                    break;
                case 'initial':
                    return self::ERA_INITIAL_SHOWA;
                    break;
                default:
                    break;
            }
        }

        // 平成
        if (self::START_DATE_HEISEI <= $time) {
            switch ($format) {
                case 'era':
                    return self::ERA_HEISEI;
                    break;
                case 'initial':
                    return self::ERA_INITIAL_HEISEI;
                    break;
                default:
                    break;
            }
        }

        return false;
    }
}
