# Html Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)

## Rutin ## {#routine}


## Alanlar ## {#fields}
[$attributeOrder](#attributeOrder)

### $attributeOrder ### {#attributeOrder}
{array} html::$attributeOrder

> Html tag'lerinin birbirleri arasındaki önem sırasını saklar.

## Metodlar ## {#methods}
[tag](#tag)
[attributes](#attributes)
[selectOptions](#selectOptions)
[radioOptions](#radioOptions)
[textBox](#textBox)
[checkBox](#checkBox)
[pager](#pager)
[doctype](#doctype)
[table](#table)

### tag ### {#tag}
{string} html::tag($name[, $attributes = array(), $value = null])

> Yeni bir html tag oluşturur.

~~~

// Komut:
html::tag('div', array('style' => 'font-weight: bold;'), 'hello netherrealm.')

// Sonuç:
string('<div style="font-weight: bold;">hello netherrealm.</div>')

~~~

### attributes ### {#attributes}
{string} html::attributes($attributes)

> Attribute list oluşturur.

~~~

// Komut:
html::attributes(array('id' => '#firstdiv', 'class' => 'divs', 'style' => 'font-weight: bold;'))

// Sonuç:
string('id="#firstdiv" class="divs" style="font-weight: bold;"')

~~~

### selectOptions ### {#selectOptions}
{string} html::selectOptions($options[, $default = null, $field = null])

> Combobox için optionlar oluşturur.

~~~

// Komut:
html::selectOptions(array('male' => 'Male', 'female' => 'Female'), 'male')

// Sonuç:
string('<option value="male" selected="selected">Male</option><option value="female">Female</option>')

~~~

### radioOptions ### {#radioOptions}
{string} html::radioOptions($name, $options[, $default = null, $field = null])

> Radio buttonlar oluşturur.

~~~

// Komut:
html::radioOptions('gender', array('male' => 'Male', 'female' => 'Female'), 'male')

// Sonuç:
string('<label class="selected"><input type="radio" name="gender" value="male" checked="checked" />Male</label><label><input type="radio" name="gender" value="female" />Female</label>')

~~~

### textBox ### {#textBox}
{string} html::textBox($name[, $value = '', $attributes = array()])

> Textbox oluşturur.

~~~

// Komut:
html::textBox('name', '', array('class' => 'input'))

// Sonuç:
string('<input type="text" name="name" value="" class="input" />')

~~~

### checkBox ### {#checkBox}
{string} html::checkBox($name, $value[, $currentValue = null, $text = null, $attributes = array()])

> Checkbox oluşturur.

~~~

// Komut:
$hasDrivingLicense = 1;
html::checkBox('drivingLicense', '1', $hasDrivingLicense, 'Driving License')

// Sonuç:
string('<label><input type="checkbox" name="drivingLicense" value="1" checked="checked" />Driving License</label>')

~~~

### pager ### {#pager}
{string} html::pager($options)

> Sayfalama için pager oluşturur.

~~~

// Komut:
html::pager(array(
	'current' => 3,
	'total' => 100,
	'pagesize' => 10,
	'numlinks' => 10,
	'divider' => ' | ',
	'dots' => '...',
	'link' => '<a href="{root}/home/index/{page}" class="pagerlink">{pagetext}</a>',
	'passivelink' => '{pagetext}',
	'activelink' => '{pagetext}',
	'firstlast' => false
))

// Sonuç:
string('<a href="/php/home/index/2" class="pagerlink">&lt;</a> | <a href="/php/home/index/1" class="pagerlink">1</a> | <a href="/php/home/index/2" class="pagerlink">2</a> | 3 | <a href="/php/home/index/4" class="pagerlink">4</a> | <a href="/php/home/index/5" class="pagerlink">5</a> | <a href="/php/home/index/6" class="pagerlink">6</a> | <a href="/php/home/index/7" class="pagerlink">7</a> | <a href="/php/home/index/8" class="pagerlink">8</a> | <a href="/php/home/index/9" class="pagerlink">9</a> | <a href="/php/home/index/10" class="pagerlink">10</a> | <a href="/php/home/index/4" class="pagerlink">&gt;</a>')

~~~

### doctype ### {#doctype}
{string} html::doctype($options)

> Sayfalama için pager oluşturur.

~~~

// Komut:
html::doctype('html5')

// Sonuç:
string('<!DOCTYPE html>')

~~~

### table ### {#table}
{string} html::table($options)

> Table oluşturur.

~~~

// Komut:
html::table(array(
	'table' => '<table>',
	'cell' => '<td>{value}</td>',
	'header' => '<th>{value}</th>',
	'headers' => array('ID', 'Name', 'Surname'),
	'data' => array(
		array('1', 'Eser', 'Ozvataf'),
		array('2', 'Cengiz', 'Onkal')
	)
))

// Sonuç:
string('<table><tr><th>ID</th><th>Name</th><th>Surname</th></tr><tr><td>1</td><td>Eser</td><td>Ozvataf</td></tr><tr><td>2</td><td>Cengiz</td><td>Onkal</td></tr></table>')

~~~

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com