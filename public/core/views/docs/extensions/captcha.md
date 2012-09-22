# Captcha Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)

## Rutin ## {#routine}

+ yüklendiğinde config dosyalarından konfigurasyon bilgilerini okur.

## Alanlar ## {#fields}
[$fontFile](#fontFile)
[$fontSize](#fontSize)
[$length](#length)

### $fontFile ### {#fontFile}
{string} captcha::$fontFile

> Captcha oluşturulurken kullanılacak font dosyası.

### $fontSize ### {#fontSize}
{int} captcha::$fontSize

> Captcha oluşturulurken kullanılacak font boyutu.

### $length ### {#length}
{int} captcha::$length

> Oluşturulacak captcha metninin uzunluğu.

## Metodlar ## {#methods}
[generate](#generate)
[check](#check)

### generate ### {#generate}
{string} captcha::generate([$cookieName = 'captcha'])

> Bir captcha oluşturur ve oluşan kodu geri döndürür.

### check ### {#check}
{bool} captcha::check($userInput, [$cookieName = 'captcha'])

> Kullanıcı tarafından girilen captcha kodunun geçerliliğini kontrol eder.

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com