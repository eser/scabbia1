# Contracts Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)
[Metodlar: contractObject](#methodsContractObject)

## Rutin ## {#routine}

## Alanlar ## {#fields}

## Metodlar ## {#methods}
[isExist](#isExist)
[isRequired](#isRequired)
[isBoolean](#isBoolean)
[isFloat](#isFloat)
[isInteger](#isInteger)
[isHex](#isHex)
[isOctal](#isOctal)
[isSlugString](#isSlugString)
[isDate](#isDate)
[isUuid](#isUuid)
[isEqual](#isEqual)
[isMinimum](#isMinimum)
[isMaximum](#isMaximum)
[isLower](#isLower)
[isGreater](#isGreater)
[isLength](#isLength)
[isLengthMinimum](#isLengthMinimum)
[isLengthMaximum](#isLengthMaximum)
[inArray](#inArray)
[regExp](#regExp)
[custom](#custom)
[isEmail](#isEmail)
[isUrl](#isUrl)
[isIpAddress](#isIpAddress)

### isExist ### {#isExist}
{contractObject} contracts::isExist($value)

> Belirtilen değer mevcut mu kontrol eder (set edilmiş mi).

### isRequired ### {#isRequired}
{contractObject} contracts::isRequired($value)

> Belirtilen değer girilmiş mi kontrol eder (uzunluğu 0'dan büyük mü).

~~~

// Komut:
$value = 'test';
contracts::isRequired($value)->check()

// Sonuç:
boolean(true)

~~~

### isBoolean ### {#isBoolean}
{contractObject} contracts::isBoolean($value)

> Belirtilen değerin bir boolean değeri olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'true';
contracts::isBoolean($value)->check()

// Sonuç:
boolean(true)

~~~

### isFloat ### {#isFloat}
{contractObject} contracts::isFloat($value)

> Belirtilen değerin bir float değeri olup olmadığını kontrol eder.

~~~

// Komut:
$value = 1.0;
contracts::isFloat($value)->check()

// Sonuç:
boolean(true)

~~~

### isInteger ### {#isInteger}
{contractObject} contracts::isInteger($value)

> Belirtilen değerin bir integer değeri olup olmadığını kontrol eder.

~~~

// Komut:
$value = 5;
contracts::isInteger($value)->check()

// Sonuç:
boolean(true)

~~~

### isHex ### {#isHex}
{contractObject} contracts::isHex($value)

> Belirtilen değerin onaltılık(hex) tabanda girilmiş bir integer değeri olup olmadığını kontrol eder.

~~~

// Komut:
$value = 0x1A;
contracts::isHex($value)->check()

// Sonuç:
boolean(true)

~~~

### isOctal ### {#isOctal}
{contractObject} contracts::isOctal($value)

> Belirtilen değerin sekizlik(octal) tabanda girilmiş karakterleri içeren bir integer değeri olup olmadığını kontrol eder.

~~~

// Komut:
$value = 0123;
contracts::isOctal($value)->check()

// Sonuç:
boolean(true)

~~~

### isSlugString ### {#isSlugString}
{contractObject} contracts::isSlugString($value)

> Belirtilen değerin alfanumerik bir karakter dizisi (- işareti dahil) olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'eserO-zvataf';
contracts::isSlugString($value)->check()

// Sonuç:
boolean(true)

~~~

### isDate ### {#isDate}
{contractObject} contracts::isDate($value, $format)

> Belirtilen değerin formatı parametre olarak belirtilmiş geçerli bir tarih olup olmadığını kontrol eder.

~~~

// Komut:
$value = '16/04/1984';
contracts::isDate($value, 'd/m/Y')->check()

// Sonuç:
boolean(true)

~~~

### isUuid ### {#isUuid}
{contractObject} contracts::isUuid($value)

> Belirtilen değerin universal identifier olup olmadığını kontrol eder.

~~~

// Komut:
$value = '0b913560-fced-11e1-a21f-0800200c9a66';
contracts::isUuid($value)->check()

// Sonuç:
boolean(true)

~~~

### isEqual ### {#isEqual}
{contractObject} contracts::isEqual($value, $values ...)

> Belirtilen değerin parametre olarak verilmiş değerlerden birine eşit olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'male';
contracts::isEqual($value, 'male', 'female')->check()

// Sonuç:
boolean(true)

~~~

### isMinimum ### {#isMinimum}
{contractObject} contracts::isMinimum($value, $minimum)

> Belirtilen değerin parametre olarak verilmiş minimum değerden yüksek veya eşit olup olmadığını kontrol eder.

~~~

// Komut:
$value = 3;
contracts::isMinimum($value, 3)->check()

// Sonuç:
boolean(true)

~~~

### isMaximum ### {#isMaximum}
{contractObject} contracts::isMaximum($value, $maximum)

> Belirtilen değerin parametre olarak verilmiş maximum değerden alçak veya eşit olup olmadığını kontrol eder.

~~~

// Komut:
$value = 10;
contracts::isMaximum($value, 10)->check()

// Sonuç:
boolean(true)

~~~

### isLower ### {#isLower}
{contractObject} contracts::isLower($value, $value2)

> Belirtilen değerin parametre olarak verilmiş değerden alçak olup olmadığını kontrol eder.

~~~

// Komut:
$value = 3;
contracts::isLower($value, 3)->check()

// Sonuç:
boolean(false)

~~~

### isGreater ### {#isGreater}
{contractObject} contracts::isGreater($value, $value2)

> Belirtilen değerin parametre olarak verilmiş değerden yüksek olup olmadığını kontrol eder.

~~~

// Komut:
$value = 10;
contracts::isGreater($value, 10)->check()

// Sonuç:
boolean(false)

~~~

### isLength ### {#isLength}
{contractObject} contracts::isLength($value, $length)

> Belirtilen değerin parametre olarak verilmiş uzunlukta olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'eser';
contracts::isLength($value, 4)->check()

// Sonuç:
boolean(true)

~~~

### isLengthMinimum ### {#isLengthMinimum}
{contractObject} contracts::isLengthMinimum($value, $minimum)

> Belirtilen değerin metin uzunluğunun parametre olarak verilmiş minimum değerden alçak veya eşit olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'esr';
contracts::isLengthMinimum($value, 4)->check()

// Sonuç:
boolean(false)

~~~

### isLengthMaximum ### {#isLengthMaximum}
{contractObject} contracts::isLengthMaximum($value, $maximum)

> Belirtilen değerin metin uzunluğunun parametre olarak verilmiş maximum değerden yüksek veya eşit olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'eser';
contracts::isLengthMaximum($value, 20)->check()

// Sonuç:
boolean(true)

~~~

### inArray ### {#inArray}
{contractObject} contracts::inArray($value, $array)

> Belirtilen değerin parametre olarak verilmiş array'in içerisinde yer alıp almadığını kontrol eder.

~~~

// Komut:
$value = 'eser';
contracts::inArray($value, array('eser', 'cengiz'))->check()

// Sonuç:
boolean(true)

~~~

### regExp ### {#regExp}
{contractObject} contracts::regExp($value, $regexp)

> Belirtilen değerin parametre olarak verilmiş regular expression tarafından validate edilip edilmediğini kontrol eder.

~~~

// Komut:
$value = '127.0.0.1';
contracts::regExp($value, '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/')->check()

// Sonuç:
boolean(true)

~~~

### custom ### {#custom}
{contractObject} contracts::custom($value, $callback)

> Belirtilen değerin parametre olarak verilmiş fonksiyon tarafından validate edilip edilmediğini kontrol eder.

~~~

// Komut:
$value = 'ALLCAPS';
contracts::custom($value, 'ctype_upper')->check()

// Sonuç:
boolean(true)

~~~

### isEmail ### {#isEmail}
{contractObject} contracts::isEmail($value)

> Belirtilen değerin e-mail adresi olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'eser@sent.com';
contracts::isEmail($value)->check()

// Sonuç:
boolean(true)

~~~

### isUrl ### {#isUrl}
{contractObject} contracts::isUrl($value)

> Belirtilen değerin url adresi olup olmadığını kontrol eder.

~~~

// Komut:
$value = 'http://www.opera.com/';
contracts::isUrl($value)->check()

// Sonuç:
boolean(true)

~~~

### isIpAddress ### {#isIpAddress}
{contractObject} contracts::isIpAddress($value)

> Belirtilen değerin ip adresi olup olmadığını kontrol eder.

~~~

// Komut:
$value = '192.168.0.1';
contracts::isIpAddress($value)->check()

// Sonuç:
boolean(true)

~~~

## Metodlar: contractObject ## {#methodsContractObject}
[exception](#exception)
[check](#check)

### exception ### {#exception}
{void} contractObject->exception($errorMessage)

> Eğer belirtilen şart gerçekleşmezse exception oluşturur.

### check ### {#check}
{bool} contractObject->check()

> Belirtilen şartın gerçekleşip gerçekleşmediğine dair true/false döndürür.

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com