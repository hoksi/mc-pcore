<?php

class ForbizTag
{
    /////////////////////////////////////////////////
    // 사용자 정보
    /////////////////////////////////////////////////
    protected $userID    = "-";  // 사용자 아이디
    protected $email     = "-";  // email
    protected $gender    = 2;  // 사용자 성별
    protected $mobile    = "-";  // 핸드폰
    protected $birthYear = "-";  // 생년(4자리)
    protected $zip       = '-';  // 지역
    protected $data0     = "-";  // 확장필드0
    protected $data1     = "-";
    protected $data2     = "-";
    protected $data3     = "-";
    protected $data4     = "-";
    protected $data5     = "-";
    protected $data6     = "-";
    protected $data7     = "-";
    protected $data8     = "-";
    protected $data9     = "-";  // 확장필드9
    /////////////////////////////////////////////////

    protected $user_info = '';
    protected $file;
    protected $siteID    = '002';  // 업체아이디
    protected $data_root = '';  // data root
    protected $shoppingStep; // 구매스텝
    protected $products;  //상품 리스트
    protected $index;
    protected $return_value;
    protected $product;
    protected $memberRegStep;
    protected $errMsg    = [];

    public function __construct()
    {
        $this->user_info     = "";
        $this->file          = (defined('ANALYSIS_TAG_JS') ? ANALYSIS_TAG_JS : '/mallstory_SalesAnalysisTag.js');
        $this->index         = 0;
        $this->shoppingStep  = SHOPPING_NONE;
        $this->memberRegStep = "";
    }

    /**
     *
     * @return string
     */
    public function getTag()
    {
        $this->user_info = implode('|',
            [
                $this->userID
                , $this->email
                , $this->mobile
                , $this->gender
                , $this->birthYear
                , $this->zip
                , $this->data0
                , $this->data1
                , $this->data2
                , $this->data3
                , $this->data4
                , $this->data5
                , $this->data6
                , $this->data7
                , $this->data8
                , $this->data9
            ]
        );

        if ((is_Numeric($this->gender) == false) || (int) ($this->gender) > 2) {
            $this->setErrorMsg("사용자 성별은 0(여자) 1(남자) 2(모름) 으로 입력해야 합니다. gender= ".$this->gender);
        }

        if ($this->birthYear != "-") {
            if (strLen($this->birthYear) != "4" || is_numeric($this->birthYear) == false) {
                $this->setErrorMsg("사용자 생년은 숫자4자리이어야 합니다. 1975(o), 75(x), 75년생(x) birthYear= ".$this->birthYear);
            }
        }

        if (strstr($this->zip, "-") == false) {
            $this->setErrorMsg("우편번호는 xxx-xxx 형태로 작성을 해야 합니다. zip=".$this->zip);
        }

        if ($this->file == "") {
            $this->setErrorMsg("스크립트의 이름이 지정되지 않았습니다.");
        }

        // 일반 페이지
        $return_value = $this->user_info;

        if ($this->memberRegStep != "") {
            // 회원 가입 페이지
            $return_value = $this->user_info."|".$this->memberRegStep;
        } else if ($this->shoppingStep != SHOPPING_NONE) {
            // 상품조회, 구매스텝
            if (count($this->product) == 0) {
                $this->setErrorMsg("상품에 대한 정보가 없습니다.");
            } else if ($this->shoppingStep < SHOPPING_NONE || $this->shoppingStep > SHOPPING_DONE) {
                $this->setErrorMsg("구매스텝의 값이 비정상적입니다. shoppingStep=".$this->shoppingStep);
            } else if (!empty($this->product) && ($this->shoppingStep <= SHOPPING_NONE || $this->shoppingStep > SHOPPING_DONE)) {
                $this->setErrorMsg("상품항목수 : ".count($this->product).", 구매스텝의 값이 정의되지 않았습니다. shoppingStep=".$this->shoppingStep);
            } else {
                $return_value = $this->user_info."|".$this->shoppingStep.'|';

                for ($i = 0; $i < count($this->product); $i++) {
                    $pInfo = explode('|', $this->product[$i]);
                    if ($pInfo[1] < 0 || $pInfo[2] < 0) {
                        $this->setErrorMsg("가격과 수량의 값이 비정상적입니다. price=".($pInfo[1] ?? '').", quantity=".($pInfo[2] ?? ''));
                        break;
                    } else {
                        $return_value .= $this->product[$i];
                    }
                }
            }
        }

        return $this->makeTag($return_value);
    }

    public function setMemberRegStep($memberRegStep)
    {
        $this->memberRegStep = $memberRegStep;

        return $this;
    }

    public function setShoppingStep($shoppingStep)
    {
        $this->shoppingStep = $shoppingStep;

        return $this;
    }

    public function createProduct($name, $path, $price, $quantity)
    {
        if ($quantity < 1) {
            $this->setErrorMsg("수량이 0보다 작습니다.");
        }

        if ($price < 0) {
            $this->setErrorMsg("가격이 '-' 입니다.");
        }


        $this->product[$this->index] = "|".strip_tags($path).">".strip_tags(ltrim(rtrim($name)))."|".$price."|".$quantity;
        $this->index++;
    }

    protected function makeTag($strVal)
    {
        if (empty($this->errMsg)) {
            $logStoryUrl = ForbizConfig::getMallConfig('logstory_url') ?: '';

            $js[] = '';
            $js[] = '<script language="javascript" src="'.$this->file.'"></script>';
            $js[] = sprintf('<script language="javascript">window.onload = function() {SetSalesAnalysisTag("%s","%s","%s","%s");};</script>', $strVal, $this->siteID, $this->data_root, $logStoryUrl);

            return implode("\n", $js);
        } else {
            return implode("\n", $this->errMsg);
        }
    }

    protected function setErrorMsg($errMsg)
    {
        $this->errMsg[] = "<!-- SalesAnalysis Tag Error Message : ".$errMsg." -->";
    }
}