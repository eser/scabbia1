# Cache Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)

## Rutin ## {#routine}

+ yüklendiğinde config dosyalarından konfigurasyon bilgilerini okur.

## Alanlar ## {#fields}
[$defaultAge](#defaultAge)
[$keyphase](#keyphase)
[$storage](#storage)

### $defaultAge ### {#defaultAge}
{int} cache::$defaultAge

> Cache'de verilerin varsayılan saklanma süresi.

### $keyphase ### {#keyphase}
{string} cache::$keyphase

> Cache'de saklanacak verilerin encryption'ı sırasında kullanılacak şifre.

### $storage ### {#storage}
{string} cache::$storage

> Kullanılacak key/value storage için bağlantı bilgileri.

## Metodlar ## {#methods}
[storageGet](#storageGet)
[storageSet](#storageSet)
[storageDestroy](#storageDestroy)
[filePath](#filePath)
[fileGet](#fileGet)
[fileGetUrl](#fileGetUrl)
[fileSet](#fileSet)
[fileDestroy](#fileDestroy)
[fileGarbageCollect](#fileGarbageCollect)

### storageGet ### {#storageGet}
{mixed} cache::storageGet($key)

> Key/value storage'dan belirtilen elemanı getirir.

### storageSet ### {#storageSet}
{void} cache::storageSet($key, $value[, $age = -1])

> Belirtilen elemanı key/value storage'da saklar.

### storageDestroy ### {#storageDestroy}
{void} cache::storageDestroy($key)

> Belirtilen elemanı key/value storage'dan siler.

### filePath ### {#filePath}
{array} cache::filePath($folder, $filename[, $age = -1, $includeAll = false])

> Cache dosyalarının saklanacağı path'i belirler, aynı zamanda hedefte okunabilecek bir cache dosyası olup olmadığını kontrol eder.

### fileGet ### {#fileGet}
{mixed} cache::fileGet($folder, $filename[, $age = -1, $includeAll = false])

> Eğer cache'den okunabilecek bir veri var ise okur, aksi halde false döndürür.

### fileGetUrl ### {#fileGetUrl}
{mixed} cache::fileGetUrl($key, $urlOrStream[, $age = -1])

> Eğer cache'den okunabilecek bir veri var ise okur, aksi takdirde veriyi velirtilen url'den veya stream'den indirir.

### fileSet ### {#fileSet}
{void} cache::fileSet($folder, $filename, $value)

> Cache'e belirtilen değeri (veya objeyi) yazar.

### fileDestroy ### {#fileDestroy}
{void} cache::fileDestroy($folder, $filename)

> Cache'den belirtilen veriyi siler.

### fileGarbageCollect ### {#fileGarbageCollect}
{void} cache::fileGarbageCollect($folder[, $age = -1])

> Belirtilen süreden eski dosyayı ilgili cache klasöründen siler.

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com