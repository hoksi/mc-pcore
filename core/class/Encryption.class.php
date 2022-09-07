<?php
/**
 * Description of ase128
 *
 * @author hong
 */
class Encryption
{
    protected $enc;
    function __construct()
    {


        getForbiz()->load->library('encryption');
        $this->enc = getForbiz()->encryption;
        $this->key = bin2hex($this->enc->create_key(16));

        $this->initialize = $this->enc->initialize(
            array(
                'mode' => 'cbc',
                'cipher' => 'aes-256',
                'key' => 'encrypt_key!@2947' ,
                'driver' => 'openssl'
            )
        );

    }

    /**
     * 암호화
     * @param $val
     * @return string
     */
    public function encrypt($val)
    {
        return $this->enc->encrypt($val);
    }

    /**
     * 복호화
     * @param $val
     * @return string
     */
    public function decrypt($val)
    {

        return $this->enc->decrypt($val);
    }

    /**
     * hex to bin
     * @param $data
     * @return string
     */
    private function hex2bin($data)
    {
        $bin = "";
        $i = 0;
        do {
            $bin .= chr(hexdec($data[$i] . $data[($i + 1)]));
            $i += 2;
        } while ($i < strlen($data));

        return $bin;
    }
}