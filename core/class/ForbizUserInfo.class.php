<?php

/**
 * Description of FrobizAdmin
 *
 * @author hoksi
 */
class ForbizUserInfo
{
    /**
     * 고유코드
     * @var string
     */
    public $company_id = '';
    /**
     * 회원코드
     * @var string
     */
    public $code = '';
    /**
     * 회원명
     * @var string
     */
    public $name = '';
    /**
     * 회원별명
     * @var string
     */
    public $nick_name = '';
    /**
     * 회원 이메일
     * @var stirng
     */
    public $mail = '';
    /**
     * 아이디
     * @var string
     */
    public $id = '';
    /**
     * 그룹레벨
     * @var int
     */
    public $gp_level = false;
    /**
     * 그룹명
     * @var string
     */
    public $gp_name = '';
    /**
     * 그룹레벨
     * @var type
     */
    public $perm = '';
    /**
     * 회원타입 (M:일반회원 C: 사업자 A: 직원)
     * @var string
     */
    public $mem_type = '';
    /**
     * 그룹 인덱스
     * @var int
     */
    public $gp_ix = false;
    /**
     * 성별
     * @var string
     */
    public $sex = '';
    /**
     * 나이
     * @var int
     */
    public $age = false;
    /**
     * 생일
     * @var string
     */
    public $birthday = '';
    /**
     * 그룹할인률
     * @var int
     */
    public $sale_rate = false;
    /**
     * 회원등급별 배송비
     * @var type
     */
    public $shipping_dc_price = false;
    /**
     * 회원 휴대폰 번호
     * @var string
     */
    public $pcs = '';
    /**
     * 회원그룹 할인율 타입 (c:카테고리할인 g:일반할인(그룹) w:품목별가격 적용)
     * @var string
     */
    public $use_discount_type = '';
    /**
     * 반올림 자리수
     * @var int
     */
    public $round_depth = false;
    /**
     * 반올림 방식 (round:반올림 floor: 내림)
     * @var string
     */
    public $round_type = '';
    /**
     * 도소매 회원 구분 (R:소매 W:도매)
     * @var string
     */
    public $selling_type = '';
    /**
     * 등록일
     * @var string
     */
    public $mem_reg_date = '';
    /**
     * 가격 노출 타입(l:판매가,s:할인가,p:프리미엄)
     * @var string
     */
    public $dc_standard_price = '';
    /**
     * 쿠폰사용 가능여부
     * @var string
     */
    public $use_coupon_yn = '';
    /**
     * 마일리지 사용/적립 가능여부
     * @var string
     */
    public $use_reserve_yn = '';
    /**
     * 휴면계정 여부
     * @var string
     */
    public $sleep_account = '';
    /**
     * 비밀번호 변경 안내
     * @var boolean
     */
    public $changeAccessPassword = false;
    /**
     * 스마트폰 앱 타입
     * @var boolean
     */
    public $appType = false;

    public function __construct($data = [])
    {
        if(!empty($data)) {
            foreach($data as $key => $val) {
                $this->{$key} = $val;
            }
        }

        // 스마트폰 앱타입을 확인한다.
        $this->appType = getAppType();
    }
}