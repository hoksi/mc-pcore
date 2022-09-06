<?php
if (!function_exists('prefilter_trans')) {

    /**
     * 템플릿의 번역 함수(trans)를 지정된 언어 템플릿으로 변경
     * @param string $source
     * @param string $tpl
     * @param string $language
     * @return string
     */
    function prefilter_trans($source, $tpl, $language)
    {
        preg_match_all('/{=trans\([\'|"](.*)[\'|"]\)}/U', $source, $match, PREG_PATTERN_ORDER);
        list($target, $arguments) = $match;
        if (count($arguments) > 0) {
            if (defined('DB_CONNECTION_DIV') && DB_CONNECTION_DIV == 'development') {
                //개발일때 언어관련 모델 로드
                /* @var $langModel CustomMallLanguagetModel */
                $langModel = getForbiz()->import('model.mall.language');
            }

            if (defined('FORBIZ_MALL_VERSION')) {
                $cacheLanguageFilePath = CUSTOM_ROOT.'/_cache/_language/'.$language.".php";
            }

            if (defined('FORBIZ_MALL_VERSION') && defined('USE_SHARED_CACHE') && USE_SHARED_CACHE === true && file_exists($cacheLanguageFilePath)) {
                $languageFilePath = $cacheLanguageFilePath;
            } else {
                $languageFilePath = MALL_DATA_PATH."/_language/".$language.".php";
            }

            if (file_exists($languageFilePath)) {
                $languageData = include($languageFilePath);
            } else {
                $languageData = [];
            }

            $transStrs = [];
            $transKeys = [];
            foreach ($arguments as $key => $basicStr) {
                $basicStr = trim($basicStr);
                $transKey = md5($basicStr);
                if (!empty($languageData[$transKey])) {
                    $transStr = $languageData[$transKey];
                } else {
                    $transStr = $basicStr;

                    if (defined('DB_CONNECTION_DIV') && DB_CONNECTION_DIV == 'development') {
                        //개발일때 등록
                        $langModel->addTranLang($transKey, $basicStr);
                    }
                }

                $transStrs[] = $transStr;
                $transKeys[] = $transKey;
            }

            return str_replace($target, $transStrs, $source);
        } else {
            return $source;
        }
    }
}
