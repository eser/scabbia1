# Http Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)

## Rutin ## {#routine}

+ yüklendiğinde config dosyalarından konfigurasyon bilgilerini okur.
+ output client'a gönderilmeden önce giden pakette değişiklikler gerçekleştirir.


## Alanlar ## {#fields}
[$platform](#platform)
[$crawler](#crawler)
[$crawlerType](#crawlerType)
[$isSecure](#isSecure)
[$isAjax](#isAjax)
[$isGet](#isGet)
[$isPost](#isPost)
[$isBrowser](#isBrowser)
[$isRobot](#isRobot)
[$isMobile](#isMobile)
[$languages](#languages)
[$contentTypes](#contentTypes)

### $platform ### {#platform}
{string} http::$platform

> Client'a ait platform bilgisi.

### $crawler ### {#crawler}
{string} http::$crawler

> Client'a ait crawler bilgisi.

### $crawlerType ### {#crawlerType}
{string} http::$crawlerType

> Client'a ait crawler tip bilgisi.

### $isSecure ### {#isSecure}
{boolean} http::$isSecure

> Client'ın HTTP üzerinden girip girmediği bilgisi.

### $isAjax ### {#isAjax}
{boolean} http::$isAjax

> Client'ın ajax ile istekte bulunup bulunmadığı bilgisi.

### $isGet ### {#isGet}
{boolean} http::$isGet

> Client'ın http get metodu ile istekte bulunup bulunmadığı bilgisi.

### $isPost ### {#isPost}
{boolean} http::$isPost

> Client'ın http post metodu ile istekte bulunup bulunmadığı bilgisi.

### $isBrowser ### {#isBrowser}
{boolean} http::$isBrowser

> Client'ın browser üzerinden istekte bulunup bulunmadığı bilgisi.

### $isRobot ### {#isRobot}
{boolean} http::$isBrowser

> Client'ın arama motoru robotu olup olmadığı bilgisi.

### $isMobile ### {#isMobile}
{boolean} http::$isMobile

> Client'ın mobil bir cihazdan bağlanıp bağlanmadığı bilgisi.

### $languages ### {#languages}
{array} http::$languages

> Client'ın desteklediği dillerin bilgisi.

### $contentTypes ### {#contentTypes}
{array} http::$contentTypes

> Client'ın desteklediği content-type'ların bilgisi.


## Metodlar ## {#methods}
[checkUserAgent](#checkUserAgent)
[checkLanguage](#checkLanguage)
[checkContentType](#checkContentType)
[xss](#xss)
[encode](#encode)
[decode](#decode)
[encodeArray](#encodeArray)
[decodeArray](#decodeArray)
[copyStream](#copyStream)
[baseUrl](#baseUrl)
[secureUrl](#secureUrl)
[sendStatus](#sendStatus)
[sendStatus404](#sendStatus404)
[sendHeader](#sendHeader)
[sentHeaderValue](#sentHeaderValue)
[sendFile](#sendFile)
[sendHeaderLastModified](#sendHeaderLastModified)
[sendHeaderExpires](#sendHeaderExpires)
[sendRedirect](#sendRedirect)
[sendRedirectPermanent](#sendRedirectPermanent)
[sendHeaderETag](#sendHeaderETag)
[sendHeaderNoCache](#sendHeaderNoCache)
[sendCookie](#sendCookie)
[removeCookie](#removeCookie)
[parseGet](#parseGet)
[parseHeaderString](#parseHeaderString)
[get](#get)
[post](#post)
[cookie](#cookie)

### checkUserAgent ### {#checkUserAgent}
{void} http::checkUserAgent()

> Client'a ait bilgileri edinir. (Konfigurasyon bilgileri doğrultusunda yüklenme esnasında otomatik olarak çalışabilir.)

### checkLanguage ### {#checkLanguage}
{boolean} http::checkLanguage([$language = null])

> Parametre olarak belirtilen dilin client'in desteklediği diller arasında olup olmadığını kontrol eder.

~~~

// Komut:
http::checkLanguage('en')

// Sonuç:
boolean(true)

~~~

### checkContentType ### {#checkContentType}
{boolean} http::checkContentType([$contentType = null])

> Parametre olarak belirtilen content-type'ın client'in desteklediği content-type'lar arasında olup olmadığını kontrol eder.

~~~

// Komut:
http::checkContentType('text/html')

// Sonuç:
boolean(true)

~~~

### xss ### {#xss}
{string} http::xss($string)

> Crosssite scripting (XSS) için gerekli input filtrelemesini gerçekleştirir.

~~~

// Komut:
$test = '<script language="text/javascript"><![CDATA[ document.write(\'testing\'); ]]></script>';
http::xss($test)

// Sonuç:
string('&#60;script language=&#34;text/javascript&#34;&#62;&#60;![CDATA[ document.write&#40;&#39;testing&#39;&#41;; ]]&#62;&#60;/script&#62;')

~~~

### encode ### {#encode}
{string} http::encode($string)

> Parametre olarak gönderilen string'i URL encoding işleminden geçirir.

~~~

// Komut:
http::encode('laroux d\'blackmore')

// Sonuç:
string('laroux+d%27blackmore')

~~~

### decode ### {#decode}
{string} http::decode($string)

> Parametre olarak gönderilen string'i URL decoding işleminden geçirir.

~~~

// Komut:
http::decode('laroux+d%27blackmore')

// Sonuç:
string('laroux d\'blackmore')

~~~

### encodeArray ### {#encodeArray}
{string} http::encodeArray($array)

> Parametre olarak gönderilen array elemanlarını URL encoding işleminden geçirir.

~~~

// Komut:
http::encodeArray(array(
	'username' => 'laroux d\'blackmore',
	'remove' => '0'
))

// Sonuç:
string('username=laroux+d%27blackmore&remove=0')

~~~

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com