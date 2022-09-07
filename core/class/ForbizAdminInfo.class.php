<?php

/**
 * Description of FrobizAdmin
 *
 * @author hoksi
 */
class ForbizAdminInfo
{
    /**
     * 회사코드
     * @var string
     */
    public $company_id = '';
    /**
     * 회사명
     * @var string
     */
    public $company_name = '';
    /**
     * 회원코드
     * @var string
     */
    public $charger_ix = '';
    /**
     * 아이디
     * @var string
     */
    public $charger_id = '';
    /**
     * 회원명
     * @var string
     */
    public $charger = '';
    /**
     * 닉네임
     * @var string
     */
    public $nick_name = '';
    /**
     * 권한템플릿코드
     * @var int
     */
    public $charger_roll = false;
    /**
     * 사용언어
     * @var string
     */
    public $language = '';
    /**
     * 회원타입 M:일반회원 C: 사업자 A: 직원
     * @var string
     */
    public $mem_type = '';
    /**
     * 회원레벨 11:지사장 , 12:총괄MD , 13 : MD 팀장, 14 : MD
     * @var string
     */
    public $mem_level = '';
    /**
     * 회원그룹 S: 셀러 MD : MD담당자 D:아무도 아닌경우
     * @var string
     */
    public $mem_div = '';
    /**
     * 관리자 레벨
     * @var int
     */
    public $admin_level = false;
    /**
     * 부서분류값
     * @var string
     */
    public $department = '';
    /**
     * 직책분류값
     * @var string
     */
    public $position = '';
    /**
     * 그룹타입코드
     * @var int
     */
    public $group_code = -1;
    /**
     * 셀러 고유번호
     * @var int
     */
    public $seller_idx = -1;

    public function __construct($data = [])
    {
        if(!empty($data)) {
            foreach($data as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }
}