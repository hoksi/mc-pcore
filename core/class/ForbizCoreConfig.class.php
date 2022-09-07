<?php

/**
 * 설정 데이타 클래스
 *
 * @author hoksi
 */
class ForbizCoreConfig
{

    /**
     * 회사 정보
     * @staticvar array $data
     * @param string $key
     * @return string or array
     */
    public static function getCompanyInfo($key = false)
    {
        static $data = [];

        if (defined('MALL_DATA_PATH')) {
            if (defined('FORBIZ_MALL_VERSION')) {
                $cacheFilePath = CUSTOM_ROOT.'/_cache/_config/';
            }

            $companyFile    = 'company.php';
            $configFilePath = false;

            if (defined('FORBIZ_MALL_VERSION') && defined('USE_SHARED_CACHE') && USE_SHARED_CACHE === true && file_exists($cacheFilePath.$companyFile)) {
                $data = require($cacheFilePath.$companyFile);
            } else {
                $configFilePath = MALL_DATA_PATH.'/_config/'.$companyFile;
            }

            if (empty($data) && $configFilePath && file_exists($configFilePath)) {
                $data = require($configFilePath);
            }
        }

        if ($key) {
            return $data[$key] ?? false;
        } else {
            return $data;
        }
    }

    public static function getAssign($key = false)
    {
        static $data = [];

        if (defined('FORBIZ_MALL_VERSION')) {
            if (empty($data) && file_exists(CUSTOM_ROOT.'/config/assign.php')) {
                $data = require(CUSTOM_ROOT.'/config/assign.php');

                // layoutCommon
                $data['layoutCommon']['templetSrc'] = ($data['layoutCommon']['templetSrc'] ?? TPL_ROOT);
                $data['layoutCommon']['productSrc'] = ($data['layoutCommon']['productSrc'] ?? (DATA_ROOT."/images/product"));
                $data['layoutCommon']['imagesSrc']  = ($data['layoutCommon']['imagesSrc'] ?? (DATA_ROOT."/images/images"));
                $data['layoutCommon']['dataRoot']   = ($data['layoutCommon']['dataRoot'] ?? DATA_ROOT);

                $pathList = explode("/", getForbiz()->router->routeUri);
                if (isset($pathList[1])) {
                    list($Group, $Page) = $pathList;
                } else if (isset($pathList[0]) && $pathList[0]) {
                    $Group = $pathList[0];
                    $Page  = 'index';
                } else {
                    // main index
                    $Group = 'main';
                    $Page  = 'index';
                }

                // layout
                $data['layout']['GroupCssSrc']   = ($data['layout']['GroupCssSrc'] ?? (TPL_ROOT.'/assets/css/'.$Group.'.css'));
                $data['layout']['GroupJsSrc']    = ($data['layout']['GroupJsSrc'] ?? (TPL_ROOT.'/js/'.$Group.'/common_'.$Group.'.js'));
                $data['layout']['PageJsSrc']     = ($data['layout']['PageJsSrc'] ?? (TPL_ROOT.'/js/'.$Group.'/'.$Page.'.js'));
                $data['layout']['LanguageJsSrc'] = ($data['layout']['LanguageJsSrc'] ?? (DATA_ROOT.'/_language/'.LANGUAGE.'.js'));

                // 업체 정보
                $data['companyInfo'] = self::getCompanyInfo();
            }
        }

        if ($key) {
            return [$key => ($data[$key] ?? '')];
        } else {
            return $data;
        }
    }

    public static function getConfig($key)
    {
        static $data = [];

        if (defined('FORBIZ_MALL_VERSION')) {
            if (!isset($data[$key]) && file_exists(CUSTOM_ROOT."/config/{$key}.php")) {
                $data[$key] = require(CUSTOM_ROOT."/config/{$key}.php");
            }
        }

        return ($data[$key] ?? false);
    }

    public static function getPrivacyConfig($key)
    {
        static $data = null;

        if ($data === null) {
            $row = getForbiz()->qb
                ->select('config_name')
                ->select('config_value')
                ->from(TBL_SHOP_MALL_PRIVACY_SETTING)
                ->where('mall_ix', MALL_IX)
                ->exec()
                ->getResultArray();

            $data = [];
            foreach ($row as $item) {
                switch ($item['config_name']) {
                    case 'pw_combi':
                    case 'pw_continuous_check':
                    case 'pw_same_check':
                        $data[$item['config_name']] = json_decode($item['config_value'], true);
                        break;
                    default:
                        $data[$item['config_name']] = $item['config_value'];
                        break;
                }
            }
        }

        return ($data[$key] ?? '');
    }

    public static function getShopInfo($key)
    {
        static $data = [];
        if (!isset($data[$key])) {
            $data = getForbiz()->qb
                ->from(TBL_SHOP_SHOPINFO)
                ->where('mall_ix', MALL_IX)
                ->limit(1)
                ->exec()
                ->getRowArray();
        }

        return ($data[$key] ?? '');
    }

    public static function getAdminImage($upload_type, $sub_type, $select = 'url')
    {
        return $row = getForbiz()->qb
            ->select($select)
            ->from(TBL_SYSTEM_UPLOAD_FILE)
            ->where('upload_type', $upload_type)
            ->where('sub_type', $sub_type)
            ->orderBy('system_upload_file_id', 'DESC')
            ->limit(1)
            ->exec()
            ->getRowArray();
    }

    public static function getPaymentConfig($key, $pg)
    {
        static $data = [];

        if (!isset($data[$key])) {
            $row = getForbiz()->qb
                ->select('config_value')
                ->from(TBL_SHOP_PAYMENT_CONFIG)
                ->where('mall_ix', MALL_IX)
                ->where('pg_code', $pg)
                ->where('config_name', $key)
                ->limit(1)
                ->exec()
                ->getRow();

            $data[$key] = ($row->config_value ?? '');
        }

        return $data[$key];
    }

    public static function getOldSharedMemory($name, $subName = false)
    {
        static $result = [];

        if ($subName === false) {
            $subName = $name;
        }

        $sharedId = sprintf('%s_%s', $name, $subName);

        if (isset($result[$sharedId]) === false) {
            $cacheSharedPath = CUSTOM_ROOT.'/_cache/_shared/';

            if (defined('USE_SHARED_CACHE') && USE_SHARED_CACHE === true && file_exists($cacheSharedPath.'/'.$name)) {
                $sharedPath = $cacheSharedPath;
            } else {
                $sharedPath = MALL_DATA_PATH.'/_shared/';
            }

            $shmop           = new \Shared($name);
            $shmop->filepath = $sharedPath;
            $shmop->SetFilePath();
            $data            = $shmop->getObjectForKey($subName);

            if (!empty($data)) {
                if (is_array($data)) {
                    $result[$sharedId] = $data;
                } else {
                    $data              = urldecode($data);
                    $result[$sharedId] = @unserialize($data);
                }
            } else {
                $result[$sharedId] = null;
            }
        }

        return $result[$sharedId];
    }

    public static function setOldSharedMemory($name, $obj, $subName = false)
    {
        if ($subName === false) {
            $subName = $name;
        }
        $shmop           = new \Shared($name);
        $shmop->filepath = MALL_DATA_PATH."/_shared/";
        $shmop->SetFilePath();

        return $shmop->setObjectForKey($obj, $subName);
    }

    public static function getSharedMemory($name, $subName = false)
    {
        static $result = [];

        if ($subName === false) {
            $subName = $name;
        }

        $config_name = sprintf('shared_%s_%s', $name, $subName);

        // 캐시 사용 여부 확인
        if (defined('USE_SHARED_CACHE') && USE_SHARED_CACHE === true) {
            $cacheId              = '_shared/'.MALL_IX.'/'.$config_name;
            $result[$config_name] = fb_get($cacheId);
        }

        if (empty($result[$config_name])) {
            $qb = getForbiz()->qb;

            $row = $qb->select('config_value')
                ->from(TBL_SHOP_MALL_CONFIG)
                ->where('mall_ix', MALL_IX)
                ->where('config_name', $config_name)
                ->exec()
                ->getRowArray();


            $result[$config_name] = isset($row['config_value']) ? json_decode($row['config_value'], true) : '';
        }

        return $result[$config_name];
    }

    public static function setSharedMemory($name, $obj, $subName = false)
    {
        if ($subName === false) {
            $subName = $name;
        }

        $qb          = getForbiz()->qb;
        $config_name = sprintf('shared_%s_%s', $name, $subName);
        $config_data = json_encode($obj, JSON_UNESCAPED_UNICODE);

        $cnt = $qb->from(TBL_SHOP_MALL_CONFIG)
            ->where('mall_ix', MALL_IX)
            ->where('config_name', $config_name)
            ->getCount();

        if ($cnt > 0) {
            $ret = $qb->set('config_value', $config_data)
                ->where('mall_ix', MALL_IX)
                ->where('config_name', $config_name)
                ->update(TBL_SHOP_MALL_CONFIG)
                ->exec();
        } else {
            $ret = $qb->set('mall_ix', MALL_IX)
                ->set('config_name', $config_name)
                ->set('config_value', $config_data)
                ->insert(TBL_SHOP_MALL_CONFIG)
                ->exec();
        }

        return $ret;
    }

    public static function getChangePasswordSession()
    {
        return sess_val('user', 'changeAccessPassword');
    }

    public static function findKey($data, $key, $sort = false)
    {
        if ($key !== false) {
            foreach ($data as $k => $v) {
                if ($k == $key) {
                    return $v;
                } elseif ($v == $key) {
                    return $k;
                }
            }

            return '';
        }

        if ($sort) {
            asort($data);
        }

        return $data;
    }

    public static function getParser()
    {
        getForbiz()->load->library('parser');

        return getForbiz()->parser;
    }

    public static function getSmsTpl($key)
    {
        static $data = [];

        if (!isset($data[$key])) {
            $row = getForbiz()->qb
                ->select('mc_sms_text')
                ->select('kakao_alim_talk_template_code as kakao_code')
                ->select('mc_sms_usersend_yn as send_yn')
                ->from(TBL_SHOP_MAILSEND_CONFIG)
                ->where('mc_code', $key)
                ->limit(1)
                ->exec()
                ->getRowArray();

            if (isset($row['mc_sms_text'])) {
                $data[$key] = $row;
            } else {
                $data[$key] = [
                    'mc_sms_text' => ''
                    , 'kakao_code' => ''
                    , 'send_yn' => 'N'
                ];
            }
        }

        return $data[$key];
    }

    public static function getBankList($key = false)
    {
        $data = [
            "su" => "산업은행", "ku" => "기업은행", "km" => "국민은행", "yh" => "KEB하나은행",
            "ss" => "수협중앙회", "nh" => "농협중앙회", "nh2" => "단위농협",
            "wr" => "우리은행", "sh" => "신한은행",
            "sc" => "SC제일은행", "hn" => "하나은행", "hc" => "한국씨티은행",
            "dk" => "대구은행", "bs" => "부산은행", "kj" => "광주은행", "jj" => "제주은행",
            "jb" => "전북은행", "kn" => "경남은행",
            "po" => "우체국",
            "sl" => "산림조합중앙회", "sk" => "새마을금고중앙회", "sn" => "신협",
            "sj" => "저축은행", "hsbc" => "HSBC", "kko" => "카카오뱅크", "kbk" => "케이뱅크"
        ];

        return self::findKey($data, $key, true);
    }

    public static function getBankCode($key = false)
    {
        $data = [
            "su" => "002", "ku" => "003", "km" => "004", "yh" => "081",
            "ss" => "007", "nh" => "011", "nh2" => "012",
            "wr" => "020", "sh" => "088",
            "sc" => "023", "hn" => "081", "hc" => "027",
            "dk" => "031", "bs" => "032", "kj" => "034", "jj" => "035",
            "jb" => "037", "kn" => "039",
            "po" => "071",
            "sl" => "064", "sk" => "045", "sn" => "048",
            "sj" => "050", "hsbc" => "054", "kko" => "090", "kbk" => "089"
        ];

        return self::findKey($data, $key, true);
    }

    public static function getOrderStatus($key = false)
    {
        $data = [
            //입금
            ORDER_STATUS_SETTLE_READY => '결제중',
            ORDER_STATUS_REPAY_READY => '재 결제 대기중',
            ORDER_STATUS_INCOM_READY => '입금예정',
            ORDER_STATUS_INCOM_COMPLETE => '입금확인',
            ORDER_STATUS_DEFERRED_PAYMENT => '후불(외상)',
            ORDER_STATUS_LOSS => '손실',
            //취소
            ORDER_STATUS_CANCEL_APPLY => '취소요청',
            ORDER_STATUS_INCOM_BEFORE_CANCEL_COMPLETE => '입금 전 취소',
            ORDER_STATUS_CANCEL_COMPLETE => '취소완료',
            ORDER_STATUS_CANCEL_DENY => '취소거부',
            ORDER_STATUS_CANCEL_ING => '취소처리중',
            //환불
            ORDER_STATUS_REFUND_READY => '환불대기',
            ORDER_STATUS_REFUND_APPLY => '환불요청',
            ORDER_STATUS_REFUND_COMPLETE => '환불완료',
            //출고
            ORDER_STATUS_WAREHOUSING_STANDYBY => '입고대기',
            ORDER_STATUS_WAREHOUSE_DELIVERY_APPLY => '출고요청',
            ORDER_STATUS_WAREHOUSE_DELIVERY_CONFIRM => '출고요청 확정',
            ORDER_STATUS_WAREHOUSE_DELIVERY_PICKING => '포장대기',
            ORDER_STATUS_WAREHOUSE_DELIVERY_READY => '출고대기',
            ORDER_STATUS_WAREHOUSE_DELIVERY_COMPLETE => '출고완료',
            //배송
            ORDER_STATUS_DELIVERY_READY => '배송준비중',
            ORDER_STATUS_DELIVERY_DELAY => '배송지연',
            ORDER_STATUS_DELIVERY_ING => '배송중',
            ORDER_STATUS_DELIVERY_COMPLETE => '배송완료',
            ORDER_STATUS_BUY_FINALIZED => '구매확정',
            ORDER_UNRECEIVED_CLAIM => '미수령 신고 접수',
            ORDER_UNRECEIVED_CLAIM_COMPLETE => '미수령 신고 철회',
            //교환
            ORDER_STATUS_EXCHANGE_APPLY => '교환요청',
            ORDER_STATUS_EXCHANGE_DENY => '교환거부',
            ORDER_STATUS_EXCHANGE_ING => '교환승인',
            ORDER_STATUS_EXCHANGE_READY => '교환예정',
            ORDER_STATUS_EXCHANGE_DELIVERY => '교환상품 배송중',
            ORDER_STATUS_EXCHANGE_ACCEPT => '교환회수 완료',
            ORDER_STATUS_EXCHANGE_DEFER => '교환보류',
            ORDER_STATUS_EXCHANGE_IMPOSSIBLE => '교환불가',
            ORDER_STATUS_EXCHANGE_COMPLETE => '교환반품 확정',
            ORDER_STATUS_EXCHANGE_CANCEL => '교환신청 취소',
            ORDER_STATUS_EXCHANGE_AGAIN_DELIVERY => '교환 재 배송중',
            //반품
            ORDER_STATUS_RETURN_APPLY => '반품요청',
            ORDER_STATUS_RETURN_ING => '반품승인',
            ORDER_STATUS_RETURN_DELIVERY => '반품상품 배송중',
            ORDER_STATUS_RETURN_COMPLETE => '반품확정',
            ORDER_STATUS_RETURN_ACCEPT => '반품회수 완료',
            ORDER_STATUS_RETURN_CANCEL => '반품취소',
            ORDER_STATUS_RETURN_DEFER => '반품보류',
            ORDER_STATUS_RETURN_DENY => '반품거부',
            ORDER_STATUS_RETURN_DENY_DEFER => '반품완료 보류',
            ORDER_STATUS_RETURN_IMPOSSIBLE => '반품불가',
            //정산
            ORDER_STATUS_ACCOUNT_APPLY => '정산요청',
            ORDER_STATUS_ACCOUNT_READY => '정산대기',
            ORDER_STATUS_ACCOUNT_COMPLETE => '정산완료',
            ORDER_STATUS_ACCOUNT_PAYMENT => '정산지급 완료',
            //해외배송
            ORDER_STATUS_OVERSEA_WAREHOUSE_DELIVERY_READY => '해외 프로세싱중',
            ORDER_STATUS_OVERSEA_WAREHOUSE_DELIVERY_ING => '해외 창고배송중',
            ORDER_STATUS_AIR_TRANSPORT_READY => '항공 배송준비중',
            ORDER_STATUS_AIR_TRANSPORT_ING => '항공 배송중',
        ];

        return self::findKey($data, $key);
    }

    public static function getPaymentMethod($key = false)
    {
        $data = [
            ORDER_METHOD_BANK => '무통장',
            ORDER_METHOD_CARD => '카드',
            ORDER_METHOD_PHONE => '휴대폰결제',
            ORDER_METHOD_AFTER => '사용안함',
            ORDER_METHOD_VBANK => '가상계좌',
            ORDER_METHOD_ICHE => '실시간계좌이체',
            ORDER_METHOD_MOBILE => '모바일결제',
            ORDER_METHOD_NOPAY => '무료결제',
            ORDER_METHOD_ASCROW => '에스크로',
            ORDER_METHOD_CASH => '현금',
            ORDER_METHOD_BOX_ENCLOSE => '박스동봉',
            ORDER_METHOD_SAVEPRICE => '예치금',
            ORDER_METHOD_RESERVE => '적립금',
            ORDER_METHOD_CART_COUPON => '장바구니쿠폰',
            ORDER_METHOD_DELIVERY_COUPON => '배송비쿠폰',
            ORDER_METHOD_PAYCO => '페이코',
            ORDER_METHOD_PAYPAL => '페이팔',
            ORDER_METHOD_KAKAOPAY => '카카오페이',
            ORDER_METHOD_NPAY => '네이버페이',
            ORDER_METHOD_SSPAY => '삼성페이',
            ORDER_METHOD_EXIMBAY => 'EXIMBAY',
            ORDER_METHOD_TOSS => '토스',
            ORDER_METHOD_SKPAY => 'SKPAY',
            ORDER_METHOD_WECHATPAY => '위쳇페이',
            ORDER_METHOD_ALIPAY => '알리페이',
            ORDER_METHOD_INAPP_PAYCO => 'PG(페이코)',
            ORDER_METHOD_INAPP_KAKAOPAY => 'PG(카카오)',
            ORDER_METHOD_INAPP_SSPAY => 'PG(삼성페이)',
            ORDER_METHOD_INAPP_SSGPAY => 'PG(쓱페이)',
            ORDER_METHOD_INAPP_TOSS => 'PG(토스)',
            ORDER_METHOD_INAPP_LPAY => 'PG(엘페이)',
            ORDER_METHOD_INAPP_SKPAY => 'PG(SK페이)',
            ORDER_METHOD_INAPP_NAVERPAY => 'PG(네이버페이)',
            ORDER_METHOD_INAPP_KPAY => 'PG(케이페이)',
            ORDER_METHOD_INAPP_KBANKPAY => 'PG(케이뱅크페이)',
            ORDER_METHOD_ERROR => '미정의타입',
            ORDER_METHOD_ESCROW_VBANK => '가상계좌',
            ORDER_METHOD_ESCROW_ICHE => '실시간계좌이체'
        ];

        return self::findKey($data, $key);
    }

    public static function getDiscount($key = false)
    {
        $data = [
            'IN' => '즉시할인'
            , 'MG' => '회원할인'
            , 'GP' => '기획할인'
            , 'SP' => '특별할인'
            , 'M' => '모바일할인'
            , 'APP' => '앱할인'
            , 'CP' => '쿠폰할인'
            , 'E' => '임직원할인'
            , 'P' => '제휴사할인'
        ];

        return self::findKey($data, $key);
    }

    public static function getOrderSelectStatus($type, $fkey, $skey, $code = false, $key = false)
    {
        $data = [
            "F" => [
                ORDER_STATUS_INCOM_READY => [
                    //입금예정->취소요청(프론트)
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "N", "title" => "다른상품구매"],
                        "NB" => ["type" => "N", "title" => "구매의사없음"],
                        "PIE" => ["type" => "N", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "N", "title" => "기타(구매자책임)"]
                    ],
                    //입금예정->취소완료(프론트)
                    ORDER_STATUS_INCOM_BEFORE_CANCEL_COMPLETE => [
                        "DPB" => ["type" => "N", "title" => "다른상품구매"],
                        "NB" => ["type" => "N", "title" => "구매의사없음"],
                        "PIE" => ["type" => "N", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "N", "title" => "기타(구매자책임)"]
                    ]
                ],
                ORDER_STATUS_INCOM_COMPLETE => [
                    //입금완료->취소요청(프론트)
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "B", "title" => "다른상품구매"],
                        "NB" => ["type" => "B", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "S", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ],
                    //입금완료->취소완료(프론트)
                    ORDER_STATUS_CANCEL_COMPLETE => [
                        "DPB" => ["type" => "B", "title" => "다른상품구매"],
                        "NB" => ["type" => "B", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "S", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ]
                ],
                //발주확인->취소요청(프론트)
                ORDER_STATUS_DELIVERY_READY => [
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "B", "title" => "다른상품구매"],
                        "NB" => ["type" => "B", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "S", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ]
                ],
                //배송지연->취소신청(프론트)
                ORDER_STATUS_DELIVERY_DELAY => [
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "B", "title" => "다른상품구매"],
                        "NB" => ["type" => "B", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "S", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ]
                ],
                ORDER_STATUS_DELIVERY_COMPLETE => [
                    //배송완료->교환요청(프론트)
                    ORDER_STATUS_EXCHANGE_APPLY => [
                        "OCF" => ["type" => "B", "title" => "사이즈,색상잘못선택"],
                        "PD" => ["type" => "S", "title" => "배송상품 파손/하자"],
                        "DE" => ["type" => "S", "title" => "배송상품 오배송"],
                        "PNT" => ["type" => "S", "title" => "상품미도착"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ],
                    //배송완료->반품요청(프론트)
                    ORDER_STATUS_RETURN_APPLY => [
                        "OCF" => ["type" => "B", "title" => "사이즈,색상잘못선택"],
                        "PD" => ["type" => "S", "title" => "배송상품 파손/하자"],
                        "DE" => ["type" => "S", "title" => "배송상품 오배송"],
                        "PNT" => ["type" => "S", "title" => "상품미도착"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ]
                ],
            ],
            "A" => [
                //입금예정->취소요청(관리자)
                ORDER_STATUS_INCOM_READY => [
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "N", "title" => "다른상품구매"],
                        "NB" => ["type" => "N", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "N", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "N", "title" => "상품정보상이"],
                        "PSL" => ["type" => "S", "title" => "상품재고부족"],
                        "PSO" => ["type" => "S", "title" => "상품품절"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"],
                        "SYS" => ["type" => "N", "title" => "시스템자동취소"]
                    ]
                ],
                //입금완료->취소요청(관리자)
                ORDER_STATUS_INCOM_COMPLETE => [
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "B", "title" => "다른상품구매"],
                        "NB" => ["type" => "B", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "S", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "PSL" => ["type" => "S", "title" => "상품재고부족"],
                        "PSO" => ["type" => "S", "title" => "상품품절"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"],
                        "SYS" => ["type" => "N", "title" => "시스템자동취소"]
                    ]
                ],
                //발주확인->취소요청(관리자)
                ORDER_STATUS_DELIVERY_READY => [
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "B", "title" => "다른상품구매"],
                        "NB" => ["type" => "B", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "S", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "PSL" => ["type" => "S", "title" => "상품재고부족"],
                        "PSO" => ["type" => "S", "title" => "상품품절"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"],
                    ]
                ],
                //취소요청->취소거부(관리자)
                ORDER_STATUS_CANCEL_APPLY => [
                    ORDER_STATUS_CANCEL_DENY => [
                        "MCC" => ["type" => "N", "title" => "주문제작 취소불가"],
                        "NCP" => ["type" => "N", "title" => "취소불가상품(상품페이지참조)"],
                        "DR" => ["type" => "N", "title" => "포장완료/배송대기"],
                        "ETC" => ["type" => "N", "title" => "기타"]
                    ]
                ],
                //배송지연(관리자)
                ORDER_STATUS_DELIVERY_DELAY => [
                    //배송지연
                    ORDER_STATUS_DELIVERY_DELAY => [
                        "STS" => ["type" => "N", "title" => "단기재고부족"],
                        "OG" => ["type" => "N", "title" => "주문폭주로인한 작업지연"],
                        "OMI" => ["type" => "N", "title" => "주문제작 중"],
                        "BA" => ["type" => "N", "title" => "고객요청"],
                        "ETC" => ["type" => "N", "title" => "기타"]
                    ],
                    //배송지연->취소요청(관리자)
                    ORDER_STATUS_CANCEL_APPLY => [
                        "DPB" => ["type" => "B", "title" => "다른상품구매"],
                        "NB" => ["type" => "B", "title" => "구매의사없음/변심"],
                        "DD" => ["type" => "S", "title" => "배송처리늦음/지연"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "PSL" => ["type" => "S", "title" => "상품재고부족"],
                        "PSO" => ["type" => "S", "title" => "상품품절"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ]
                ],
                //교환거부(관리자)
                ORDER_STATUS_EXCHANGE_DENY => [
                    ORDER_STATUS_EXCHANGE_DENY => [
                        "MPNE" => ["type" => "N", "title" => "주문제작상품으로 교환불가"],
                        "NEP" => ["type" => "N", "title" => "교환불가상품(상세페이지참조)"],
                        "ETC" => ["type" => "N", "title" => "기타"]
                    ]
                ],
                //교환보류(관리자)
                ORDER_STATUS_EXCHANGE_DEFER => [
                    ORDER_STATUS_EXCHANGE_DEFER => [
                        "NRA" => ["type" => "N", "title" => "반품상품 미입고"],
                        "NRDP" => ["type" => "N", "title" => "반품배송비 미동봉"],
                        "RPD" => ["type" => "N", "title" => "반품상품 훼손/파손"],
                        "RPPD" => ["type" => "N", "title" => "반품상품포장 훼손/파손"],
                        "ETC" => ["type" => "N", "title" => "기타"]
                    ]
                ],
                //교환불가(관리자)
                ORDER_STATUS_EXCHANGE_IMPOSSIBLE => [
                    ORDER_STATUS_EXCHANGE_IMPOSSIBLE => [
                        "RPD" => ["type" => "N", "title" => "반품상품 훼손/파손"],
                        "RPPD" => ["type" => "N", "title" => "반품상품포장 훼손/파손"],
                        "BNC" => ["type" => "N", "title" => "구매자 연락되지 않음"]
                    ]
                ],
                //배송완료(관리자)
                ORDER_STATUS_DELIVERY_COMPLETE => [
                    //반품요청
                    ORDER_STATUS_RETURN_APPLY => [
                        "OCF" => ["type" => "B", "title" => "사이즈,색상잘못선택"],
                        "PD" => ["type" => "S", "title" => "배송상품 파손/하자"],
                        "DE" => ["type" => "S", "title" => "배송상품 오배송"],
                        "PNT" => ["type" => "S", "title" => "상품미도착"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ],
                    //교환요청
                    ORDER_STATUS_EXCHANGE_APPLY => [
                        "OCF" => ["type" => "B", "title" => "사이즈,색상잘못선택"],
                        "PD" => ["type" => "S", "title" => "배송상품 파손/하자"],
                        "DE" => ["type" => "S", "title" => "배송상품 오배송"],
                        "PNT" => ["type" => "S", "title" => "상품미도착"],
                        "PIE" => ["type" => "S", "title" => "상품정보상이"],
                        "ETCB" => ["type" => "B", "title" => "기타(구매자책임)"],
                        "ETCS" => ["type" => "S", "title" => "기타(판매자책임)"]
                    ]
                ],
                //반품거부(관리자)
                ORDER_STATUS_RETURN_DENY => [
                    ORDER_STATUS_RETURN_DENY => [
                        "MPNE" => ["type" => "N", "title" => "주문제작상품으로 교환불가"],
                        "NEP" => ["type" => "N", "title" => "교환불가상품(상세페이지참조)"],
                        "ETC" => ["type" => "N", "title" => "기타"]
                    ]
                ],
                //반품보류(관리자)
                ORDER_STATUS_RETURN_DEFER => [
                    ORDER_STATUS_RETURN_DEFER => [
                        "NRA" => ["type" => "N", "title" => "반품상품 미입고"],
                        "NRDP" => ["type" => "N", "title" => "반품배송비 미동봉"],
                        "RPD" => ["type" => "N", "title" => "반품상품 훼손/파손"],
                        "RPPD" => ["type" => "N", "title" => "반품상품포장 훼손/파손"],
                        "ETC" => ["type" => "N", "title" => "기타"]
                    ]
                ],
                //반품불가(관리자)
                ORDER_STATUS_RETURN_IMPOSSIBLE => [
                    ORDER_STATUS_RETURN_IMPOSSIBLE => [
                        "RPD" => ["type" => "N", "title" => "반품상품 훼손/파손"],
                        "RPPD" => ["type" => "N", "title" => "반품상품포장 훼손/파손"],
                        "BNC" => ["type" => "N", "title" => "구매자 연락되지 않음"]
                    ]
                ]
            ]
        ];

        if (isset($data[$type][$fkey][$skey])) {
            if ($code === false) {
                return $data[$type][$fkey][$skey];
            } elseif ($key === false) {
                return ($data[$type][$fkey][$skey][$code] ?? []);
            } else {
                return ($data[$type][$fkey][$skey][$code][$key] ?? '');
            }
        } else {
            return '';
        }
    }

    public static function getDeliveryCompanyInfo($code_ix = false, $key = false)
    {
        static $data = false;

        if ($data === false) {
            $rows = getForbiz()->qb
                ->select('code_ix')
                ->select('code_name')
                ->select('code_etc1')
                ->select('code_etc2')
                ->select('code_etc3')
                ->select('code_etc4')
                ->from(TBL_SHOP_CODE)
                ->where('code_gubun', '02')
                ->where('disp', 1)
                ->exec()
                ->getResultArray();

            foreach ($rows as $row) {
                $data[$row['code_ix']] = [
                    'name' => $row['code_name'],
                    'url' => $row['code_etc1'],
                    'method' => $row['code_etc3'],
                    'column' => $row['code_etc4'],
                    'etc' => $row['code_etc2']
                ];
            }
        }

        if ($key === false) {
            return ($code_ix === false ? $data : ($data[$code_ix] ?? []));
        } else {
            return ($data[$code_ix][$key] ?? '');
        }
    }

    /**
     * 관리자 그룹 정보
     * @staticvar array $data
     * @param string $key
     * @param string $type 'value|key'
     * @return string
     */
    static public function getScmGroup($key = false, $type = 'value')
    {
        static $data = [];

        if (empty($data)) {
            $rows = getForbiz()->qb
                ->select('system_group_type_id')
                ->select('group_name')
                ->from(TBL_SYSTEM_GROUP_TYPE)
                ->orderBy('system_group_type_id')
                ->exec()
                ->getResult();

            if (!empty($data)) {
                foreach ($rows as $row) {
                    $data[$row['system_group_type_id']] = $row['group_name'];
                }
            } else {
                $data[1] = '시스템관리자';
            }
        }

        if ($key) {
            if ($type == 'value') {
                return isset($data[$key]) ?? false;
            } else {
                return array_search($key, $data);
            }
        } else {
            return $data;
        }
    }

    /**
     * 모듈별 엑세스 권한을 조회한다.
     * @param string $moduleGroup 모듈그룹명
     * @param string $moduleName 모듈명
     * @param string $userCode 회원코드
     * @param string $groupCode 그룹코드
     * @return array
     */
    static public function getAcl($moduleGroup, $moduleName, $userCode, $groupCode)
    {

        $aclModel = getForbiz()->import('model.scm.system.acl');

        $firstAdmin   = $aclModel->isFirstAdminId(sess_val('admininfo', 'charger_id'));
        $fullModule   = $moduleGroup.'/'.$moduleName;
        $systemModule = ['System/ModuleMan', 'System/ManageAuthorityDivision', 'System/ManageAuthority', 'System/ManageMenu'];

        if (sess_val('admininfo', 'charger_id') == 'forbiz') {
            return [
                'read' => 'Y'
                , 'write' => 'Y'
                , 'delete' => 'Y'
                , 'down' => 'Y'
            ];
        } else if (in_array($fullModule, $systemModule)) {
            if ($firstAdmin) {
                return [
                    'read' => 'Y'
                    , 'write' => 'Y'
                    , 'delete' => 'Y'
                    , 'down' => 'Y'
                ];
            } else {
                return [
                    'read' => 'N'
                    , 'write' => 'N'
                    , 'delete' => 'N'
                    , 'down' => 'N'
                ];
            }
        } else if ($groupCode == PUBLIC_ADMIN_GROUP_CODE) {
            return [
                'read' => 'Y'
                , 'write' => 'Y'
                , 'delete' => 'N'
                , 'down' => 'N'
            ];
        } else {
            $acl = [
                'read' => 'N'
                , 'write' => 'N'
                , 'delete' => 'N'
                , 'down' => 'N'
            ];

            $rows = getForbiz()->qb
                ->select('read')
                ->select('write')
                ->select('delete')
                ->select('down')
                ->from(TBL_SYSTEM_MODULE.' AS sm')
                ->join(TBL_SYSTEM_MODULE_GROUP.' AS smg', 'sm.system_module_group_id = smg.system_module_group_id')
                ->join(TBL_SYSTEM_ACL.' AS sa', 'sm.system_module_id = sa.system_module_id')
                ->where('smg.module_group', lcfirst($moduleGroup))
                ->where('sm.module', lcfirst($moduleName))
                ->groupStart()
                ->where('sa.system_group_type_id', PUBLIC_ADMIN_GROUP_CODE)
                ->orWhere('sa.system_group_type_id', $groupCode)
                ->orWhere('sa.code', $userCode)
                ->groupEnd()
                ->exec()
                ->getResultArray();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $acl['read']   = ($row['read'] == 'Y' ? 'Y' : $acl['read']);
                    $acl['write']  = ($row['write'] == 'Y' ? 'Y' : $acl['write']);
                    $acl['delete'] = ($row['delete'] == 'Y' ? 'Y' : $acl['delete']);
                    $acl['down']   = ($row['down'] == 'Y' ? 'Y' : $acl['down']);
                }
            }
        }

        return $acl;
    }

    /**
     * 모듈 그룹
     * @return array
     */
    static public function getMouleGroup()
    {
        static $data = [];

        if (empty($data)) {
            $rows = getForbiz()->qb
                ->from(TBL_SYSTEM_MODULE_GROUP)
                ->where('is_use', 'Y')
                ->exec()
                ->getResultArray();

            foreach ($rows as $row) {
                $data[$row['system_module_group_id']] = $row['module_group'];
            }
        }

        return $data;
    }

    public static function getMallConfig($key)
    {
        static $data = [];

        if (!isset($data[$key])) {
            $row = getForbiz()->qb
                ->select('config_value')
                ->from(TBL_SHOP_MALL_CONFIG)
                ->where('mall_ix', MALL_IX)
                ->where('config_name', $key)
                ->limit(1)
                ->exec()
                ->getRow();

            $data[$key] = $row->config_value ?? '';
        }

        return ($data[$key] ?? '');
    }

    public static function getUseOpenApi()
    {
        $row = getForbiz()->qb
            ->select('is_use')
            ->from('system_sub_menu')
            ->where('system_sub_menu_id', 464)
            ->exec()
            ->getRowArray();

        return ($row['is_use'] ?? 'N');;
    }

}