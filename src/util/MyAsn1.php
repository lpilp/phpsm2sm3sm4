<?php

namespace Rtgm\util;

use FG\ASN1\ASNObject;
use FG\ASN1\Identifier;


class MyAsn1
{
    const CLASS_UNIVERSAL        = 0;
    const CLASS_APPLICATION      = 1;
    const CLASS_CONTEXT_SPECIFIC = 2;
    const CLASS_PRIVATE          = 3;
    const TYPE_BOOLEAN           = 1;
    const TYPE_INTEGER           = 2;
    const TYPE_BIT_STRING        = 3;
    const TYPE_OCTET_STRING      = 4;
    const TYPE_NULL              = 5;
    const TYPE_OBJECT_IDENTIFIER = 6;
    const TYPE_OBJECT_DESCRIPTOR = 7;
    const TYPE_INSTANCE_OF       = 8; // EXTERNAL
    const TYPE_REAL              = 9;
    const TYPE_ENUMERATED        = 10;
    const TYPE_EMBEDDED          = 11;
    const TYPE_UTF8_STRING       = 12;
    const TYPE_RELATIVE_OID      = 13;
    const TYPE_SEQUENCE          = 16; // SEQUENCE OF
    const TYPE_SET               = 17; // SET OF
    const TYPE_NUMERIC_STRING    = 18;
    const TYPE_PRINTABLE_STRING  = 19;
    const TYPE_TELETEX_STRING    = 20; // T61String
    const TYPE_VIDEOTEX_STRING   = 21;
    const TYPE_IA5_STRING        = 22;
    const TYPE_UTC_TIME          = 23;
    const TYPE_GENERALIZED_TIME  = 24;
    const TYPE_GRAPHIC_STRING    = 25;
    const TYPE_VISIBLE_STRING    = 26; // ISO646String
    const TYPE_GENERAL_STRING    = 27;
    const TYPE_UNIVERSAL_STRING  = 28;
    const TYPE_CHARACTER_STRING  = 29;
    const TYPE_BMP_STRING        = 30;
    const TYPE_CHOICE            = -1;
    const TYPE_ANY               = -2;
    const TYPE_ANY_RAW           = -3;
    const TYPE_ANY_SKIP          = -4;
    const TYPE_ANY_DER           = -5;

    public static function decode_file($pemfile)
    {
        $data = self::pem2der(file_get_contents($pemfile));
        // var_dump($data);
        return self::decode($data);
    }

    public static function decode($data, $format = 'bin')
    {
        if ($format == 'base64') {
            $data = base64_decode($data);
        } else if ($format == 'hex') {
            $data = hex2bin($data);
        }
        $asnObject = ASNObject::fromBinary($data);
        return self::printObject($asnObject);
    }
    public static function printObject(ASNObject $object, $depth = 0)
    {
        $content = $object->getContent();
        if (is_array($content)) {
            $result = array();
            foreach ($object as $child) {
                $rs = self::printObject($child, $depth + 1);
                $result[] = $rs;
            }
            return $result;
        } else {
            $type = $object->getType();
            
            // $strval = $object->__toString(); 
            // 如果是 oid的话，tostring时是取的oidText, PHPasn1没有sm2等相关的就会去调用 http://oid-info.com/get/{$oidString}的接口，然后超时0.5秒
            // 这里相当于直接取get_contents(), 也是可以的
            $strval = $content; 
            if ($type == 6) { //oid
                $rt =  self::OIDtoText($strval);
            } else if ($type == 2) {
                $rt =   self::format_bigint($strval);
            }
            // else if($type==4){
            //     if(substr($strval,0,2)=='30') { //可以再分解
            //         return self::decode($strval, 'hex');
            //     } else {
            //         $rt = $strval;
            //     }
            // }
            else {
                $rt = $strval;
            }
            return $rt;
            // $name = Identifier::getShortName($type);
            // $name = str_replace(" ", "-", $name);
            // return "{$name}($type)_$rt";
        }
    }

    public static function printObject2(ASNObject $object, $depth = 0)
    {
        $treeSymbol = '';
        $depthString = str_repeat('─', $depth);
        if ($depth > 0) {
            $treeSymbol = '├';
        }
        // $type = $object->getType();
        $name = Identifier::getShortName($object->getType());
        echo "{$treeSymbol}{$depthString}{$name}: ";
        $strval = $object->__toString();
        $result[] = $strval;
        echo $object->__toString() . PHP_EOL;

        $content = $object->getContent();
        // print_R($content);
        if (is_array($content)) {
            foreach ($object as $child) {
                self::printObject2($child, $depth + 1);
            }
        }
    }

    protected static function pem2der($pem_data)
    {
        $begin = "-----";
        $end   = "-----END";
        $pem_data = substr($pem_data, strpos($pem_data, $begin, 6) + strlen($begin));
        $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
        $der = base64_decode($pem_data);
        return $der;
    }
    /**
     * 大数都转成16进制
     *
     * @param bigint|string $data
     * @return string
     */
    protected static function format_bigint($data)
    {
        $hex = gmp_strval(gmp_init($data, 10), 16);
        return self::padding_one_zero($hex);
    }
    public static function padding_one_zero($hex)
    {
        if (strlen($hex) % 2 == 1) {
            $hex =  '0' . $hex;
        }
        return $hex;
    }

    public static function padding_zero($hex, $len = 64)
    {
        $left = $len - strlen($hex);
        if ($left > 0) {
            $hex = str_repeat('0', $left) . $hex;
        }
        return $hex;
    }
    /**
     * from  https://github.com/vakata/asn1
     *
     * @var array
     */
    public static $oids = array(
        'sm2' => '1.2.156.10197.1.301',
        'sm3WithSM2Encryption' => '1.2.156.10197.1.501',
        'sha1' =>                 '1.3.14.3.2.26',
        'sha256' =>               '2.16.840.1.101.3.4.2.1',
        'sha384' =>               '2.16.840.1.101.3.4.2.2',
        'sha512' =>               '2.16.840.1.101.3.4.2.3',
        'sha224' =>               '2.16.840.1.101.3.4.2.4',
        'md5' =>                  '1.2.840.113549.2.5',
        'md2' =>                  '1.3.14.7.2.2.1',
        'ripemd160' =>            '1.3.36.3.2.1',
        'MD4withRSA' =>           '1.2.840.113549.1.1.3',
        'SHA1withECDSA' =>        '1.2.840.10045.4.1',
        'SHA224withECDSA' =>      '1.2.840.10045.4.3.1',
        'SHA256withECDSA' =>      '1.2.840.10045.4.3.2',
        'SHA384withECDSA' =>      '1.2.840.10045.4.3.3',
        'SHA512withECDSA' =>      '1.2.840.10045.4.3.4',
        'dsa' =>                  '1.2.840.10040.4.1',
        'SHA1withDSA' =>          '1.2.840.10040.4.3',
        'SHA224withDSA' =>        '2.16.840.1.101.3.4.3.1',
        'SHA256withDSA' =>        '2.16.840.1.101.3.4.3.2',
        'rsaEncryption' =>        '1.2.840.113549.1.1.1',
        'countryName' =>          '2.5.4.6',
        'organization' =>         '2.5.4.10',
        'organizationalUnit' =>   '2.5.4.11',
        'stateOrProvinceName' =>  '2.5.4.8',
        'locality' =>             '2.5.4.7',
        'commonName' =>           '2.5.4.3',
        'subjectKeyIdentifier' => '2.5.29.14',
        'keyUsage' =>             '2.5.29.15',
        'subjectAltName' =>       '2.5.29.17',
        'basicConstraints' =>     '2.5.29.19',
        'nameConstraints' =>      '2.5.29.30',
        'cRLDistributionPoints' => '2.5.29.31',
        'certificatePolicies' =>  '2.5.29.32',
        'authorityKeyIdentifier' => '2.5.29.35',
        'policyConstraints' =>    '2.5.29.36',
        'extKeyUsage' =>          '2.5.29.37',
        'authorityInfoAccess' =>  '1.3.6.1.5.5.7.1.1',
        'anyExtendedKeyUsage' =>  '2.5.29.37.0',
        'serverAuth' =>           '1.3.6.1.5.5.7.3.1',
        'clientAuth' =>           '1.3.6.1.5.5.7.3.2',
        'codeSigning' =>          '1.3.6.1.5.5.7.3.3',
        'emailProtection' =>      '1.3.6.1.5.5.7.3.4',
        'timeStamping' =>         '1.3.6.1.5.5.7.3.8',
        'ocspSigning' =>          '1.3.6.1.5.5.7.3.9',
        'ecPublicKey' =>          '1.2.840.10045.2.1',
        'secp256r1' =>            '1.2.840.10045.3.1.7',
        'secp256k1' =>            '1.3.132.0.10',
        'secp384r1' =>            '1.3.132.0.34',
        'pkcs5PBES2' =>           '1.2.840.113549.1.5.13',
        'pkcs5PBKDF2' =>          '1.2.840.113549.1.5.12',
        'des-EDE3-CBC' =>         '1.2.840.113549.3.7',
        'data' =>                 '1.2.840.113549.1.7.1', // CMS data
        'signed-data' =>          '1.2.840.113549.1.7.2', // CMS signed-data
        'enveloped-data' =>       '1.2.840.113549.1.7.3', // CMS enveloped-data
        'digested-data' =>        '1.2.840.113549.1.7.5', // CMS digested-data
        'encrypted-data' =>       '1.2.840.113549.1.7.6', // CMS encrypted-data
        'authenticated-data' =>   '1.2.840.113549.1.9.16.1.2', // CMS authenticated-data
        'tstinfo' =>              '1.2.840.113549.1.9.16.1.4', // RFC3161 TSTInfo,
        'pkix' => '1.3.6.1.5.5.7',
        'pe' => '1.3.6.1.5.5.7.1',
        'qt' => '1.3.6.1.5.5.7.2',
        'kp' => '1.3.6.1.5.5.7.3',
        'ad' => '1.3.6.1.5.5.7.48',
        'cps' => '1.3.6.1.5.5.7.2.1',
        'unotice' => '1.3.6.1.5.5.7.2.2',
        'ocsp' => '1.3.6.1.5.5.7.48.1',
        'caIssuers' => '1.3.6.1.5.5.7.48.2',
        'timeStamping' => '1.3.6.1.5.5.7.48.3',
        'caRepository' => '1.3.6.1.5.5.7.48.5',
        'at' => '2.5.4',
        'name' => '2.5.4.41',
        'surname' => '2.5.4.4',
        'givenName' => '2.5.4.42',
        'initials' => '2.5.4.43',
        'generationQualifier' => '2.5.4.44',
        'commonName' => '2.5.4.3',
        'localityName' => '2.5.4.7',
        'stateOrProvinceName' => '2.5.4.8',
        'organizationName' => '2.5.4.10',
        'organizationalUnitName' => '2.5.4.11',
        'title' => '2.5.4.12',
        'description' => '2.5.4.13',
        'dnQualifier' => '2.5.4.46',
        'countryName' => '2.5.4.6',
        'serialNumber' => '2.5.4.5',
        'pseudonym' => '2.5.4.65',
        'postalCode' => '2.5.4.17',
        'streetAddress' => '2.5.4.9',
        'uniqueIdentifier' => '2.5.4.45',
        'role' => '2.5.4.72',
        'postalAddress' => '2.5.4.16',
        'domainComponent' => '0.9.2342.19200300.100.1.25',
        'pkcs-9' => '1.2.840.113549.1.9',
        'emailAddress' => '1.2.840.113549.1.9.1',
        'ce' => '2.5.29',
        'authorityKeyIdentifier' => '2.5.29.35',
        'subjectKeyIdentifier' => '2.5.29.14',
        'keyUsage' => '2.5.29.15',
        'privateKeyUsagePeriod' => '2.5.29.16',
        'certificatePolicies' => '2.5.29.32',
        'anyPolicy' => '2.5.29.32.0',
        'policyMappings' => '2.5.29.33',
        'subjectAltName' => '2.5.29.17',
        'issuerAltName' => '2.5.29.18',
        'subjectDirectoryAttributes' => '2.5.29.9',
        'basicConstraints' => '2.5.29.19',
        'nameConstraints' => '2.5.29.30',
        'policyConstraints' => '2.5.29.36',
        'cRLDistributionPoints' => '2.5.29.31',
        'extKeyUsage' => '2.5.29.37',
        'anyExtendedKeyUsage' => '2.5.29.37.0',
        'kp-serverAuth' => '1.3.6.1.5.5.7.3.1',
        'kp-clientAuth' => '1.3.6.1.5.5.7.3.2',
        'kp-codeSigning' => '1.3.6.1.5.5.7.3.3',
        'kp-emailProtection' => '1.3.6.1.5.5.7.3.4',
        'kp-timeStamping' => '1.3.6.1.5.5.7.3.8',
        'kp-OCSPSigning' => '1.3.6.1.5.5.7.3.9',
        'inhibitAnyPolicy' => '2.5.29.54',
        'freshestCRL' => '2.5.29.46',
        'pe-authorityInfoAccess' => '1.3.6.1.5.5.7.1.1',
        'pe-subjectInfoAccess' => '1.3.6.1.5.5.7.1.11',
        'cRLNumber' => '2.5.29.20',
        'issuingDistributionPoint' => '2.5.29.28',
        'deltaCRLIndicator' => '2.5.29.27',
        'cRLReasons' => '2.5.29.21',
        'certificateIssuer' => '2.5.29.29',
        'holdInstructionCode' => '2.5.29.23',
        'holdInstruction' => '1.2.840.10040.2',
        'holdinstruction-none' => '1.2.840.10040.2.1',
        'holdinstruction-callissuer' => '1.2.840.10040.2.2',
        'holdinstruction-reject' => '1.2.840.10040.2.3',
        'invalidityDate' => '2.5.29.24',
        'md2' => '1.2.840.113549.2.2',
        'md5' => '1.2.840.113549.2.5',
        'sha1' => '1.3.14.3.2.26',
        'dsa' => '1.2.840.10040.4.1',
        'dsa-with-sha1' => '1.2.840.10040.4.3',
        'pkcs-1' => '1.2.840.113549.1.1',
        'rsaEncryption' => '1.2.840.113549.1.1.1',
        'md2WithRSAEncryption' => '1.2.840.113549.1.1.2',
        'md5WithRSAEncryption' => '1.2.840.113549.1.1.4',
        'sha1WithRSAEncryption' => ['1.2.840.113549.1.1.5', '1.3.14.3.2.29'],
        'dhpublicnumber' => '1.2.840.10046.2.1',
        'keyExchangeAlgorithm' => '2.16.840.1.101.2.1.1.22',
        'ansi-X9-62' => '1.2.840.10045',
        'ecSigType' => '1.2.840.10045.4',
        'ecdsa-with-SHA1' => '1.2.840.10045.4.1',
        'fieldType' => '1.2.840.10045.1',
        'prime-field' => '1.2.840.10045.1.1',
        'characteristic-two-field' => '1.2.840.10045.1.2',
        'characteristic-two-basis' => '1.2.840.10045.1.2.3',
        'gnBasis' => '1.2.840.10045.1.2.3.1',
        'tpBasis' => '1.2.840.10045.1.2.3.2',
        'ppBasis' => '1.2.840.10045.1.2.3.3',
        'publicKeyType' => '1.2.840.10045.2',
        'ecPublicKey' => '1.2.840.10045.2.1',
        'ellipticCurve' => '1.2.840.10045.3',
        'c-TwoCurve' => '1.2.840.10045.3.0',
        'c2pnb163v1' => '1.2.840.10045.3.0.1',
        'c2pnb163v2' => '1.2.840.10045.3.0.2',
        'c2pnb163v3' => '1.2.840.10045.3.0.3',
        'c2pnb176w1' => '1.2.840.10045.3.0.4',
        'c2pnb191v1' => '1.2.840.10045.3.0.5',
        'c2pnb191v2' => '1.2.840.10045.3.0.6',
        'c2pnb191v3' => '1.2.840.10045.3.0.7',
        'c2pnb191v4' => '1.2.840.10045.3.0.8',
        'c2pnb191v5' => '1.2.840.10045.3.0.9',
        'c2pnb208w1' => '1.2.840.10045.3.0.10',
        'c2pnb239v1' => '1.2.840.10045.3.0.11',
        'c2pnb239v2' => '1.2.840.10045.3.0.12',
        'c2pnb239v3' => '1.2.840.10045.3.0.13',
        'c2pnb239v4' => '1.2.840.10045.3.0.14',
        'c2pnb239v5' => '1.2.840.10045.3.0.15',
        'c2pnb272w1' => '1.2.840.10045.3.0.16',
        'c2pnb304w1' => '1.2.840.10045.3.0.17',
        'c2pnb359v1' => '1.2.840.10045.3.0.18',
        'c2pnb368w1' => '1.2.840.10045.3.0.19',
        'c2pnb431r1' => '1.2.840.10045.3.0.20',
        'primeCurve' => '1.2.840.10045.3.1',
        'prime192v1' => '1.2.840.10045.3.1.1',
        'prime192v2' => '1.2.840.10045.3.1.2',
        'prime192v3' => '1.2.840.10045.3.1.3',
        'prime239v1' => '1.2.840.10045.3.1.4',
        'prime239v2' => '1.2.840.10045.3.1.5',
        'prime239v3' => '1.2.840.10045.3.1.6',
        'prime256v1' => '1.2.840.10045.3.1.7',
        'RSAES-OAEP' => '1.2.840.113549.1.1.7',
        'pSpecified' => '1.2.840.113549.1.1.9',
        'RSASSA-PSS' => '1.2.840.113549.1.1.10',
        'mgf1' => '1.2.840.113549.1.1.8',
        'sha224WithRSAEncryption' => '1.2.840.113549.1.1.14',
        'sha256WithRSAEncryption' => '1.2.840.113549.1.1.11',
        'sha384WithRSAEncryption' => '1.2.840.113549.1.1.12',
        'sha512WithRSAEncryption' => '1.2.840.113549.1.1.13',
        'sha224' => '2.16.840.1.101.3.4.2.4',
        'sha256' => '2.16.840.1.101.3.4.2.1',
        'sha384' => '2.16.840.1.101.3.4.2.2',
        'sha512' => '2.16.840.1.101.3.4.2.3',
        'GostR3411-94-with-GostR3410-94' => '1.2.643.2.2.4',
        'GostR3411-94-with-GostR3410-2001' => '1.2.643.2.2.3',
        'GostR3410-2001' => '1.2.643.2.2.20',
        'GostR3410-94' => '1.2.643.2.2.19',
        'netscape' => '2.16.840.1.113730',
        'netscape-cert-extension' => '2.16.840.1.113730.1',
        'netscape-cert-type' => '2.16.840.1.113730.1.1',
        'netscape-comment' => '2.16.840.1.113730.1.13',
        'netscape-ca-policy-url' => '2.16.840.1.113730.1.8',
        'logotype' => '1.3.6.1.5.5.7.1.12',
        'entrustVersInfo' => '1.2.840.113533.7.65.0',
        'verisignPrivate' => '2.16.840.1.113733.1.6.9',
        'unstructuredName' => '1.2.840.113549.1.9.2',
        'challengePassword' => '1.2.840.113549.1.9.7',
        'extensionRequest' => '1.2.840.113549.1.9.14',
        'userid' => '0.9.2342.19200300.100.1.1',
        's/mime' => '1.2.840.113549.1.9.15',
        'unstructuredAddress' => '1.2.840.113549.1.9.8',
        'rc2-cbc' => '1.2.840.113549.3.2',
        'rc4' => '1.2.840.113549.3.4',
        'desCBC' => '1.3.14.3.2.7',
        'qcStatements' => '1.3.6.1.5.5.7.1.3',
        'pkixQCSyntax-v1' => '1.3.6.1.5.5.7.11.1',
        'pkixQCSyntax-v2' => '1.3.6.1.5.5.7.11.2',
        'ipsecEndSystem' => '1.3.6.1.5.5.7.3.5',
        'ipsecTunnel' => '1.3.6.1.5.5.7.3.6',
        'ipsecUser' => '1.3.6.1.5.5.7.3.7',
        'OCSP' => '1.3.6.1.5.5.7.48.1',
        'countryOfCitizenship' => '1.3.6.1.5.5.7.9.4',
        'IPSECProtection' => '1.3.6.1.5.5.8.2.2',
        'telephoneNumber' => '2.5.4.20',
        'organizationIdentifier' => '2.5.4.97',

    );

    public static $oidTexts = array(
        '1.2.156.10197.1.301' => 'sm2',
        '1.2.156.10197.1.501' => 'sm3WithSM2Encryption',
        '1.3.14.3.2.26' => 'sha1',
        '2.16.840.1.101.3.4.2.1' => 'sha256',
        '2.16.840.1.101.3.4.2.2' => 'sha384',
        '2.16.840.1.101.3.4.2.3' => 'sha512',
        '2.16.840.1.101.3.4.2.4' => 'sha224',
        '1.2.840.113549.2.5' => 'md5',
        '1.2.840.113549.2.2' => 'md2',
        '1.3.36.3.2.1' => 'ripemd160',
        '1.2.840.113549.1.1.3' => 'MD4withRSA',
        '1.2.840.10045.4.1' => 'SHA1withECDSA',
        '1.2.840.10045.4.3.1' => 'SHA224withECDSA',
        '1.2.840.10045.4.3.2' => 'SHA256withECDSA',
        '1.2.840.10045.4.3.3' => 'SHA384withECDSA',
        '1.2.840.10045.4.3.4' => 'SHA512withECDSA',
        '1.2.840.10040.4.1' => 'dsa',
        '1.2.840.10040.4.3' => 'SHA1withDSA',
        '2.16.840.1.101.3.4.3.1' => 'SHA224withDSA',
        '2.16.840.1.101.3.4.3.2' => 'SHA256withDSA',
        '1.2.840.113549.1.1.1' => 'rsaEncryption',
        '2.5.4.6' => 'countryName',
        '2.5.4.10' => 'organization',
        '2.5.4.11' => 'organizationalUnit',
        '2.5.4.8' => 'stateOrProvinceName',
        '2.5.4.7' => 'locality',
        '2.5.4.3' => 'commonName',
        '2.5.29.14' => 'subjectKeyIdentifier',
        '2.5.29.15' => 'keyUsage',
        '2.5.29.17' => 'subjectAltName',
        '2.5.29.19' => 'basicConstraints',
        '2.5.29.30' => 'nameConstraints',
        '2.5.29.31' => 'cRLDistributionPoints',
        '2.5.29.32' => 'certificatePolicies',
        '2.5.29.35' => 'authorityKeyIdentifier',
        '2.5.29.36' => 'policyConstraints',
        '2.5.29.37' => 'extKeyUsage',
        '1.3.6.1.5.5.7.1.1' => 'authorityInfoAccess',
        '2.5.29.37.0' => 'anyExtendedKeyUsage',
        '1.3.6.1.5.5.7.3.1' => 'serverAuth',
        '1.3.6.1.5.5.7.3.2' => 'clientAuth',
        '1.3.6.1.5.5.7.3.3' => 'codeSigning',
        '1.3.6.1.5.5.7.3.4' => 'emailProtection',
        '1.3.6.1.5.5.7.48.3' => 'timeStamping',
        '1.3.6.1.5.5.7.3.9' => 'ocspSigning',
        '1.2.840.10045.2.1' => 'ecPublicKey',
        '1.2.840.10045.3.1.7' => 'secp256r1',
        '1.3.132.0.10' => 'secp256k1',
        '1.3.132.0.34' => 'secp384r1',
        '1.2.840.113549.1.5.13' => 'pkcs5PBES2',
        '1.2.840.113549.1.5.12' => 'pkcs5PBKDF2',
        '1.2.840.113549.3.7' => 'des-EDE3-CBC',
        '1.2.840.113549.1.7.1' => 'data',
        '1.2.840.113549.1.7.2' => 'signed-data',
        '1.2.840.113549.1.7.3' => 'enveloped-data',
        '1.2.840.113549.1.7.5' => 'digested-data',
        '1.2.840.113549.1.7.6' => 'encrypted-data',
        '1.2.840.113549.1.9.16.1.2' => 'authenticated-data',
        '1.2.840.113549.1.9.16.1.4' => 'tstinfo',
        '1.3.6.1.5.5.7' => 'pkix',
        '1.3.6.1.5.5.7.1' => 'pe',
        '1.3.6.1.5.5.7.2' => 'qt',
        '1.3.6.1.5.5.7.3' => 'kp',
        '1.3.6.1.5.5.7.48' => 'ad',
        '1.3.6.1.5.5.7.2.1' => 'cps',
        '1.3.6.1.5.5.7.2.2' => 'unotice',
        '1.3.6.1.5.5.7.48.1' => 'ocsp',
        '1.3.6.1.5.5.7.48.2' => 'caIssuers',
        '1.3.6.1.5.5.7.48.5' => 'caRepository',
        '2.5.4' => 'at',
        '2.5.4.41' => 'name',
        '2.5.4.4' => 'surname',
        '2.5.4.42' => 'givenName',
        '2.5.4.43' => 'initials',
        '2.5.4.44' => 'generationQualifier',
        '2.5.4.7' => 'localityName',
        '2.5.4.10' => 'organizationName',
        '2.5.4.11' => 'organizationalUnitName',
        '2.5.4.12' => 'title',
        '2.5.4.13' => 'description',
        '2.5.4.46' => 'dnQualifier',
        '2.5.4.5' => 'serialNumber',
        '2.5.4.65' => 'pseudonym',
        '2.5.4.17' => 'postalCode',
        '2.5.4.9' => 'streetAddress',
        '2.5.4.45' => 'uniqueIdentifier',
        '2.5.4.72' => 'role',
        '2.5.4.16' => 'postalAddress',
        '0.9.2342.19200300.100.1.25' => 'domainComponent',
        '1.2.840.113549.1.9' => 'pkcs-9',
        '1.2.840.113549.1.9.1' => 'emailAddress',
        '2.5.29' => 'ce',
        '2.5.29.16' => 'privateKeyUsagePeriod',
        '2.5.29.32.0' => 'anyPolicy',
        '2.5.29.33' => 'policyMappings',
        '2.5.29.18' => 'issuerAltName',
        '2.5.29.9' => 'subjectDirectoryAttributes',
        '1.3.6.1.5.5.7.3.1' => 'kp-serverAuth',
        '1.3.6.1.5.5.7.3.2' => 'kp-clientAuth',
        '1.3.6.1.5.5.7.3.3' => 'kp-codeSigning',
        '1.3.6.1.5.5.7.3.4' => 'kp-emailProtection',
        '1.3.6.1.5.5.7.3.8' => 'kp-timeStamping',
        '1.3.6.1.5.5.7.3.9' => 'kp-OCSPSigning',
        '2.5.29.54' => 'inhibitAnyPolicy',
        '2.5.29.46' => 'freshestCRL',
        '1.3.6.1.5.5.7.1.1' => 'pe-authorityInfoAccess',
        '1.3.6.1.5.5.7.1.11' => 'pe-subjectInfoAccess',
        '2.5.29.20' => 'cRLNumber',
        '2.5.29.28' => 'issuingDistributionPoint',
        '2.5.29.27' => 'deltaCRLIndicator',
        '2.5.29.21' => 'cRLReasons',
        '2.5.29.29' => 'certificateIssuer',
        '2.5.29.23' => 'holdInstructionCode',
        '1.2.840.10040.2' => 'holdInstruction',
        '1.2.840.10040.2.1' => 'holdinstruction-none',
        '1.2.840.10040.2.2' => 'holdinstruction-callissuer',
        '1.2.840.10040.2.3' => 'holdinstruction-reject',
        '2.5.29.24' => 'invalidityDate',
        '1.2.840.10040.4.3' => 'dsa-with-sha1',
        '1.2.840.113549.1.1' => 'pkcs-1',
        '1.2.840.113549.1.1.2' => 'md2WithRSAEncryption',
        '1.2.840.113549.1.1.4' => 'md5WithRSAEncryption',
        '1.2.840.113549.1.1.5' => 'sha1WithRSAEncryption',
        '1.3.14.3.2.29' => 'sha1WithRSAEncryption',
        '1.2.840.10046.2.1' => 'dhpublicnumber',
        '2.16.840.1.101.2.1.1.22' => 'keyExchangeAlgorithm',
        '1.2.840.10045' => 'ansi-X9-62',
        '1.2.840.10045.4' => 'ecSigType',
        '1.2.840.10045.4.1' => 'ecdsa-with-SHA1',
        '1.2.840.10045.1' => 'fieldType',
        '1.2.840.10045.1.1' => 'prime-field',
        '1.2.840.10045.1.2' => 'characteristic-two-field',
        '1.2.840.10045.1.2.3' => 'characteristic-two-basis',
        '1.2.840.10045.1.2.3.1' => 'gnBasis',
        '1.2.840.10045.1.2.3.2' => 'tpBasis',
        '1.2.840.10045.1.2.3.3' => 'ppBasis',
        '1.2.840.10045.2' => 'publicKeyType',
        '1.2.840.10045.3' => 'ellipticCurve',
        '1.2.840.10045.3.0' => 'c-TwoCurve',
        '1.2.840.10045.3.0.1' => 'c2pnb163v1',
        '1.2.840.10045.3.0.2' => 'c2pnb163v2',
        '1.2.840.10045.3.0.3' => 'c2pnb163v3',
        '1.2.840.10045.3.0.4' => 'c2pnb176w1',
        '1.2.840.10045.3.0.5' => 'c2pnb191v1',
        '1.2.840.10045.3.0.6' => 'c2pnb191v2',
        '1.2.840.10045.3.0.7' => 'c2pnb191v3',
        '1.2.840.10045.3.0.8' => 'c2pnb191v4',
        '1.2.840.10045.3.0.9' => 'c2pnb191v5',
        '1.2.840.10045.3.0.10' => 'c2pnb208w1',
        '1.2.840.10045.3.0.11' => 'c2pnb239v1',
        '1.2.840.10045.3.0.12' => 'c2pnb239v2',
        '1.2.840.10045.3.0.13' => 'c2pnb239v3',
        '1.2.840.10045.3.0.14' => 'c2pnb239v4',
        '1.2.840.10045.3.0.15' => 'c2pnb239v5',
        '1.2.840.10045.3.0.16' => 'c2pnb272w1',
        '1.2.840.10045.3.0.17' => 'c2pnb304w1',
        '1.2.840.10045.3.0.18' => 'c2pnb359v1',
        '1.2.840.10045.3.0.19' => 'c2pnb368w1',
        '1.2.840.10045.3.0.20' => 'c2pnb431r1',
        '1.2.840.10045.3.1' => 'primeCurve',
        '1.2.840.10045.3.1.1' => 'prime192v1',
        '1.2.840.10045.3.1.2' => 'prime192v2',
        '1.2.840.10045.3.1.3' => 'prime192v3',
        '1.2.840.10045.3.1.4' => 'prime239v1',
        '1.2.840.10045.3.1.5' => 'prime239v2',
        '1.2.840.10045.3.1.6' => 'prime239v3',
        '1.2.840.10045.3.1.7' => 'prime256v1',
        '1.2.840.113549.1.1.7' => 'RSAES-OAEP',
        '1.2.840.113549.1.1.9' => 'pSpecified',
        '1.2.840.113549.1.1.10' => 'RSASSA-PSS',
        '1.2.840.113549.1.1.8' => 'mgf1',
        '1.2.840.113549.1.1.14' => 'sha224WithRSAEncryption',
        '1.2.840.113549.1.1.11' => 'sha256WithRSAEncryption',
        '1.2.840.113549.1.1.12' => 'sha384WithRSAEncryption',
        '1.2.840.113549.1.1.13' => 'sha512WithRSAEncryption',
        '1.2.643.2.2.4' => 'GostR3411-94-with-GostR3410-94',
        '1.2.643.2.2.3' => 'GostR3411-94-with-GostR3410-2001',
        '1.2.643.2.2.20' => 'GostR3410-2001',
        '1.2.643.2.2.19' => 'GostR3410-94',
        '2.16.840.1.113730' => 'netscape',
        '2.16.840.1.113730.1' => 'netscape-cert-extension',
        '2.16.840.1.113730.1.1' => 'netscape-cert-type',
        '2.16.840.1.113730.1.13' => 'netscape-comment',
        '2.16.840.1.113730.1.8' => 'netscape-ca-policy-url',
        '1.3.6.1.5.5.7.1.12' => 'logotype',
        '1.2.840.113533.7.65.0' => 'entrustVersInfo',
        '2.16.840.1.113733.1.6.9' => 'verisignPrivate',
        '1.2.840.113549.1.9.2' => 'unstructuredName',
        '1.2.840.113549.1.9.7' => 'challengePassword',
        '1.2.840.113549.1.9.14' => 'extensionRequest',
        '0.9.2342.19200300.100.1.1' => 'userid',
        '1.2.840.113549.1.9.15' => 's/mime',
        '1.2.840.113549.1.9.8' => 'unstructuredAddress',
        '1.2.840.113549.3.2' => 'rc2-cbc',
        '1.2.840.113549.3.4' => 'rc4',
        '1.3.14.3.2.7' => 'desCBC',
        '1.3.6.1.5.5.7.1.3' => 'qcStatements',
        '1.3.6.1.5.5.7.11.1' => 'pkixQCSyntax-v1',
        '1.3.6.1.5.5.7.11.2' => 'pkixQCSyntax-v2',
        '1.3.6.1.5.5.7.3.5' => 'ipsecEndSystem',
        '1.3.6.1.5.5.7.3.6' => 'ipsecTunnel',
        '1.3.6.1.5.5.7.3.7' => 'ipsecUser',
        '1.3.6.1.5.5.7.48.1' => 'OCSP',
        '1.3.6.1.5.5.7.9.4' => 'countryOfCitizenship',
        '1.3.6.1.5.5.8.2.2' => 'IPSECProtection',
        '2.5.4.20' => 'telephoneNumber',
        '2.5.4.97' => 'organizationIdentifier',

    );
    /**
     * from  https://github.com/vakata/asn1
     *
     * @param string $id
     * @return string
     */
    public static function OIDtoText($id)
    {
        // echo $id."\n";
        $text = self::$oidTexts[$id] ?? $id;

        return $text;
    }
    /**
     * from  https://github.com/vakata/asn1
     *
     * @param string $text
     * @return string
     */
    public static function TextToOID($text)
    {
        $res = static::$oids[$text] ?? null;
        if (is_array($res)) {
            $res = $res[0];
        }
        return $res ?? $text;
    }
}

function getMillisecond()
{
    list($microsecond, $time) = explode(' ', microtime()); //' '中间是一个空格
    return (float)sprintf('%.0f', (floatval($microsecond) + floatval($time)) * 1000);
}
