<?php
if (!function_exists('prefilter_trans')) {
    /**
     * 템플릿의 번역 함수(trans)를 지정된 언어 템플릿으로 변경
     * @param string $source
     * @param string $tpl
     * @param string $language
     * @return string
     */
    function prefilter_scmtrans($source, $tpl, $language)
    {
        preg_match_all("|{=trans\((.*)\)}|U", $source, $match, PREG_PATTERN_ORDER);
        list($target, $arguments) = $match;
        if (count($arguments) > 0) {
            $cacheLangPath = CUSTOM_ROOT.'/_cache/_language/'. $language . ".php";

            if (defined('USE_SHARED_CACHE') && USE_SHARED_CACHE === true && file_exists($cacheLangPath)) {
                $languageFilePath = $cacheLangPath;
            } else {
                $languageFilePath = MALL_DATA_PATH . "/_language/" . $language . ".php";
            }

            $languageData = array();
            if (file_exists($languageFilePath)) {
                $languageData = include_once($languageFilePath);
            }

            $basicReplace = array("'", '"');
            $transStrs    = array();
            $transKeys    = array();
            foreach ($arguments as $key => $basicStr) {
                $basicStr = trim($basicStr);
                $startStr = substr($basicStr, 0, 1);
                if (in_array($startStr, $basicReplace)) {
                    $basicStr = substr($basicStr, 1);
                }
                $endStr = substr($basicStr, -1);
                if (in_array($endStr, $basicReplace)) {
                    $basicStr = substr($basicStr, 0, -1);
                }

                $transKey = md5($basicStr);
                $transStr = $languageData[$transKey] ?? $basicStr;

                $transStrs[] = $transStr;
                $transKeys[] = $transKey;

                //TODO: transKey 체크해서 언어 등록 처리
            }

            if (constant('ENVIRONMENT') == 'development') {
                //TODO: 개발일때 체크해서 언어 등록 처리
            }

            return str_replace($target, $transStrs, $source);
        } else {
            return $source;
        }
    }
}
