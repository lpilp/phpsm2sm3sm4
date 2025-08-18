<?php

class Sm2Asn1 {
    /**
     * 将整数转换为ASN.1 INTEGER编码
     * @param string $hex 十六进制字符串
     * @return string ASN.1编码的INTEGER
     */
    protected function encodeInteger($hex) {
        // 移除可能的0x前缀
        $hex = ltrim(strtolower($hex), '0x');
        
        // 确保是偶数长度
        if (strlen($hex) % 2 != 0) {
            $hex = '0' . $hex;
        }
        
        // 转换为二进制
        $bin = hex2bin($hex);
        
        // 如果最高位为1，需要添加0x00前缀防止被解释为负数
        if (ord($bin[0]) & 0x80) {
            $bin = "\x00" . $bin;
        }
        
        // INTEGER标签
        $tag = "\x02";
        
        // 长度编码
        $length = $this->encodeLength(strlen($bin));
        
        return $tag . $length . $bin;
    }

    /**
     * 将字符串转换为ASN.1 OCTET STRING编码
     * @param string $str 字符串
     * @return string ASN.1编码的OCTET STRING
     */
    protected function encodeOctetString($str) {
        // OCTET STRING标签
        $tag = "\x04";
        
        // 长度编码
        $length = $this->encodeLength(strlen($str));
        
        return $tag . $length . $str;
    }

    /**
     * 编码长度字段
     * @param int $len 长度
     * @return string 编码后的长度
     */
    protected function encodeLength($len) {
        if ($len < 0x80) {
            return chr($len);
        } else {
            $lenBytes = '';
            while ($len > 0) {
                $lenBytes = chr($len & 0xFF) . $lenBytes;
                $len >>= 8;
            }
            return chr(0x80 | strlen($lenBytes)) . $lenBytes;
        }
    }

    /**
     * 将四个参数编码成SM2 ASN.1格式
     * @param string $c1x 十六进制字符串
     * @param string $c1y 十六进制字符串
     * @param string $c3 字符串
     * @param string $c2 字符串
     * @return string ASN.1编码的十六进制字符串
     */
    public function encodeSM2ASN1($c1x, $c1y, $c3, $c2) {
        // 编码各个字段
        $c1xEncoded = $this->encodeInteger($c1x);
        $c1yEncoded = $this->encodeInteger($c1y);
        $c3Encoded = $this->encodeOctetString($c3);
        $c2Encoded = $this->encodeOctetString($c2);
        
        // 构造SEQUENCE
        $sequenceData = $c1xEncoded . $c1yEncoded . $c3Encoded . $c2Encoded;
        
        // SEQUENCE标签
        $tag = "\x30";
        
        // 长度编码
        $length = $this->encodeLength(strlen($sequenceData));
        
        // 完整的ASN.1编码
        $asn1 = $tag . $length . $sequenceData;
        
        // 返回十六进制字符串
        return bin2hex($asn1);
    }
    
    /**
     * 解析ASN.1长度字段
     * @param string $data 数据
     * @param int $offset 偏移量引用
     * @return int 长度值
     */
    protected function decodeLength($data, &$offset) {
        $firstByte = ord($data[$offset++]);
        
        if ($firstByte < 0x80) {
            return $firstByte;
        } else {
            $lengthBytes = $firstByte & 0x7F;
            $len = 0;
            for ($i = 0; $i < $lengthBytes; $i++) {
                $len = ($len << 8) | ord($data[$offset++]);
            }
            return $len;
        }
    }

    /**
     * 解析ASN.1 INTEGER
     * @param string $data 数据
     * @param int $offset 偏移量引用
     * @return string 十六进制字符串
     */
    protected function decodeInteger($data, &$offset) {
        // 检查标签
        $tag = ord($data[$offset++]);
        if ($tag !== 0x02) {
            throw new Exception("Invalid INTEGER tag");
        }
        
        // 解析长度
        $length = $this->decodeLength($data, $offset);
        
        // 获取数据
        $integerData = substr($data, $offset, $length);
        $offset += $length;
        
        // 处理可能的0x00前缀
        if (ord($integerData[0]) === 0x00) {
            $integerData = substr($integerData, 1);
        }
        
        // 转换为十六进制
        return bin2hex($integerData);
    }

    /**
     * 解析ASN.1 OCTET STRING
     * @param string $data 数据
     * @param int $offset 偏移量引用
     * @return string 字符串
     */
    protected function decodeOctetString($data, &$offset) {
        // 检查标签
        $tag = ord($data[$offset++]);
        if ($tag !== 0x04) {
            throw new Exception("Invalid OCTET STRING tag");
        }
        
        // 解析长度
        $length = $this->decodeLength($data, $offset);
        
        // 获取数据
        $octetData = substr($data, $offset, $length);
        $offset += $length;
        
        return $octetData;
    }

    /**
     * 解码SM2 ASN.1格式
     * @param string $hex ASN.1编码的十六进制字符串
     * @return array 包含c1x, c1y, c3, c2的数组
     */
    public function decodeSM2ASN1($hex) {
        // 转换为二进制
        $data = hex2bin($hex);
        $offset = 0;
        
        // 检查SEQUENCE标签
        $tag = ord($data[$offset++]);
        if ($tag !== 0x30) {
            throw new Exception("Invalid SEQUENCE tag");
        }
        
        // 解析SEQUENCE长度
        $sequenceLength = $this->decodeLength($data, $offset);
        
        // 解析各个字段
        $c1x = $this->decodeInteger($data, $offset);
        $c1y = $this->decodeInteger($data, $offset);
        $c3 = $this->decodeOctetString($data, $offset);
        $c2 = $this->decodeOctetString($data, $offset);
        
        return [
            'c1x' => $c1x,
            'c1y' => $c1y,
            'c3' => $c3,
            'c2' => $c2
        ];
    }
}
// $debug = intval(@$argv[1]);
// if ($debug == 1) {
//    $sm2 = new Sm2Asn1();
//     $result = $sm2->encodeSM2ASN1("981234abcd", "5678ef01", str_repeat("hashvalue",4), str_repeat("encrypteddata",5));
//     echo "Encoded: " . $result . "\n";

//     // 解码示例
//     $decoded = $sm2->decodeSM2ASN1($result);
//     echo "Decoded:\n";
//     echo "c1x: " . $decoded['c1x'] . "\n";
//     echo "c1y: " . $decoded['c1y'] . "\n";
//     echo "c3: " . $decoded['c3'] . "\n";
//     echo "c2: " . $decoded['c2'] . "\n";
// }
