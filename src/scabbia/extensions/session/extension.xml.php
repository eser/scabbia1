<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
    <info>
        <name>session</name>
        <version>1.1.0</version>
        <license>GPLv3</license>
        <phpversion>5.3.0</phpversion>
        <phpdependList />
        <fwversion>1.1</fwversion>
        <fwdependList>
            <fwdepend>cache</fwdepend>
        </fwdependList>
    </info>
    <eventList>
        <event>
            <name>output</name>
            <type>callback</type>
            <value>Scabbia\Extensions\Session\session::save</value>
        </event>
    </eventList>
</scabbia>