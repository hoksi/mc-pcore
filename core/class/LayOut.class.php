<?php

class LayOut
{
    /**
     * @todo 버전 픽스 완료 후 삭제 예정 (다른 페이지 에러때문에..)
     * @var $Config
     */
    public $Config;
    private $tpl;
    private $layoutScopeId      = "layout";
    private $headerTopScopeId   = "headerTop";
    private $headerMenuScopeId  = "headerMenu";
    private $contentsScopeId    = "contents";
    private $contentsAddScopeId = "contentsAdd";
    private $leftMenuScopeId    = "leftMenu";
    private $rightMenuScopeId   = "rightMenu";
    private $footerMenuScopeId  = "footerMenu";
    private $footerDescScopeId  = "footerDesc";
    private $layoutPath;
    private $headerTopPath;
    private $headerMenuPath;
    private $contentsPath;
    private $contentsAddPath;
    private $leftMenuPath;
    private $rightMenuPath;
    private $footerMenuPath;
    private $footerDescPath;
    private $assignData;

    public function __construct()
    {
        $this->tpl = getForbiz()->tpl;
    }

    public function setCommonAssign($assignData)
    {
        $this->assignData = $assignData;
    }

    public function setLayoutPath($layoutPath)
    {
        $this->layoutPath = $layoutPath;
    }

    public function setHeaderTopPath($headerTopPath)
    {
        $this->headerTopPath = $headerTopPath;
    }

    public function setHeaderMenuPath($headerMenuPath)
    {
        $this->headerMenuPath = $headerMenuPath;
    }

    public function setContentsPath($contentsPath)
    {
        $this->contentsPath = $contentsPath;
    }

    public function setContentsAddPath($contentsAddPath)
    {
        $this->contentsAddPath = $contentsAddPath;
    }

    public function setLeftMenuPath($leftMenuPath)
    {
        $this->leftMenuPath = $leftMenuPath;
    }

    public function setRightMenuPath($rightMenuPath)
    {
        $this->rightMenuPath = $rightMenuPath;
    }

    public function setFooterMenuPath($footerMenuPath)
    {
        $this->footerMenuPath = $footerMenuPath;
    }

    public function setFooterDescPath($footerDescPath)
    {
        $this->footerDescPath = $footerDescPath;
    }

    public function getContentsScopeId()
    {
        return $this->contentsScopeId;
    }

    public function getAssignData()
    {
        return $this->assignData;
    }

    public function contentsScope($contentsPath = '')
    {
        $this->tpl->define($this->contentsScopeId, ($contentsPath ? $contentsPath : $this->contentsPath));
    }

    public function setLayoutAssign($key, $val = '')
    {
        $this->fnAssignData($this->layoutScopeId, $key, $val);
        return $this;
    }

    public function setHeaderTopAssign($key, $val = '')
    {
        $this->fnAssignData($this->headerTopScopeId, $key, $val);
        return $this;
    }

    public function setHeaderMenuAssign($key, $val = '')
    {
        $this->fnAssignData($this->headerMenuScopeId, $key, $val);
        return $this;
    }

    public function setContentsAddAssign($key, $val = '')
    {
        $this->fnAssignData($this->contentsAddScopeId, $key, $val);
        return $this;
    }

    public function setLeftMenuAssign($key, $val = '')
    {
        $this->fnAssignData($this->leftMenuScopeId, $key, $val);
        return $this;
    }

    public function setRightMenuAssign($key, $val = '')
    {
        $this->fnAssignData($this->rightMenuScopeId, $key, $val);
        return $this;
    }

    public function setFooterMenuAssign($key, $val = '')
    {
        $this->fnAssignData($this->footerMenuScopeId, $key, $val);
        return $this;
    }

    public function setFooterDescAssign($key, $val = '')
    {
        $this->fnAssignData($this->footerDescScopeId, $key, $val);
        return $this;
    }

    private function fnAssignData($scope, $key, $val)
    {
        $key = is_array($key) ? $key : [$key => $val];

        $this->assignData[$scope] = array_merge($this->assignData[$scope], $key);
    }

    public function LoadLayOut()
    {
        $this->tpl->define($this->layoutScopeId, $this->layoutPath);
        $this->tpl->define($this->headerTopScopeId, $this->headerTopPath);
        $this->tpl->define($this->headerMenuScopeId, $this->headerMenuPath);
        $this->tpl->define($this->contentsAddScopeId, $this->contentsAddPath);
        $this->tpl->define($this->leftMenuScopeId, $this->leftMenuPath);
        $this->tpl->define($this->rightMenuScopeId, $this->rightMenuPath);
        $this->tpl->define($this->footerMenuScopeId, $this->footerMenuPath);
        $this->tpl->define($this->footerDescScopeId, $this->footerDescPath);

        $this->tpl->assign($this->assignData);

        return $this->tpl->fetch($this->layoutScopeId);
    }
}